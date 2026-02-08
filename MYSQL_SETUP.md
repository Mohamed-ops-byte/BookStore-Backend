# إعداد MySQL للـ Backend

## 📋 الخطوات السريعة

### الخطوة 1: تثبيت MySQL Community Server

#### الطريقة الأولى: تنزيل من موقع MySQL الرسمي
1. اذهب إلى: https://dev.mysql.com/downloads/mysql/
2. اختر نسخة Windows
3. حمّل `MySQL Community Server` (الإصدار الأخير)
4. ثبّت البرنامج باتباع المعالج:
   - اختر **Server Machine** أثناء الإعداد
   - استخدم المنفذ الافتراضي: **3306**
   - اسم المستخدم: **root** (افتراضي)
   - كلمة المرور: اتركها فارغة أو ضع كلمة سهلة

#### الطريقة الثانية: استخدام Chocolatey (إذا كان مثبتاً)
```powershell
choco install mysql-server
```

#### الطريقة الثالثة: استخدام Windows Terminal
```powershell
winget install MySQL.Server
```

---

### الخطوة 2: التحقق من تثبيت MySQL

بعد التثبيت، افتح PowerShell بصلاحيات Admin وشغّل:

```powershell
# التحقق من وجود خدمة MySQL
Get-Service MySQL* | Format-Table Name, Status, StartType

# يجب أن تريد شيء مشابه لهذا:
# Name          Status   StartType
# ----          ------   ---------
# MySQL80       Running  Automatic
```

إذا كانت الحالة **Stopped**، شغّلها:
```powershell
# بصلاحيات Admin
Start-Service MySQL80  # غيّر الرقم حسب إصدار MySQL لديك

# أو
net start MySQL80
```

---

### الخطوة 3: إنشاء قاعدة البيانات

```bash
# اتصل بـ MySQL من Command Line
mysql -u root -p

# ستُطلب منك كلمة المرور (اضغط Enter إذا لم تضع واحدة)
```

بعد الدخول، شغّل الأوامر التالية:

```sql
-- إنشاء قاعدة البيانات
CREATE DATABASE bookstore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- التحقق
SHOW DATABASES;

-- الخروج
EXIT;
```

---

### الخطوة 4: تشغيل Migrations

```bash
cd C:\Users\Mohamed\Desktop\myprojects\Test\ 2\auth-backend-app

# تشغيل جميع الـ Migrations
php artisan migrate

# أو مع حذف الجداول القديمة وإعادة إنشاء كل شيء مع البيانات التجريبية
php artisan migrate:fresh --seed
```

---

### الخطوة 5: تشغيل السيرفر

```bash
php artisan serve

# السيرفر سيعمل على: http://localhost:8000
```

---

## 🔧 استكشاف المشاكل الشائعة

### مشكلة 1: "No such file or directory" عند محاولة الاتصال بـ MySQL

**الحل:**
```powershell
# التحقق من مسار MySQL
Get-Command mysql.exe -ErrorAction SilentlyContinue

# إذا لم تظهر نتيجة، أضف MySQL إلى PATH
# 1. اذهب إلى Control Panel > System > Environment Variables
# 2. أضف: C:\Program Files\MySQL\MySQL Server 8.0\bin
# (غيّر المسار حسب مكان تثبيتك)
```

### مشكلة 2: "Access denied for user 'root'@'localhost'"

**الحل:**
تحقق من كلمة المرور في `.env`:
```env
DB_USERNAME=root
DB_PASSWORD=your_password_here
```

### مشكلة 3: "Can't connect to MySQL server on '127.0.0.1' (10061)"

**الحل:**
MySQL Server غير مشغل. شغّله:
```powershell
Start-Service MySQL80

# أو تحقق من الحالة
Get-Service MySQL80 | Select Name, Status
```

### مشكلة 4: خطأ في Laravel عند الاتصال

**تحقق من ملف .env:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bookstore
DB_USERNAME=root
DB_PASSWORD=
```

ثم:
```bash
php artisan config:cache
php artisan migrate:fresh --seed
```

---

## 📊 التحقق من قاعدة البيانات

### عرض الجداول
```bash
php artisan tinker

# ثم شغّل:
>>> DB::select('SHOW TABLES');
>>> App\Models\Book::count();  # يجب أن ترى 15
>>> App\Models\User::all();
```

### استخدام MySQL Workbench (الطريقة الرسومية)
1. حمّل MySQL Workbench من: https://www.mysql.com/products/workbench/
2. افتح الاتصال بـ MySQL
3. تحقق من قاعدة البيانات `bookstore`

### استخدام phpMyAdmin (عبر الويب)
```bash
# إذا كان لديك XAMPP أو WAMP
# اذهب إلى: http://localhost/phpmyadmin
```

---

## ✅ قائمة التحقق

- [ ] تثبيت MySQL Community Server
- [ ] خدمة MySQL مشغلة (Status: Running)
- [ ] إنشاء قاعدة البيانات `bookstore`
- [ ] تشغيل `php artisan migrate:fresh --seed`
- [ ] تشغيل `php artisan serve`
- [ ] اختبار في Postman: GET http://localhost:8000/api/books
- [ ] عرض 15 كتاب من البيانات التجريبية

---

## 🚀 اختبار سريع

بعد إكمال الخطوات:

```bash
# الطلب الأول: عرض الكتب
curl http://localhost:8000/api/books

# يجب أن ترى JSON يحتوي على 15 كتاب
```

---

**إذا واجهت أي مشاكل، ابدأ بـ:**
```bash
php artisan migrate:refresh
php artisan db:seed --class=BookSeeder
```

---

للمزيد من المعلومات:
- MySQL Docs: https://dev.mysql.com/doc/
- Laravel Database: https://laravel.com/docs/database
- Eloquent ORM: https://laravel.com/docs/eloquent
