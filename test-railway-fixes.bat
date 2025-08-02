@echo off
echo 🚀 Testing Railway App After Fixes
echo.

set RAILWAY_URL=https://cmsbpo2025-production.up.railway.app

echo 📡 Testing basic connectivity...
powershell -Command "try { $response = Invoke-WebRequest -Uri '%RAILWAY_URL%' -Method GET; Write-Host '✅ App is accessible (Status:' $response.StatusCode ')' } catch { Write-Host '❌ App is not accessible - Error:' $_.Exception.Message }"

echo.
echo 🧪 Testing health endpoint...
powershell -Command "try { $response = Invoke-WebRequest -Uri '%RAILWAY_URL%/health' -Method GET; Write-Host '✅ Health check passed:' $response.Content } catch { Write-Host '❌ Health check failed' }"

echo.
echo 🗄️ Testing database connectivity...
powershell -Command "try { $response = Invoke-WebRequest -Uri '%RAILWAY_URL%/api/test-db' -Method GET; Write-Host '✅ Database test:' $response.Content } catch { Write-Host '❌ Database test failed' }"

echo.
echo 🔐 Testing auth check endpoint...
powershell -Command "try { $response = Invoke-WebRequest -Uri '%RAILWAY_URL%/api/auth/check' -Method GET; Write-Host '✅ Auth check working:' $response.Content } catch { Write-Host '❌ Auth check failed' }"

echo.
echo 🔑 Testing login API...
powershell -Command "try { $body = '{\"email\":\"admin@bpo.com\",\"password\":\"admin123\"}'; $response = Invoke-WebRequest -Uri '%RAILWAY_URL%/api/auth/login' -Method POST -Headers @{'Content-Type'='application/json'} -Body $body; Write-Host '✅ Login API working:' $response.Content } catch { Write-Host '❌ Login API failed' }"

echo.
echo 🌐 Testing HTTPS...
powershell -Command "try { $response = Invoke-WebRequest -Uri '%RAILWAY_URL%' -Method GET; if ($response.BaseResponse.ResponseUri.Scheme -eq 'https') { Write-Host '✅ HTTPS is enabled' } else { Write-Host '❌ HTTPS not enabled' } } catch { Write-Host '❌ HTTPS test failed' }"

echo.
echo ✅ Testing completed!
echo.
echo 📋 Manual Testing Checklist:
echo 1. Open %RAILWAY_URL% in your browser
echo 2. Verify login page loads (should show login form)
echo 3. Login with admin@bpo.com / admin123
echo 4. Verify dashboard loads after login
echo 5. Test navigation between modules (Dashboard, Clients, Agents, etc.)
echo 6. Test logout functionality
echo 7. Verify session management works
echo.
echo 🔧 If issues persist:
echo - Check Railway logs: railway logs
echo - Verify environment variables in Railway dashboard
echo - Check database connection status
echo.
pause 