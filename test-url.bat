@echo off
echo 🚀 Quick Railway App Test
echo.

set /p APP_URL="Enter your Railway app URL: "

echo.
echo 📡 Testing your Railway app...
echo URL: %APP_URL%
echo.

echo 🔍 Testing basic connectivity...
powershell -Command "try { $response = Invoke-WebRequest -Uri '%APP_URL%' -Method GET; Write-Host '✅ App is accessible (Status:' $response.StatusCode ')' } catch { Write-Host '❌ App is not accessible - Error:' $_.Exception.Message }"

echo.
echo 🧪 Testing health endpoint...
powershell -Command "try { $response = Invoke-WebRequest -Uri '%APP_URL%/health' -Method GET; Write-Host '✅ Health check passed:' $response.Content } catch { Write-Host '❌ Health check failed' }"

echo.
echo 🔐 Testing auth check...
powershell -Command "try { $response = Invoke-WebRequest -Uri '%APP_URL%/api/auth/check' -Method GET; Write-Host '✅ Auth check working:' $response.Content } catch { Write-Host '❌ Auth check failed' }"

echo.
echo 🌐 Testing HTTPS...
powershell -Command "try { $response = Invoke-WebRequest -Uri '%APP_URL%' -Method GET; if ($response.BaseResponse.ResponseUri.Scheme -eq 'https') { Write-Host '✅ HTTPS is enabled' } else { Write-Host '❌ HTTPS not enabled' } } catch { Write-Host '❌ HTTPS test failed' }"

echo.
echo ✅ Testing completed!
echo.
echo 📋 Next Steps:
echo 1. Open %APP_URL% in your browser
echo 2. Test login with admin@bpo.com / admin123
echo 3. Verify dashboard functionality
echo.
pause 