# 🚀 Bookstore Backend - كيفية التشغيل

## 📌 المتطلبات الأساسية

✅ PHP >= 8.2  
✅ Composer  
✅ MySQL Server  
✅ Git (اختياري)

---

## ⚡ طريقة سريعة (الأفضل)

### الخطوة 1️⃣: تثبيت MySQL

**على Windows:**
```bash
# إذا كان لديك Chocolatey
choco install mysql-server

# أو من Windows Package Manager
winget install MySQL.Server

# أو حمّل من: https://dev.mysql.com/downloads/mysql/
```

**على Linux (Ubuntu/Debian):**
```bash
sudo apt-get update
sudo apt-get install mysql-server
sudo mysql_secure_installation
```

**على macOS:**
```bash
brew install mysql
brew services start mysql
```

---

### الخطوة 2️⃣: تشغيل MySQL

**Windows:**
```powershell
# بصلاحيات Admin
Start-Service MySQL80

# أو
net start MySQL80

# للتحقق
Get-Service MySQL80 | Select Status
```

**Linux/macOS:**
```bash
# التحقق من الحالة
sudo systemctl status mysql

# أو
brew services list
```

---

### الخطوة 3️⃣: استخدام سكريبت الإعداد التلقائي

#### اختيار A: استخدام PowerShell (موصى به على Windows)

```powershell
# 1. افتح PowerShell كـ Administrator
# 2. اذهب إلى مجلد المشروع
cd "C:\Users\Mohamed\Desktop\myprojects\Test 2\auth-backend-app"

# 3. شغّل السكريبت
.\setup.ps1

# إذا حصلت على خطأ في التنفيذ، شغّل أولاً:
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
.\setup.ps1
```

#### اختيار B: استخدام Batch File (Windows الكلاسيكي)

```cmd
REM 1. افتح Command Prompt أو PowerShell
REM 2. اذهب إلى مجلد المشروع
cd "C:\Users\Mohamed\Desktop\myprojects\Test 2\auth-backend-app"

REM 3. شغّل السكريبت
setup.bat
```

#### اختيار C: تشغيل يدوي

```bash
# 1. اذهب إلى المجلد
cd auth-backend-app

# 2. أنشئ قاعدة البيانات
mysql -u root -e "CREATE DATABASE bookstore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 3. شغّل Migrations والبيانات التجريبية
php artisan migrate:fresh --seed

# 4. إنشاء رابط التخزين
php artisan storage:link
```

---

### الخطوة 4️⃣: تشغيل السيرفر

```bash
php artisan serve
```

ستظهر رسالة مثل:
```
INFO  Server running on [http://127.0.0.1:8000]
```

---

## 🧪 اختبار الـ APIs

### اختبار 1: عرض جميع الكتب (بدون مصادقة)

```bash
curl http://localhost:8000/api/books
```

**المتوقع:** قائمة بـ 15 كتاب من البيانات التجريبية

### اختبار 2: استخدام Postman

1. افتح Postman
2. اضغط: **File** > **Import**
3. اختر: `Bookstore_API.postman_collection.json`
4. جرّب الـ APIs:
   - ابدأ بـ **Register** (إنشاء حساب)
   - ثم **Login** (الحصول على Token)
   - استخدم Token في باقي الـ APIs

---

## 📊 فحص قاعدة البيانات

### الطريقة 1: MySQL Command Line

```bash
mysql -u root
```

ثم شغّل:
```sql
USE bookstore;
SHOW TABLES;
SELECT COUNT(*) FROM books;
SELECT * FROM books LIMIT 5;
EXIT;
```

### الطريقة 2: phpMyAdmin (لو كان مثبتاً)

```
http://localhost/phpmyadmin
```

اذهب إلى: `bookstore` > `books`

### الطريقة 3: Laravel Tinker

```bash
php artisan tinker

# ثم شغّل:
>>> App\Models\Book::count()
>>> App\Models\Book::first()
>>> App\Models\User::all()
```

---

## 🔧 تغيير إعدادات MySQL

إذا أردت تغيير المستخدم أو كلمة المرور:

