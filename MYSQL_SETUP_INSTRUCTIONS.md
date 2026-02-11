# MySQL Docker Setup Instructions

Based on your MySQL container setup:
```bash
docker pull mysql
docker volume create --name mysql_data
docker run --name mysql --restart always -d -p 3306:3306 -e MYSQL_ROOT_PASSWORD=secret -v mysql_data:/var/lib/mysql mysql
```

## Quick Setup

Run the automated setup script:
```bash
./setup-mysql.sh
```

This will:
1. Check if MySQL container is running
2. Create the `strengthstoolbox` database if it doesn't exist
3. Test the connection
4. Provide next steps

## Manual Setup

### Step 1: Verify MySQL Container is Running

```bash
docker ps | grep mysql
```

If not running, start it:
```bash
docker start mysql
```

### Step 2: Create the Database

```bash
docker exec -i mysql mysql -u root -psecret -e "CREATE DATABASE IF NOT EXISTS strengthstoolbox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Step 3: Verify Database Created

```bash
docker exec -i mysql mysql -u root -psecret -e "SHOW DATABASES;" | grep strengthstoolbox
```

### Step 4: Update .env File

Ensure your `.env` file has the correct database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=strengthstoolbox
DB_USERNAME=root
DB_PASSWORD=secret
```

**Important:** The password is `secret` (not empty)!

### Step 5: Clear Laravel Config Cache

```bash
php artisan config:clear
```

### Step 6: Test Connection

```bash
# Test with PHP script
php test-db-connection.php secret

# Or test with Laravel
php artisan migrate:status
```

### Step 7: Run Migrations

```bash
php artisan migrate
```

## Troubleshooting

### Connection Fails

1. **Check container is running:**
   ```bash
   docker ps | grep mysql
   ```

2. **Check MySQL logs:**
   ```bash
   docker logs mysql
   ```
   Look for "ready for connections" message.

3. **Test connection from inside container:**
   ```bash
   docker exec -it mysql mysql -u root -psecret -e "SELECT 1;"
   ```

4. **Test connection from host:**
   ```bash
   mysql -h 127.0.0.1 -P 3306 -u root -psecret -e "SELECT 1;"
   ```

### Database Already Exists Error

If you get "Database already exists", that's fine. The database is ready to use.

### Wrong Password Error

Make sure your `.env` file has:
```
DB_PASSWORD=secret
```

Not:
```
DB_PASSWORD=
```

### Port Already in Use

If port 3306 is already in use:
1. Find what's using it: `sudo lsof -i :3306`
2. Stop the conflicting service
3. Or use a different port in docker run: `-p 3307:3306`

## Useful Commands

```bash
# View MySQL logs
docker logs mysql

# Restart MySQL container
docker restart mysql

# Stop MySQL container
docker stop mysql

# Start MySQL container
docker start mysql

# Connect to MySQL CLI
docker exec -it mysql mysql -u root -psecret

# List all databases
docker exec -i mysql mysql -u root -psecret -e "SHOW DATABASES;"

# Drop and recreate database (WARNING: deletes all data)
docker exec -i mysql mysql -u root -psecret -e "DROP DATABASE IF EXISTS strengthstoolbox;"
docker exec -i mysql mysql -u root -psecret -e "CREATE DATABASE strengthstoolbox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

## Verification

After setup, verify everything works:

```bash
# 1. Test PHP connection
php test-db-connection.php secret

# 2. Test Laravel connection
php artisan migrate:status

# 3. Run migrations
php artisan migrate

# 4. Check tables were created
docker exec -i mysql mysql -u root -psecret strengthstoolbox -e "SHOW TABLES;"
```
