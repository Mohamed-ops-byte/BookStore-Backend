@echo off
chcp 65001 >nul
echo.
echo ╔═══════════════════════════════════════════════════════════════╗
echo ║     Bookstore Backend - Setup Script                          ║
echo ║     متجر الكتب - سكريبت الإعداد                             ║
echo ╚═══════════════════════════════════════════════════════════════╝
echo.

REM Check if PHP is installed
php -v >nul 2>&1
if errorlevel 1 (
    echo ❌ PHP غير مثبت أو لا يمكن العثور عليه!
    echo PHP is not installed or not found!
    pause
    exit /b 1
)

echo ✅ PHP مثبت
echo.

REM Check if MySQL is running
echo جاري التحقق من MySQL Server...
echo Checking MySQL Server...
mysql -u root -e "SELECT 1" >nul 2>&1
if errorlevel 1 (
    echo ⚠️  تحذير: MySQL Server غير مشغل!
    echo WARNING: MySQL Server is not running!
    echo.
    echo يرجى تشغيل MySQL Server أولاً:
    echo Please start MySQL Server first:
    echo   Windows Services: Services.msc ^> MySQL80
    echo   Or start it from Control Panel
    echo.
    pause
    exit /b 1
)

echo ✅ MySQL Server يعمل بنجاح
echo.

REM Create database
echo جاري إنشاء قاعدة البيانات...
echo Creating database...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS bookstore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if errorlevel 1 (
    echo ❌ خطأ في إنشاء قاعدة البيانات
    pause
    exit /b 1
)
echo ✅ تم إنشاء قاعدة البيانات بنجاح
echo.

REM Run migrations
echo جاري تشغيل Migrations...
echo Running migrations...
php artisan migrate:fresh --seed
if errorlevel 1 (
    echo ❌ خطأ في تشغيل Migrations
    pause
    exit /b 1
)
echo ✅ تم تشغيل Migrations بنجاح
echo.

REM Create symbolic link for storage
echo جاري إنشاء Symbolic Link...
echo Creating symbolic link...
php artisan storage:link >nul 2>&1

echo.
echo ╔═══════════════════════════════════════════════════════════════╗
echo ║     ✅ تم الإعداد بنجاح!                                     ║
echo ║     ✅ Setup completed successfully!                          ║
echo ╠═══════════════════════════════════════════════════════════════╣
echo ║                                                               ║
echo ║  شغّل الخادم بـ:                                            ║
echo ║  Run the server with:                                        ║
echo ║                                                               ║
echo ║     php artisan serve                                        ║
echo ║                                                               ║
echo ║  ثم اذهب إلى:                                               ║
echo ║  Then go to:                                                 ║
echo ║                                                               ║
echo ║     http://localhost:8000/api/books                         ║
echo ║                                                               ║
echo ╚═══════════════════════════════════════════════════════════════╝
echo.
pause
