#!/bin/bash

# Script to fix .env and test database connection

echo "=== Fixing .env Configuration ==="
echo ""

# Clear Laravel config cache
echo "Clearing Laravel config cache..."
php artisan config:clear

echo ""
echo "=== Current Database Configuration ==="
php artisan config:show database.connections.mysql 2>&1 | grep -E "database|username|password|host|port"

echo ""
echo "=== Testing Database Connection ==="

# Test connection with updated config
php test-db-connection.php secret 2>&1 | head -15

echo ""
echo "=== Next Steps ==="
echo ""
echo "If connection test passed, run:"
echo "  php artisan migrate"
echo ""
echo "If connection failed, make sure:"
echo "  1. MySQL container is running: docker ps | grep mysql"
echo "  2. Database exists: docker exec -i mysql mysql -u root -psecret -e 'SHOW DATABASES;' | grep strengthstoolbox"
echo "  3. .env file has correct values (DB_DATABASE=strengthstoolbox, DB_PASSWORD=secret)"
