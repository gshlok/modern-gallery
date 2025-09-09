@echo off
REM Gallery Platform Test Script for Windows
REM This script validates the basic functionality of the gallery platform

echo 🧪 Gallery Platform - Functionality Test
echo =========================================
echo.

set /a TESTS_PASSED=0
set /a TESTS_FAILED=0

REM Helper function to run tests
:run_test
echo Testing %~1...
%~2 >nul 2>&1
if %errorlevel% equ 0 (
    echo ✓ PASS - %~1
    set /a TESTS_PASSED+=1
) else (
    echo ✗ FAIL - %~1
    set /a TESTS_FAILED+=1
)
goto :eof

REM Check if Docker is running
echo 🐳 Checking Docker Environment
echo ------------------------------
call :run_test "Docker daemon" "docker info"
call :run_test "Docker Compose" "docker-compose --version"
echo.

REM Check if containers are running
echo 📦 Checking Container Status
echo ----------------------------
call :run_test "App container" "docker-compose ps app | findstr Up"
call :run_test "Database container" "docker-compose ps db | findstr Up"
call :run_test "Redis container" "docker-compose ps redis | findstr Up"
call :run_test "Nginx container" "docker-compose ps nginx | findstr Up"
echo.

REM Check application health
echo 🌐 Checking Application Health
echo ------------------------------
call :run_test "Web server response" "curl -s -o nul -w \"%%{http_code}\" http://localhost:8000 | findstr 200"
echo.

REM Check file structure
echo 📁 Checking File Structure
echo --------------------------
call :run_test "Storage directory" "if exist storage\\app\\public echo found"
call :run_test "Environment file" "if exist .env echo found"
echo.

REM Summary
echo 📊 Test Summary
echo ===============
echo Tests Passed: %TESTS_PASSED%
echo Tests Failed: %TESTS_FAILED%
set /a TOTAL_TESTS=%TESTS_PASSED%+%TESTS_FAILED%
echo Total Tests: %TOTAL_TESTS%

if %TESTS_FAILED% equ 0 (
    echo.
    echo 🎉 All tests passed! The gallery platform is working correctly.
    echo.
    echo 🔗 Quick Links:
    echo    • Gallery: http://localhost:8000
    echo    • Admin Login: admin@gallery.local / password
    echo    • Mail Server: http://localhost:8025
    echo.
) else (
    echo.
    echo ❌ Some tests failed. Please check the setup and try again.
    echo.
    echo 🔧 Troubleshooting:
    echo    • Restart containers: docker-compose restart
    echo    • Check logs: docker-compose logs
    echo    • Rebuild: docker-compose down ^&^& docker-compose up -d --build
    echo.
)

pause