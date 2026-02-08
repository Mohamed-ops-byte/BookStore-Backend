#!/usr/bin/env php
<?php
/**
 * Database Connection Test Script
 * اختبار الاتصال بقاعدة البيانات
 */

echo "\n╔═══════════════════════════════════════════════════════════╗\n";
echo "║  Database Connection Tester                             ║\n";
echo "║  اختبار الاتصال بقاعدة البيانات                        ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Load .env file
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
} else {
    echo "❌ .env file not found!\n";
    exit(1);
}

// Extract database config
$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? 3306;
$database = $env['DB_DATABASE'] ?? 'bookstore';
$username = $env['DB_USERNAME'] ?? 'root';
$password = $env['DB_PASSWORD'] ?? '';
$connection = $env['DB_CONNECTION'] ?? 'mysql';

echo "📋 Configuration:\n";
echo "   Connection: $connection\n";
echo "   Host: $host\n";
echo "   Port: $port\n";
echo "   Database: $database\n";
echo "   Username: $username\n";
echo "   Password: " . (empty($password) ? '[empty]' : '[set]') . "\n\n";

// Test connection
echo "🔗 Testing connection...\n";

try {
    if ($connection === 'mysql') {
        $dsn = "mysql:host=$host;port=$port;dbname=$database";
        $pdo = new PDO($dsn, $username, $password);
        echo "✅ Connected to MySQL Server!\n";
        echo "✅ Database '$database' is accessible!\n";
        
        // Check tables
        $query = $pdo->prepare("SHOW TABLES FROM $database");
        $query->execute();
        $tables = $query->fetchAll(PDO::FETCH_COLUMN);
        
        echo "\n📊 Tables in database:\n";
        if (empty($tables)) {
            echo "   ⚠️  No tables found. Run migrations first:\n";
            echo "   php artisan migrate:fresh --seed\n";
        } else {
            foreach ($tables as $table) {
                // Count rows
                $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
                echo "   - $table ($count rows)\n";
            }
        }
        
    } elseif ($connection === 'sqlite') {
        $dbPath = __DIR__ . '/database/database.sqlite';
        $pdo = new PDO("sqlite:$dbPath");
        echo "✅ Connected to SQLite!\n";
        echo "   Database file: $dbPath\n";
        
        // Check tables
        $query = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table'");
        $query->execute();
        $tables = $query->fetchAll(PDO::FETCH_COLUMN);
        
        echo "\n📊 Tables in database:\n";
        if (empty($tables)) {
            echo "   ⚠️  No tables found. Run migrations first:\n";
            echo "   php artisan migrate:fresh --seed\n";
        } else {
            foreach ($tables as $table) {
                $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
                echo "   - $table ($count rows)\n";
            }
        }
    }
    
    echo "\n✅ All checks passed!\n";
    echo "🚀 You can now run: php artisan migrate:fresh --seed\n";
    
} catch (PDOException $e) {
    echo "❌ Connection failed!\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
    
    if ($connection === 'mysql') {
        echo "💡 Troubleshooting:\n";
        echo "   1. Is MySQL Server running?\n";
        echo "      Windows: Start-Service MySQL80\n";
        echo "      Linux: sudo systemctl start mysql\n";
        echo "      macOS: brew services start mysql\n\n";
        echo "   2. Check database credentials in .env\n";
        echo "   3. Try connecting manually:\n";
        echo "      mysql -h $host -u $username\n";
    }
    
    exit(1);
}

echo "\n";
?>
