#!/bin/bash

# Docker MySQL Troubleshooting Script
# This script helps diagnose and fix MySQL Docker connection issues

echo "=== Docker MySQL Troubleshooting ==="
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "✗ Docker is not running or not accessible"
    echo "  Please start Docker and try again"
    exit 1
fi
echo "✓ Docker is running"

# Check if MySQL container exists
if docker ps -a | grep -q "strengths_toolbox_db\|mysql"; then
    echo "✓ MySQL container found"
    docker ps -a | grep -E "strengths_toolbox_db|mysql" | head -1
else
    echo "⚠ No MySQL container found"
fi

# Check if MySQL container is running
if docker ps | grep -q "strengths_toolbox_db\|mysql"; then
    echo "✓ MySQL container is running"
    CONTAINER_NAME=$(docker ps | grep -E "strengths_toolbox_db|mysql" | awk '{print $NF}' | head -1)
    echo "  Container: $CONTAINER_NAME"
    
    # Check port mapping
    PORT_MAP=$(docker port $CONTAINER_NAME 2>/dev/null | grep 3306 || echo "")
    if [ -n "$PORT_MAP" ]; then
        echo "✓ Port 3306 is mapped: $PORT_MAP"
    else
        echo "⚠ Port 3306 is NOT exposed to host"
        echo "  The container is running but port is not accessible from host"
        echo "  Solution: Add 'ports: - \"3306:3306\"' to docker-compose.yml"
    fi
else
    echo "✗ MySQL container is not running"
    echo ""
    echo "To start MySQL container:"
    echo "  docker-compose up -d db"
    echo "  OR"
    echo "  docker start <container-name>"
fi

echo ""
echo "=== Connection Test ==="

# Try to connect to MySQL
if docker ps | grep -q "strengths_toolbox_db\|mysql"; then
    CONTAINER_NAME=$(docker ps | grep -E "strengths_toolbox_db|mysql" | awk '{print $NF}' | head -1)
    
    echo "Testing connection from inside container..."
    if docker exec $CONTAINER_NAME mysql -u root -e "SELECT 1" > /dev/null 2>&1; then
        echo "✓ MySQL is accessible from inside container"
        
        # List databases
        echo ""
        echo "Available databases:"
        docker exec $CONTAINER_NAME mysql -u root -e "SHOW DATABASES;" 2>/dev/null | grep -v "Database\|information_schema\|performance_schema\|mysql\|sys"
        
        # Check if strengthstoolbox exists
        if docker exec $CONTAINER_NAME mysql -u root -e "USE strengthstoolbox" > /dev/null 2>&1; then
            echo ""
            echo "✓ Database 'strengthstoolbox' exists"
        else
            echo ""
            echo "⚠ Database 'strengthstoolbox' does NOT exist"
            echo ""
            read -p "Create database 'strengthstoolbox'? (y/n) " -n 1 -r
            echo
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                docker exec $CONTAINER_NAME mysql -u root -e "CREATE DATABASE IF NOT EXISTS strengthstoolbox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
                echo "✓ Database created"
            fi
        fi
    else
        echo "✗ Cannot connect to MySQL from inside container"
        echo "  MySQL may still be starting up. Wait a few seconds and try again."
    fi
fi

echo ""
echo "=== Quick Fixes ==="
echo ""
echo "1. If port 3306 is not exposed, update docker-compose.yml:"
echo "   Add 'ports: - \"3306:3306\"' under the db service"
echo ""
echo "2. If database doesn't exist, create it:"
echo "   docker exec -it <container-name> mysql -u root -p"
echo "   CREATE DATABASE strengthstoolbox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo ""
echo "3. Restart MySQL container:"
echo "   docker restart <container-name>"
echo ""
echo "4. Check MySQL logs:"
echo "   docker logs <container-name>"
