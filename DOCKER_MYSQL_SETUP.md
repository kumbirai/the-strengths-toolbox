# Docker MySQL Connection Troubleshooting Guide

## Problem
Laravel cannot connect to MySQL running in Docker container.

## Quick Diagnosis

Run the troubleshooting script:
```bash
./docker-mysql-troubleshoot.sh
```

## Common Issues and Solutions

### Issue 1: Port 3306 Not Exposed

**Symptoms:**
- Connection error: "Unknown error while connecting"
- Port 3306 is listening but connection fails

**Solution:**
The MySQL container must expose port 3306 to the host. Update your `docker-compose.yml`:

```yaml
services:
  db:
    image: mysql:8.0
    ports:
      - "3306:3306"  # Add this line
    environment:
      MYSQL_DATABASE: strengthstoolbox
      MYSQL_USER: root
      MYSQL_PASSWORD: ""
      MYSQL_ROOT_PASSWORD: ""
```

Then restart:
```bash
docker-compose down
docker-compose up -d db
```

### Issue 2: Database Doesn't Exist

**Symptoms:**
- Error: "Unknown database 'strengthstoolbox'"

**Solution:**
Create the database:

```bash
# Find container name
docker ps | grep mysql

# Connect to MySQL
docker exec -it <container-name> mysql -u root

# Create database
CREATE DATABASE strengthstoolbox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit
```

Or use the one-liner:
```bash
docker exec -i <container-name> mysql -u root -e "CREATE DATABASE IF NOT EXISTS strengthstoolbox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Issue 3: Wrong Credentials

**Symptoms:**
- Error: "Access denied for user"

**Solution:**
Check your `.env` file matches Docker environment variables:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=strengthstoolbox
DB_USERNAME=root
DB_PASSWORD=
```

### Issue 4: Container Not Running

**Symptoms:**
- No container found or container is stopped

**Solution:**
Start the container:

```bash
# Using docker-compose
docker-compose up -d db

# Or using docker directly
docker start <container-name>
```

### Issue 5: MySQL Still Starting

**Symptoms:**
- Connection fails immediately after starting container

**Solution:**
Wait for MySQL to fully start (usually 10-30 seconds):

```bash
# Check logs
docker logs <container-name>

# Wait for "ready for connections" message
docker logs -f <container-name>
```

## Step-by-Step Setup

### 1. Create/Update docker-compose.yml

Use the provided `docker-compose.yml` file which exposes port 3306:

```yaml
version: '3.8'

services:
  db:
    image: mysql:8.0
    container_name: strengths_toolbox_db
    restart: unless-stopped
    ports:
      - "3306:3306"
    environment:
      MYSQL_DATABASE: strengthstoolbox
      MYSQL_USER: root
      MYSQL_PASSWORD: ""
      MYSQL_ROOT_PASSWORD: ""
      MYSQL_ALLOW_EMPTY_PASSWORD: "yes"
    volumes:
      - db_data:/var/lib/mysql

volumes:
  db_data:
```

### 2. Start MySQL Container

```bash
docker-compose up -d db
```

### 3. Wait for MySQL to Start

```bash
# Watch logs until you see "ready for connections"
docker logs -f strengths_toolbox_db
# Press Ctrl+C when ready
```

### 4. Verify Connection

```bash
# Test from host
mysql -h 127.0.0.1 -P 3306 -u root -e "SELECT 1"

# Or test from inside container
docker exec -it strengths_toolbox_db mysql -u root -e "SELECT 1"
```

### 5. Create Database (if needed)

```bash
docker exec -i strengths_toolbox_db mysql -u root -e "CREATE DATABASE IF NOT EXISTS strengthstoolbox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 6. Update .env File

Ensure your `.env` has:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=strengthstoolbox
DB_USERNAME=root
DB_PASSWORD=
```

### 7. Test Laravel Connection

```bash
php artisan migrate:status
```

## Testing Connection

### Method 1: Using PHP Script

```bash
php test-db-connection.php
```

### Method 2: Using Laravel

```bash
php artisan tinker
# Then in tinker:
DB::connection()->getPdo();
```

### Method 3: Using MySQL Client

```bash
mysql -h 127.0.0.1 -P 3306 -u root strengthstoolbox
```

## Docker Network Considerations

If your Laravel app is also running in Docker, you can connect using the service name instead of `127.0.0.1`:

```env
DB_HOST=db  # Use service name from docker-compose
DB_PORT=3306
```

## Useful Commands

```bash
# View MySQL logs
docker logs strengths_toolbox_db

# View MySQL logs in real-time
docker logs -f strengths_toolbox_db

# Restart MySQL container
docker restart strengths_toolbox_db

# Stop MySQL container
docker stop strengths_toolbox_db

# Start MySQL container
docker start strengths_toolbox_db

# Remove MySQL container (WARNING: deletes data)
docker-compose down -v

# Connect to MySQL CLI
docker exec -it strengths_toolbox_db mysql -u root

# Execute SQL command
docker exec -i strengths_toolbox_db mysql -u root -e "SHOW DATABASES;"
```

## Still Having Issues?

1. Check Docker is running: `docker ps`
2. Check container logs: `docker logs <container-name>`
3. Verify port is exposed: `docker port <container-name>`
4. Test connection from host: `mysql -h 127.0.0.1 -P 3306 -u root`
5. Check firewall settings
6. Verify `.env` file has correct values
