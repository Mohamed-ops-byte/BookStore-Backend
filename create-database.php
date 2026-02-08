<?php
/**
 * Create Database Script
 * إنشاء قاعدة البيانات
 */

echo "\n╔═══════════════════════════════════════════════════════════╗\n";
echo "║  Creating Database 'bookstore'                          ║\n";
echo "║  جاري إنشاء قاعدة البيانات                             ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

try {
    // Connect to MySQL without database
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306", "root", "");
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS bookstore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
    $pdo->exec($sql);
    
    echo "✅ تم إنشاء قاعدة البيانات بنجاح!\n";
    echo "✅ Database 'bookstore' created successfully!\n\n";
    
    // Verify
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'bookstore'");
    $result = $stmt->fetch();
    
    if ($result) {
        echo "✅ تم التحقق: قاعدة البيانات موجودة\n";
        echo "✅ Verified: Database exists\n\n";
        
        echo "🚀 الآن شغّل:\n";
        echo "🚀 Now run:\n\n";
        echo "   php artisan migrate:fresh --seed\n\n";
    }
    
} catch (PDOException $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    
    echo "💡 يرجى التأكد من:\n";
    echo "💡 Please make sure:\n";
    echo "   1. MySQL Server يعمل / MySQL Server is running\n";
    echo "   2. المستخدم root موجود / User root exists\n";
    echo "   3. اسم المستخدم وكلمة المرور صحيحة في .env\n";
    echo "   3. Username and password in .env are correct\n";
    
    exit(1);
}
?>
