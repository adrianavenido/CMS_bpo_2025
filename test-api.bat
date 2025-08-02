@echo off
echo 🧪 Testing BPO CMS API Endpoints...
echo.

echo 📡 Testing login endpoint...
curl -X POST http://localhost:3000/api/auth/login -H "Content-Type: application/json" -d "{\"email\":\"admin@bpo.com\",\"password\":\"admin123\"}"

echo.
echo 📡 Testing auth check endpoint...
curl http://localhost:3000/api/auth/check

echo.
echo ✅ API tests completed!
pause 