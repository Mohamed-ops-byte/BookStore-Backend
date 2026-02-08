# Bookstore Backend - Setup Script for PowerShell
# متجر الكتب - سكريبت الإعداد لـ PowerShell

Write-Host ""
Write-Host "╔═══════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║     Bookstore Backend - Setup Script                          ║" -ForegroundColor Cyan
Write-Host "║     متجر الكتب - سكريبت الإعداد                             ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# Check if PHP is installed
$phpPath = Get-Command php -ErrorAction SilentlyContinue
if (-not $phpPath) {
    Write-Host "❌ PHP غير مثبت أو لا يمكن العثور عليه!" -ForegroundColor Red
    Write-Host "PHP is not installed or not found!" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "✅ PHP مثبت" -ForegroundColor Green
Write-Host ""

# Check if MySQL is running
Write-Host "جاري التحقق من MySQL Server..." -ForegroundColor Yellow
Write-Host "Checking MySQL Server..." -ForegroundColor Yellow

$mysqlTest = mysql -u root -e "SELECT 1" 2>$null
if ($LASTEXITCODE -ne 0) {
    Write-Host "⚠️  تحذير: MySQL Server غير مشغل!" -ForegroundColor Red
    Write-Host "WARNING: MySQL Server is not running!" -ForegroundColor Red
    Write-Host ""
    Write-Host "يرجى تشغيل MySQL Server أولاً:" -ForegroundColor Yellow
    Write-Host "Please start MySQL Server first:" -ForegroundColor Yellow
    Write-Host "  Windows Services: Win+R > services.msc > MySQL80 > Start" -ForegroundColor Gray
    Write-Host "  Or: Start-Service MySQL80 (as Administrator)" -ForegroundColor Gray
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "✅ MySQL Server يعمل بنجاح" -ForegroundColor Green
Write-Host ""

# Create database
Write-Host "جاري إنشاء قاعدة البيانات..." -ForegroundColor Yellow
Write-Host "Creating database..." -ForegroundColor Yellow

$dbCreate = mysql -u root -e "CREATE DATABASE IF NOT EXISTS bookstore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ خطأ في إنشاء قاعدة البيانات" -ForegroundColor Red
    Write-Host "Error creating database" -ForegroundColor Red
    Write-Host $dbCreate -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "✅ تم إنشاء قاعدة البيانات بنجاح" -ForegroundColor Green
Write-Host ""

# Run migrations
Write-Host "جاري تشغيل Migrations..." -ForegroundColor Yellow
Write-Host "Running migrations..." -ForegroundColor Yellow

php artisan migrate:fresh --seed
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ خطأ في تشغيل Migrations" -ForegroundColor Red
    Write-Host "Error running migrations" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "✅ تم تشغيل Migrations بنجاح" -ForegroundColor Green
Write-Host ""

# Create symbolic link for storage
Write-Host "جاري إنشاء Symbolic Link..." -ForegroundColor Yellow
Write-Host "Creating symbolic link..." -ForegroundColor Yellow

php artisan storage:link 2>$null

Write-Host ""
Write-Host "╔═══════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║     ✅ تم الإعداد بنجاح!                                     ║" -ForegroundColor Green
Write-Host "║     ✅ Setup completed successfully!                          ║" -ForegroundColor Green
Write-Host "╠═══════════════════════════════════════════════════════════════╣" -ForegroundColor Green
Write-Host "║                                                               ║" -ForegroundColor Green
Write-Host "║  شغّل الخادم بـ:                                            ║" -ForegroundColor Green
Write-Host "║  Run the server with:                                        ║" -ForegroundColor Green
Write-Host "║                                                               ║" -ForegroundColor Green
Write-Host "║     php artisan serve                                        ║" -ForegroundColor Green
Write-Host "║                                                               ║" -ForegroundColor Green
Write-Host "║  ثم اذهب إلى:                                               ║" -ForegroundColor Green
Write-Host "║  Then go to:                                                 ║" -ForegroundColor Green
Write-Host "║                                                               ║" -ForegroundColor Green
Write-Host "║     http://localhost:8000/api/books                         ║" -ForegroundColor Green
Write-Host "║                                                               ║" -ForegroundColor Green
Write-Host "╚═══════════════════════════════════════════════════════════════╝" -ForegroundColor Green
Write-Host ""

Read-Host "Press Enter to exit"