**ملف `.env`:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bookstore
DB_USERNAME=root
DB_PASSWORD=your_password_here
```

ثم:
```bash
php artisan config:cache
php artisan migrate:fresh --seed
```

---

## ⚠️ حل المشاكل الشائعة

### مشكلة: "No such file or directory" (MySQL غير موجود)

**الحل:**
```bash
# تحقق من تثبيت MySQL
which mysql

# أو أضف إلى PATH
# Windows: System > Environment Variables > Path
# أضف: C:\Program Files\MySQL\MySQL Server 8.0\bin
```

### مشكلة: "Access denied for user 'root'"

**الحل:**
```bash
# إعادة تعيين كلمة المرور
mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'password';"

# ثم حدّث .env
DB_PASSWORD=password
```

### مشكلة: "Can't connect to MySQL server"

**الحل:**
```bash
# تحقق من حالة MySQL
Get-Service MySQL80 | Select Status

# أو شغّله
Start-Service MySQL80

# أو من Services:
# Win+R > services.msc > MySQL80 > Start
```

### مشكلة: "Error: Base table or view not found"

**الحل:**
```bash
# أعد تشغيل Migrations
php artisan migrate:fresh --seed
```

### مشكلة: "Specified key was too long"

**الحل:**
في `config/database.php`، تأكد من:
```php
'mysql' => [
    // ...
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
],
```

---

## 📂 هيكل المشروع

```
auth-backend-app/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php
│   │   └── BookController.php
│   └── Models/
│       ├── User.php
│       └── Book.php
├── database/
│   ├── migrations/
│   │   ├── create_users_table.php
│   │   ├── create_books_table.php
│   │   └── create_personal_access_tokens_table.php
│   └── seeders/
│       └── BookSeeder.php
├── routes/
│   └── api.php
├── .env
├── setup.ps1 (Windows PowerShell)
├── setup.bat (Windows Batch)
├── MYSQL_SETUP.md
├── SETUP_INSTRUCTIONS.md
├── README_API.md
└── Bookstore_API.postman_collection.json
```

---

## 🎯 الخطوات الموصى بها

```bash
# 1. تثبيت Dependencies (لو لم تكن مثبتة)
composer install

# 2. إعادة تعيين قاعدة البيانات
php artisan migrate:fresh --seed

# 3. مسح Cache (للتأكد)
php artisan config:cache
php artisan cache:clear

# 4. تشغيل السيرفر
php artisan serve
```

---

## 📡 ربط React Frontend

في مشروع React (`auth-ui-app`):

```javascript
// في App.js أو أي ملف
const API_URL = 'http://localhost:8000/api';

// مثال: Login
const loginUser = async (email, password) => {
  const response = await fetch(`${API_URL}/login`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ email, password })
  });
  
  const data = await response.json();
  
  if (data.success) {
    localStorage.setItem('auth_token', data.data.token);
    return data.data;
  }
  
  throw new Error(data.message);
};

// مثال: عرض الكتب
const getBooks = async () => {
  const response = await fetch(`${API_URL}/books`);
  const data = await response.json();
  return data.data.data; // Pagination
};
```

---

## ✅ قائمة التحقق

- [ ] MySQL Server مثبت وتشغيل
- [ ] قاعدة البيانات `bookstore` تم إنشاؤها
- [ ] PHP و Composer مثبتان
- [ ] `composer install` شُغّل
- [ ] `php artisan migrate:fresh --seed` شُغّل بنجاح
- [ ] `php artisan serve` يعمل
- [ ] `/api/books` يعرض 15 كتاب
- [ ] Postman Collection imported وجاهزة

---

## 🆘 طلب المساعدة

إذا استمرت المشاكل، جرّب:

```bash
# 1. التحقق من الاتصال بـ MySQL
php artisan tinker
>>> DB::connection()->getPDO();

# 2. عرض جميع الـ Migrations
php artisan migrate:status

# 3. حذف الجداول والبدء من جديد
php artisan migrate:reset
php artisan migrate:fresh --seed

# 4. مسح كل Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## 🎉 جاهز!

الآن يجب أن يكون كل شيء جاهز:
- Backend: http://localhost:8000
- Frontend: http://localhost:3000 (إذا كان React يعمل)
- Postman: جميع APIs متوفرة

استمتع بالتطوير! 🚀
