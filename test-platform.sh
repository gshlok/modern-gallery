#!/bin/bash

# Gallery Platform Test Script
# This script validates the basic functionality of the gallery platform

echo "🧪 Gallery Platform - Functionality Test"
echo "========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test results
TESTS_PASSED=0
TESTS_FAILED=0

# Helper function to run tests
run_test() {
    local test_name="$1"
    local test_command="$2"
    
    echo -n "Testing $test_name... "
    
    if eval "$test_command" > /dev/null 2>&1; then
        echo -e "${GREEN}✓ PASS${NC}"
        ((TESTS_PASSED++))
    else
        echo -e "${RED}✗ FAIL${NC}"
        ((TESTS_FAILED++))
    fi
}

# Check if Docker is running
echo "🐳 Checking Docker Environment"
echo "------------------------------"

run_test "Docker daemon" "docker info"
run_test "Docker Compose" "docker-compose --version"

echo ""

# Check if containers are running
echo "📦 Checking Container Status"
echo "----------------------------"

run_test "App container" "docker-compose ps app | grep -q 'Up'"
run_test "Database container" "docker-compose ps db | grep -q 'Up'"
run_test "Redis container" "docker-compose ps redis | grep -q 'Up'"
run_test "Nginx container" "docker-compose ps nginx | grep -q 'Up'"

echo ""

# Check application health
echo "🌐 Checking Application Health"
echo "------------------------------"

run_test "Web server response" "curl -s -o /dev/null -w '%{http_code}' http://localhost:8000 | grep -q '200'"
run_test "Database connection" "docker-compose exec -T app php artisan tinker --execute='DB::connection()->getPdo();'"
run_test "Redis connection" "docker-compose exec -T app php artisan tinker --execute='Redis::ping();'"

echo ""

# Check API endpoints
echo "🔌 Checking API Endpoints"
echo "-------------------------"

run_test "Images API" "curl -s http://localhost:8000/api/v1/images | jq -e '.data' > /dev/null"
run_test "Albums API" "curl -s http://localhost:8000/api/v1/albums | jq -e '.data' > /dev/null"
run_test "Search API" "curl -s 'http://localhost:8000/api/v1/search?q=test' | jq -e '.data' > /dev/null"

echo ""

# Check file structure
echo "📁 Checking File Structure"
echo "--------------------------"

run_test "Storage directory" "[ -d storage/app/public ]"
run_test "Images directory" "[ -d storage/app/public/images ]"
run_test "Thumbnails directory" "[ -d storage/app/public/thumbnails ]"
run_test "Environment file" "[ -f .env ]"

echo ""

# Check database setup
echo "🗄️ Checking Database Setup"
echo "---------------------------"

run_test "User table" "docker-compose exec -T app php artisan tinker --execute='App\\Models\\User::count();'"
run_test "Image table" "docker-compose exec -T app php artisan tinker --execute='App\\Models\\Image::count();'"
run_test "Album table" "docker-compose exec -T app php artisan tinker --execute='App\\Models\\Album::count();'"
run_test "Admin user" "docker-compose exec -T app php artisan tinker --execute='App\\Models\\User::where(\"email\", \"admin@gallery.local\")->exists();'"

echo ""

# Performance tests
echo "⚡ Basic Performance Tests"
echo "-------------------------"

run_test "Page load time (<2s)" "[ $(curl -o /dev/null -s -w '%{time_total}' http://localhost:8000 | cut -d'.' -f1) -lt 2 ]"
run_test "API response time (<1s)" "[ $(curl -o /dev/null -s -w '%{time_total}' http://localhost:8000/api/v1/images | cut -d'.' -f1) -lt 1 ]"

echo ""

# Summary
echo "📊 Test Summary"
echo "==============="
echo -e "Tests Passed: ${GREEN}$TESTS_PASSED${NC}"
echo -e "Tests Failed: ${RED}$TESTS_FAILED${NC}"
echo -e "Total Tests: $((TESTS_PASSED + TESTS_FAILED))"

if [ $TESTS_FAILED -eq 0 ]; then
    echo ""
    echo -e "${GREEN}🎉 All tests passed! The gallery platform is working correctly.${NC}"
    echo ""
    echo "🔗 Quick Links:"
    echo "   • Gallery: http://localhost:8000"
    echo "   • Admin Login: admin@gallery.local / password"
    echo "   • API Docs: http://localhost:8000/api/documentation"
    echo "   • Mail Server: http://localhost:8025"
    echo ""
    exit 0
else
    echo ""
    echo -e "${RED}❌ Some tests failed. Please check the setup and try again.${NC}"
    echo ""
    echo "🔧 Troubleshooting:"
    echo "   • Restart containers: docker-compose restart"
    echo "   • Check logs: docker-compose logs"
    echo "   • Rebuild: docker-compose down && docker-compose up -d --build"
    echo ""
    exit 1
fi