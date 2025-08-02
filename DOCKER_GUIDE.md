# 🐳 Docker Development & Deployment Guide

## 📋 Prerequisites

1. **Docker Desktop** installed and running
2. **Git** for version control
3. **GitHub account** for Railway deployment

## 🚀 Local Development with Docker

### Step 1: Build and Start Services
```bash
# Build and start all services (PostgreSQL + Node.js app)
docker-compose up --build

# Or run in background
docker-compose up -d --build
```

### Step 2: Verify Services
```bash
# Check if containers are running
docker-compose ps

# Check logs
docker-compose logs app
docker-compose logs postgres

# Check database connection
docker-compose exec postgres psql -U bpo_user -d bpo_cms -c "\dt"
```

### Step 3: Access Application
- **Application**: http://localhost:3000
- **Default Login**: 
  - Email: `admin@bpo.com`
  - Password: `admin123`

## 🧪 Testing Checklist

### ✅ Database Connection
```bash
# Test database connectivity
docker-compose exec app node -e "
const { Pool } = require('pg');
const pool = new Pool({
  connectionString: 'postgresql://bpo_user:bpo_password@postgres:5432/bpo_cms'
});
pool.query('SELECT NOW()', (err, res) => {
  if (err) console.error('DB Error:', err);
  else console.log('DB Connected:', res.rows[0]);
  process.exit();
});
"
```

### ✅ API Endpoints
```bash
# Test login endpoint
curl -X POST http://localhost:3000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@bpo.com","password":"admin123"}'

# Test auth check
curl http://localhost:3000/api/auth/check
```

### ✅ Frontend Functionality
1. Open http://localhost:3000
2. Login with admin credentials
3. Navigate through dashboard modules
4. Test logout functionality

## 🔧 Development Commands

### Hot Reload Development
```bash
# For development with file watching
docker-compose -f docker-compose.dev.yml up --build
```

### Database Management
```bash
# Access PostgreSQL shell
docker-compose exec postgres psql -U bpo_user -d bpo_cms

# Reset database
docker-compose down -v
docker-compose up --build
```

### Container Management
```bash
# Stop services
docker-compose down

# Stop and remove volumes (resets database)
docker-compose down -v

# View logs
docker-compose logs -f app

# Execute commands in container
docker-compose exec app npm install new-package
```

## 🚀 Railway Deployment

### Step 1: Prepare for Railway
1. **Push to GitHub**:
   ```bash
   git add .
   git commit -m "Docker setup complete"
   git push origin main
   ```

2. **Railway Setup**:
   - Go to [Railway.app](https://railway.app)
   - Connect GitHub repository
   - Add PostgreSQL service
   - Configure environment variables

### Step 2: Environment Variables (Railway)
```env
DATABASE_URL=postgresql://[railway-generated-url]
SESSION_SECRET=your-super-secret-key-here
NODE_ENV=production
PORT=3000
```

### Step 3: Deploy
- Railway will automatically detect the Dockerfile
- Build and deploy using the Docker image
- PostgreSQL will be provisioned automatically

## 🔍 Troubleshooting

### Common Issues

#### 1. Port Already in Use
```bash
# Check what's using port 3000
netstat -ano | findstr :3000

# Kill process or change port in docker-compose.yml
```

#### 2. Database Connection Issues
```bash
# Check if PostgreSQL is running
docker-compose ps postgres

# Check database logs
docker-compose logs postgres

# Test connection manually
docker-compose exec postgres pg_isready -U bpo_user -d bpo_cms
```

#### 3. Application Won't Start
```bash
# Check application logs
docker-compose logs app

# Rebuild without cache
docker-compose build --no-cache

# Check if all files are copied
docker-compose exec app ls -la
```

#### 4. Permission Issues
```bash
# Fix file permissions
docker-compose exec app chown -R nodejs:nodejs /app
```

## 📊 Monitoring

### Health Checks
```bash
# Check container health
docker-compose ps

# Monitor resource usage
docker stats
```

### Logs
```bash
# Follow application logs
docker-compose logs -f app

# Follow database logs
docker-compose logs -f postgres
```

## 🔐 Security Notes

1. **Change default credentials** after first login
2. **Update SESSION_SECRET** in production
3. **Use strong passwords** for database
4. **Enable HTTPS** in production (Railway handles this)

## 📈 Performance Tips

1. **Use Alpine images** (already configured)
2. **Multi-stage builds** for production
3. **Volume mounting** for development
4. **Health checks** for reliability

## 🎯 Next Steps

1. ✅ **Local Testing**: Run `docker-compose up --build`
2. ✅ **Verify Functionality**: Test login, dashboard, logout
3. ✅ **GitHub Push**: Commit and push your changes
4. ✅ **Railway Setup**: Create account and connect repository
5. ✅ **Deploy**: Railway will build and deploy automatically
6. ✅ **Test Production**: Verify deployed application works
7. ✅ **Update Credentials**: Change default admin password

## 🆘 Need Help?

- **Docker Issues**: Check Docker Desktop is running
- **Database Issues**: Verify PostgreSQL container is healthy
- **Application Issues**: Check logs with `docker-compose logs app`
- **Deployment Issues**: Verify environment variables in Railway 