@echo off
echo 🔍 Testing Current Railway State
echo.

set RAILWAY_URL=https://cmsbpo2025-production.up.railway.app

echo 📡 Testing basic connectivity...
powershell -Command "try { $response = Invoke-WebRequest -Uri '%RAILWAY_URL%' -Method GET; Write-Host '✅ App is accessible (Status:' $response.StatusCode ')' } catch { Write-Host '❌ App is not accessible - Error:' $_.Exception.Message }"

echo.
echo 🧪 Testing health endpoint...
powershell -Command "try { $response = Invoke-WebRequest -Uri '%RAILWAY_URL%/health' -Method GET; Write-Host '✅ Health check passed:' $response.Content } catch { Write-Host '❌ Health check failed' }"

echo.
echo 🔐 Testing auth check endpoint...
powershell -Command "try { $response = Invoke-WebRequest -Uri '%RAILWAY_URL%/api/auth/check' -Method GET; Write-Host '✅ Auth check working:' $response.Content } catch { Write-Host '❌ Auth check failed' }"

echo.
echo 🔑 Testing login API with proper headers...
powershell -Command "try { $body = '{\"email\":\"admin@bpo.com\",\"password\":\"admin123\"}'; $headers = @{'Content-Type'='application/json'}; $response = Invoke-WebRequest -Uri '%RAILWAY_URL%/api/auth/login' -Method POST -Headers $headers -Body $body; Write-Host '✅ Login API working:' $response.Content } catch { Write-Host '❌ Login API failed - Error:' $_.Exception.Message }"

echo.
echo 🌐 Testing HTTPS...
powershell -Command "try { $response = Invoke-WebRequest -Uri '%RAILWAY_URL%' -Method GET; if ($response.BaseResponse.ResponseUri.Scheme -eq 'https') { Write-Host '✅ HTTPS is enabled' } else { Write-Host '❌ HTTPS not enabled' } } catch { Write-Host '❌ HTTPS test failed' }"

echo.
echo ✅ Testing completed!
echo.
pause 