const express = require('express');
const session = require('express-session');
const bcrypt = require('bcryptjs');
const { Pool } = require('pg');
const cors = require('cors');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');
const path = require('path');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3000;

// Database connection with better error handling
const pool = new Pool({
  connectionString: process.env.DATABASE_URL || 'postgresql://localhost:5432/bpo_cms',
  ssl: process.env.NODE_ENV === 'production' ? { rejectUnauthorized: false } : false,
  max: 20, // Maximum number of clients in the pool
  idleTimeoutMillis: 30000, // Close idle clients after 30 seconds
  connectionTimeoutMillis: 2000, // Return an error after 2 seconds if connection could not be established
});

// Test database connection
pool.on('error', (err) => {
  console.error('Unexpected error on idle client', err);
  process.exit(-1);
});

// Security middleware with improved configuration
app.use(helmet({
  contentSecurityPolicy: {
    directives: {
      defaultSrc: ["'self'"],
      styleSrc: ["'self'", "'unsafe-inline'", "https://cdn.tailwindcss.com", "https://cdnjs.cloudflare.com"],
      scriptSrc: ["'self'", "'unsafe-inline'", "https://cdn.tailwindcss.com"],
      fontSrc: ["'self'", "https://cdnjs.cloudflare.com"],
      imgSrc: ["'self'", "data:", "https:"],
      connectSrc: ["'self'"],
      frameSrc: ["'none'"],
      objectSrc: ["'none'"],
    },
  },
  crossOriginEmbedderPolicy: false,
}));

// Rate limiting with better configuration
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 100, // limit each IP to 100 requests per windowMs
  message: 'Too many requests from this IP, please try again later.',
  standardHeaders: true,
  legacyHeaders: false,
});
app.use(limiter);

// CORS with specific origin for production
const corsOptions = {
  origin: true, // Allow all origins for now
  credentials: true,
  optionsSuccessStatus: 200
};
app.use(cors(corsOptions));

// Body parsing middleware
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// Session configuration with improved security
app.use(session({
  secret: process.env.SESSION_SECRET || 'your-secret-key-change-this-in-production',
  resave: false,
  saveUninitialized: false,
  cookie: {
    secure: false, // Set to false for testing
    httpOnly: true,
    maxAge: 24 * 60 * 60 * 1000, // 24 hours
    sameSite: 'lax',
    path: '/'
  },
  name: 'bpo-cms-session' // Custom session name for security
}));

// Serve static files with caching
app.use(express.static(path.join(__dirname, 'public'), {
  maxAge: '1h',
  etag: true
}));

