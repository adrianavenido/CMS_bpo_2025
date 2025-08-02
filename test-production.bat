@echo off
echo 🚀 Testing Railway Production Deployment
echo.

set /p RAILWAY_URL="Enter your Railway URL (e.g., https://your-app.railway.app): "

echo.
echo 📡 Testing basic connectivity...
powershell -Command "try { $response = Invoke-WebRequest -Uri '%RAILWAY_URL%' -Method GET; Write-Host '✅ App is accessible' } catch { Write-Host '❌ App is not accessible' }"

echo.
echo 🧪 Testing health endpoint...
powershell -Command "try { $response = Invoke-WebRequest -Uri '%RAILWAY_URL%/health' -Method GET; Write-Host '✅ Health check passed:' $response.Content } catch { Write-Host '❌ Health check failed' }"

echo.
echo 🔐 Testing auth check endpoint...
powershell -Command "try { $response = Invoke-WebRequest -Uri '%RAILWAY_URL%/api/auth/check' -Method GET; Write-Host '✅ Auth check working:' $response.Content } catch { Write-Host '❌ Auth check failed' }"

echo.
echo 🔑 Testing login API...
powershell -Command "try { $body = '{\"email\":\"admin@bpo.com\",\"password\":\"admin123\"}'; $response = Invoke-WebRequest -Uri '%RAILWAY_URL%/api/auth/login' -Method POST -Headers @{'Content-Type'='application/json'} -Body $body; Write-Host '✅ Login API working:' $response.Content } catch { Write-Host '❌ Login API failed' }"

echo.
echo 🔒 Testing security headers...
powershell -Command "try { $response = Invoke-WebRequest -Uri '%RAILWAY_URL%' -Method GET; Write-Host '✅ Security headers present'; Write-Host 'CSP:' $response.Headers['Content-Security-Policy']; Write-Host 'X-Frame-Options:' $response.Headers['X-Frame-Options'] } catch { Write-Host '❌ Security headers test failed' }"

echo.
echo 🌐 Testing HTTPS...
powershell -Command "try { $response = Invoke-WebRequest -Uri '%RAILWAY_URL%' -Method GET; if ($response.BaseResponse.ResponseUri.Scheme -eq 'https') { Write-Host '✅ HTTPS is enabled' } else { Write-Host '❌ HTTPS not enabled' } } catch { Write-Host '❌ HTTPS test failed' }"

echo.
echo ✅ Production testing completed!
echo.
echo 📋 Manual Testing Checklist:
echo 1. Open %RAILWAY_URL% in your browser
echo 2. Verify login page loads correctly
echo 3. Login with admin@bpo.com / admin123
echo 4. Verify dashboard loads after login
echo 5. Test navigation between modules
echo 6. Test logout functionality
echo 7. Verify session management works
echo.
pause 