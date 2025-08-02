# 🚀 Railway Deployment Guide for BPO CMS

## 📋 Prerequisites

1. **GitHub Account** - Your code must be in a GitHub repository
2. **Railway Account** - Sign up at [railway.app](https://railway.app)
3. **PostgreSQL Database** - Railway provides free PostgreSQL

## 🔧 Migration Summary

### ✅ Completed Changes

1. **Backend Migration**: PHP → Node.js/Express
2. **Database**: MySQL → PostgreSQL (Railway compatible)
3. **Authentication**: Custom PHP → Express sessions
4. **Static Files**: Moved to `/public` directory
5. **API Endpoints**: Updated to `/api/auth/*` format
6. **Docker Configuration**: Optimized for Railway

### 🔄 Key Changes Made

- **`server.js`**: New Express server with PostgreSQL
- **`package.json`**: Node.js dependencies
- **`Dockerfile`**: Simplified for Railway
- **Frontend**: Updated API calls to work with new backend
- **Database**: PostgreSQL schema with users and activity_logs tables

## 🚀 Deployment Steps

### Step 1: Prepare Your Repository

1. **Commit all changes to GitHub**:
   ```bash
   git add .
   git commit -m "Migrate to Node.js for Railway deployment"
   git push origin main
   ```

### Step 2: Deploy on Railway

1. **Connect GitHub Repository**:
   - Go to [railway.app](https://railway.app)
   - Click "New Project"
   - Select "Deploy from GitHub repo"
   - Choose your repository

2. **Add PostgreSQL Database**:
   - In your Railway project dashboard
   - Click "New" → "Database" → "PostgreSQL"
   - Railway will automatically set `DATABASE_URL` environment variable

3. **Configure Environment Variables**:
   - Go to your project's "Variables" tab
   - Add the following variables:
   ```
   SESSION_SECRET=your-super-secret-session-key-change-this
   NODE_ENV=production
   ```

### Step 3: Deploy

1. **Railway will automatically detect the Dockerfile**
2. **Build and deploy** will start automatically
3. **Your app will be available** at the provided Railway URL

## 🔐 Default Login Credentials

After deployment, you can login with:
- **Email**: `admin@bpo.com`
- **Password**: `admin123`

⚠️ **Important**: Change these credentials after first login!

## 📊 Resource Optimization

### Railway Free Tier Limits:
- **RAM**: 512MB
- **Database**: 1GB storage
- **Runtime**: 500 hours/month

### Optimizations Applied:
- ✅ Alpine Linux base image (smaller)
- ✅ Production-only npm install
- ✅ Gzip compression enabled
- ✅ Static file caching
- ✅ Rate limiting (100 req/15min)

## 🔍 Monitoring & Debugging

### Railway Dashboard Features:
- **Logs**: Real-time application logs
- **Metrics**: CPU, memory, and network usage
- **Database**: PostgreSQL connection and queries
- **Deployments**: Automatic deployments on git push

### Health Check Endpoint:
- **URL**: `https://your-app.railway.app/health`
- **Response**: `healthy` (if server is running)

## 🛠️ Local Development

### Prerequisites:
```bash
npm install
```

### Environment Setup:
1. Copy `env.example` to `.env`
2. Set up local PostgreSQL database
3. Update `DATABASE_URL` in `.env`

### Run Locally:
```bash
npm run dev
```

### Access:
- **Frontend**: http://localhost:3000
- **API**: http://localhost:3000/api/*

## 🔧 Troubleshooting

### Common Issues:

1. **Database Connection Failed**:
   - Check `DATABASE_URL` environment variable
   - Ensure PostgreSQL service is running

2. **Build Failed**:
   - Check `package.json` dependencies
   - Verify Node.js version (>=18.0.0)

3. **Static Files Not Loading**:
   - Ensure files are in `/public` directory
   - Check nginx configuration

4. **Authentication Issues**:
   - Verify session configuration
   - Check `SESSION_SECRET` environment variable

### Railway Logs:
```bash
# View logs in Railway dashboard or CLI
railway logs
```

## 📈 Performance Monitoring

### Railway Metrics:
- **CPU Usage**: Monitor in Railway dashboard
- **Memory Usage**: Keep under 512MB
- **Database Queries**: Optimize slow queries
- **Response Time**: Target <200ms for API calls

### Optimization Tips:
1. **Database Indexes**: Add indexes for frequently queried columns
2. **Connection Pooling**: Already configured in `server.js`
3. **Caching**: Implement Redis for session storage (if needed)
4. **CDN**: Use Railway's built-in CDN for static assets

## 🔄 Continuous Deployment

### Automatic Deployments:
- Railway automatically deploys on `git push`
- No manual intervention required
- Rollback available in Railway dashboard

### Environment Management:
- **Production**: Main branch
- **Staging**: Create separate Railway project
- **Development**: Local environment

## 📞 Support

### Railway Support:
- **Documentation**: [docs.railway.app](https://docs.railway.app)
- **Community**: [Railway Discord](https://discord.gg/railway)
- **Issues**: [GitHub Issues](https://github.com/railwayapp/railway)

### Migration Support:
- **Database Migration**: PostgreSQL compatibility
- **API Migration**: Express.js endpoints
- **Frontend Updates**: JavaScript API calls

---

## ✅ Deployment Checklist

- [ ] Code committed to GitHub
- [ ] Railway project created
- [ ] PostgreSQL database added
- [ ] Environment variables configured
- [ ] Deployment successful
- [ ] Health check passes
- [ ] Login functionality works
- [ ] Default credentials changed
- [ ] Monitoring configured
- [ ] Documentation updated

**🎉 Your BPO CMS is now ready for production on Railway!** 