<?php

/**
 * Database Connection Diagnostic Script
 * Tests MySQL connection with various configurations
 */

echo "=== MySQL Connection Diagnostic ===\n\n";

// Test 1: Basic PDO connection without database (with password 'secret')
echo "Test 1: Basic connection (no database)...\n";
try {
    $password = $argv[1] ?? 'secret'; // Use password from command line or default to 'secret'
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected successfully\n";
    
    // List databases
    $stmt = $pdo->query('SHOW DATABASES');
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Available databases: " . implode(', ', $databases) . "\n\n";
} catch (PDOException $e) {
    echo "✗ Connection failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Try connecting to 'laravel' database
echo "Test 2: Connect to 'laravel' database...\n";
try {
    $password = $argv[1] ?? 'secret';
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=laravel', 'root', $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to 'laravel' database\n\n";
} catch (PDOException $e) {
    echo "✗ Failed: " . $e->getMessage() . "\n\n";
}

// Test 3: Try connecting to 'strengthstoolbox' database
echo "Test 3: Connect to 'strengthstoolbox' database...\n";
try {
    $password = $argv[1] ?? 'secret';
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=strengthstoolbox', 'root', $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to 'strengthstoolbox' database\n\n";
} catch (PDOException $e) {
    echo "✗ Failed: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "\n⚠ Database 'strengthstoolbox' does not exist.\n";
        echo "Creating database...\n";
        try {
            $password = $argv[1] ?? 'secret';
            $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', $password);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS strengthstoolbox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "✓ Database 'strengthstoolbox' created successfully\n\n";
        } catch (PDOException $createError) {
            echo "✗ Failed to create database: " . $createError->getMessage() . "\n\n";
        }
    }
}

// Test 4: Test with password (if provided)
if (isset($argv[1])) {
    echo "Test 4: Connect with password...\n";
    try {
        $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=strengthstoolbox', 'root', $argv[1]);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✓ Connected with password\n\n";
    } catch (PDOException $e) {
        echo "✗ Failed: " . $e->getMessage() . "\n\n";
    }
}

echo "=== Diagnostic Complete ===\n";