// Database initialization with better error handling
async function initDatabase() {
  let client;
  try {
    client = await pool.connect();
    
    // Create users table
    await client.query(`
      CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);

    // Create activity_logs table
    await client.query(`
      CREATE TABLE IF NOT EXISTS activity_logs (
        id SERIAL PRIMARY KEY,
        user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
        action VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);

    // Create default admin user if not exists
    const adminCheck = await client.query('SELECT id FROM users WHERE email = $1', ['admin@bpo.com']);
    if (adminCheck.rows.length === 0) {
      const hashedPassword = await bcrypt.hash('admin123', 12); // Increased salt rounds
      await client.query(
        'INSERT INTO users (username, email, password, role) VALUES ($1, $2, $3, $4)',
        ['admin', 'admin@bpo.com', hashedPassword, 'admin']
      );
      console.log('Default admin user created');
    }

    console.log('Database initialized successfully');
  } catch (error) {
    console.error('Database initialization error:', error);
    throw error; // Re-throw to prevent server start
  } finally {
    if (client) client.release();
  }
}

// Authentication middleware with better error handling
const requireAuth = (req, res, next) => {
  if (!req.session.userId) {
    return res.status(401).json({ 
      error: 'Authentication required',
      redirect: '/'
    });
  }
  next();
};

// Health check endpoint
app.get('/health', (req, res) => {
  res.json({ 
    status: 'ok', 
    timestamp: new Date().toISOString(),
    environment: process.env.NODE_ENV || 'development'
  });
});

// Database test endpoint
app.get('/api/test-db', async (req, res) => {
  let client;
  try {
    client = await pool.connect();
    const result = await client.query('SELECT NOW() as current_time, COUNT(*) as user_count FROM users');
    res.json({
      success: true,
      database: 'connected',
      current_time: result.rows[0].current_time,
      user_count: result.rows[0].user_count
    });
  } catch (error) {
    console.error('Database test error:', error);
    res.status(500).json({
      success: false,
      error: 'Database connection failed',
      details: error.message
    });
  } finally {
    if (client) client.release();
  }
});

// Routes with better error handling
app.get('/', (req, res) => {
  try {
    res.sendFile(path.join(__dirname, 'public', 'login.html'));
  } catch (error) {
    console.error('Error serving login page:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

app.get('/dashboard', requireAuth, (req, res) => {
  try {
    res.sendFile(path.join(__dirname, 'public', 'index.html'));
  } catch (error) {
    console.error('Error serving dashboard:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

// API Routes with improved error handling
app.post('/api/auth/login', async (req, res) => {
  let client;
  try {
    const { email, password } = req.body;
    
    // Input validation
    if (!email || !password) {
      return res.status(400).json({ error: 'Email and password are required' });
    }

    if (!email.includes('@')) {
      return res.status(400).json({ error: 'Invalid email format' });
    }

    client = await pool.connect();
    
    const result = await client.query(
      'SELECT id, username, email, password, role FROM users WHERE email = $1',
      [email]
    );

    if (result.rows.length === 0) {
      return res.status(401).json({ error: 'Invalid credentials' });
    }

    const user = result.rows[0];
    const isValidPassword = await bcrypt.compare(password, user.password);

    if (!isValidPassword) {
      return res.status(401).json({ error: 'Invalid credentials' });
    }

    // Set session
    req.session.userId = user.id;
    req.session.username = user.username;
    req.session.email = user.email;
    req.session.role = user.role;

    // Log activity
    await client.query(
      'INSERT INTO activity_logs (user_id, action, description) VALUES ($1, $2, $3)',
      [user.id, 'login', 'User logged in successfully']
    );

    res.json({ 
      success: true, 
      user: {
        id: user.id,
        username: user.username,
        email: user.email,
        role: user.role
      }
    });
  } catch (error) {
    console.error('Login error:', error);
    res.status(500).json({ error: 'Login failed. Please try again.' });
  } finally {
    if (client) client.release();
  }
});

app.post('/api/auth/logout', requireAuth, async (req, res) => {
  let client;
  try {
    client = await pool.connect();
    
    // Log activity
    await client.query(
      'INSERT INTO activity_logs (user_id, action, description) VALUES ($1, $2, $3)',
      [req.session.userId, 'logout', 'User logged out']
    );

    req.session.destroy((err) => {
      if (err) {
        console.error('Session destruction error:', err);
        return res.status(500).json({ error: 'Logout failed' });
      }
      res.json({ success: true });
    });
  } catch (error) {
    console.error('Logout error:', error);
    res.status(500).json({ error: 'Logout failed' });
  } finally {
    if (client) client.release();
  }
});

app.get('/api/auth/check', (req, res) => {
  try {
    if (req.session.userId) {
      res.json({
        authenticated: true,
        user: {
          id: req.session.userId,
          username: req.session.username,
          email: req.session.email,
          role: req.session.role
        }
      });
    } else {
      res.json({ authenticated: false });
    }
  } catch (error) {
    console.error('Auth check error:', error);
    res.status(500).json({ error: 'Authentication check failed' });
  }
});

// Catch-all route for SPA with better error handling
app.get('*', (req, res) => {
  try {
    // Only serve index.html for routes that should be handled by the SPA
    // Exclude API routes and specific pages
    if (req.path.startsWith('/api/') || req.path === '/' || req.path === '/dashboard') {
      return res.status(404).json({ error: 'Page not found' });
    }
    res.sendFile(path.join(__dirname, 'public', 'index.html'));
  } catch (error) {
    console.error('Error serving catch-all route:', error);
    res.status(404).json({ error: 'Page not found' });
  }
});

// Error handling middleware
app.use((err, req, res, next) => {
  console.error('Unhandled error:', err);
  res.status(500).json({ 
    error: process.env.NODE_ENV === 'production' 
      ? 'Internal server error' 
      : err.message 
  });
});

// Graceful shutdown
process.on('SIGTERM', () => {
  console.log('SIGTERM received, shutting down gracefully');
  pool.end();
  process.exit(0);
});

process.on('SIGINT', () => {
  console.log('SIGINT received, shutting down gracefully');
  pool.end();
  process.exit(0);
});

// Initialize database and start server
initDatabase().then(() => {
  app.listen(PORT, () => {
    console.log(`Server running on port ${PORT} in ${process.env.NODE_ENV || 'development'} mode`);
  });
}).catch(error => {
  console.error('Failed to start server:', error);
  process.exit(1);
}); 