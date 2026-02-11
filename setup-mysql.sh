#!/bin/bash

# Setup script for MySQL Docker container
# Based on your MySQL setup: docker run --name mysql -p 3306:3306 -e MYSQL_ROOT_PASSWORD=secret

echo "=== MySQL Docker Setup ==="
echo ""

# Check if MySQL container is running
if ! docker ps | grep -q "mysql"; then
    echo "⚠ MySQL container 'mysql' is not running"
    echo "Starting container..."
    docker start mysql 2>/dev/null || {
        echo "✗ Container 'mysql' not found. Please create it first:"
        echo "  docker pull mysql"
        echo "  docker volume create --name mysql_data"
        echo "  docker run --name mysql --restart always -d -p 3306:3306 -e MYSQL_ROOT_PASSWORD=secret -v mysql_data:/var/lib/mysql mysql"
        exit 1
    }
    echo "✓ Container started. Waiting for MySQL to be ready..."
    sleep 5
fi

echo "✓ MySQL container is running"
echo ""

# Check if database exists
echo "Checking if database 'strengthstoolbox' exists..."
if docker exec mysql mysql -u root -psecret -e "USE strengthstoolbox" > /dev/null 2>&1; then
    echo "✓ Database 'strengthstoolbox' already exists"
else
    echo "⚠ Database 'strengthstoolbox' does not exist"
    echo "Creating database..."
    docker exec mysql mysql -u root -psecret -e "CREATE DATABASE IF NOT EXISTS strengthstoolbox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>&1
    if [ $? -eq 0 ]; then
        echo "✓ Database 'strengthstoolbox' created successfully"
    else
        echo "✗ Failed to create database"
        exit 1
    fi
fi

echo ""
echo "=== Testing Connection ==="

# Test connection from host
echo "Testing connection from host..."
php test-db-connection.php secret 2>&1 | head -20

echo ""
echo "=== Next Steps ==="
echo ""
echo "1. Update your .env file with:"
echo "   DB_CONNECTION=mysql"
echo "   DB_HOST=127.0.0.1"
echo "   DB_PORT=3306"
echo "   DB_DATABASE=strengthstoolbox"
echo "   DB_USERNAME=root"
echo "   DB_PASSWORD=secret"
echo ""
echo "2. Clear Laravel config cache:"
echo "   php artisan config:clear"
echo ""
echo "3. Run migrations:"
echo "   php artisan migrate"
echo ""
