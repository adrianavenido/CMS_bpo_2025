@echo off
echo 🐳 Starting BPO CMS with Docker...
echo.

echo 📦 Building and starting services...
docker-compose up --build

echo.
echo ✅ Services started!
echo 🌐 Application: http://localhost:3000
echo 👤 Default Login: admin@bpo.com / admin123
echo.
echo Press Ctrl+C to stop services
pause 