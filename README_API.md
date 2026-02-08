# Bookstore Backend API

## 📚 نظام إدارة متجر الكتب - Laravel Backend

Backend كامل لمتجر الكتب مبني باستخدام Laravel مع Authentication و CRUD APIs

---

## 🚀 المميزات

### Authentication (المصادقة)
- ✅ تسجيل مستخدم جديد (Register)
- ✅ تسجيل الدخول (Login)
- ✅ تسجيل الخروج (Logout)
- ✅ الحصول على بيانات المستخدم (Get User Profile)
- 🔒 Laravel Sanctum للمصادقة بـ Token

### Books APIs (إدارة الكتب)
#### APIs عامة (Public - لا تحتاج مصادقة):
- ✅ عرض جميع الكتب مع Pagination
- ✅ عرض تفاصيل كتاب محدد
- ✅ البحث في الكتب (بالعنوان أو المؤلف)
- ✅ فلترة الكتب (حسب التصنيف والحالة)
- ✅ ترتيب الكتب (حسب السعر، التاريخ، التقييم، إلخ)
- ✅ إحصائيات الكتب

#### APIs إدارية (Protected - تحتاج مصادقة):
- ✅ إضافة كتاب جديد
- ✅ تعديل بيانات كتاب
- ✅ حذف كتاب
- ✅ رفع صورة غلاف الكتاب

---

## 📋 متطلبات التشغيل

- PHP >= 8.2
- Composer
- قاعدة بيانات (MySQL أو SQLite)
- Laravel 12.x

---

## ⚙️ التثبيت والإعداد

### 1. تثبيت Dependencies
```bash
cd auth-backend-app
composer install
```

### 2. إعداد ملف Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 3. إعداد قاعدة البيانات

#### استخدام MySQL:
قم بتعديل ملف `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bookstore
DB_USERNAME=root
DB_PASSWORD=your_password
```

ثم قم بإنشاء قاعدة البيانات:
```sql
CREATE DATABASE bookstore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### أو استخدام SQLite:
تأكد من تثبيت SQLite PHP Extension:
```bash
# Windows
# قم بتفعيل extension=pdo_sqlite في php.ini

# Linux
sudo apt-get install php-sqlite3
```

### 4. تشغيل Migrations
```bash
php artisan migrate
```

### 5. إنشاء Symbolic Link للصور
```bash
php artisan storage:link
```

### 6. تشغيل السيرفر
```bash
php artisan serve
```
السيرفر سيعمل على: `http://localhost:8000`

---

## 📡 استخدام APIs

### Base URL
```
http://localhost:8000/api
```

### Authentication Headers
للـ APIs المحمية، أضف هذا في Headers:
```
Authorization: Bearer YOUR_TOKEN_HERE
Accept: application/json
```

---

## 📝 API Endpoints

### 🔐 Authentication

#### تسجيل مستخدم جديد
```http
POST /api/register
Content-Type: application/json

{
    "name": "أحمد محمد",
    "email": "ahmed@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### تسجيل الدخول
```http
POST /api/login
Content-Type: application/json

{
    "email": "ahmed@example.com",
    "password": "password123"
}
```

#### تسجيل الخروج
```http
POST /api/logout
Authorization: Bearer {token}
```

#### الحصول على بيانات المستخدم
```http
GET /api/me
Authorization: Bearer {token}
```

---

### 📚 Books (Public APIs)

#### عرض جميع الكتب
```http
GET /api/books?page=1&per_page=15&search=البؤساء&category=روايات&status=available&sort_by=price&sort_order=asc
```

#### عرض كتاب محدد
```http
GET /api/books/{id}
```

#### إحصائيات الكتب
```http
GET /api/books/statistics
```

---

### 🔒 Books (Protected APIs)

#### إضافة كتاب جديد
```http
POST /api/books
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "البؤساء",
    "author": "فيكتور هيجو",
    "isbn": "978-1234567890",
    "publisher": "دار النشر العربي",
    "pages": 1488,
    "category": "روايات",
    "price": 200.00,
    "discount_price": 150.00,
    "description": "رواية عالمية رائعة",
    "stock": 15,
    "status": "available"
}
```

#### تعديل كتاب
```http
PUT /api/books/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "البؤساء - الطبعة الجديدة",
    "price": 180.00,
    "stock": 20
}
```

#### حذف كتاب
```http
DELETE /api/books/{id}
Authorization: Bearer {token}
```

#### رفع صورة غلاف
```http
POST /api/books
Authorization: Bearer {token}
Content-Type: multipart/form-data

cover_image: [file]
title: "الحرب والسلام"
author: "ليو تولستوي"
...
```

---

## 📊 Database Schema

### Books Table
```
- id (bigint)
- title (string)
- author (string)
- isbn (string, unique)
- publisher (string, nullable)
- pages (integer, nullable)
- category (string)
- price (decimal 8,2)
- discount_price (decimal 8,2, nullable)
- description (text, nullable)
- cover_image (string, nullable)
- stock (integer, default: 0)
- status (enum: available, out_of_stock, coming_soon)
- rating (decimal 3,2, default: 0)
- reviews_count (integer, default: 0)
- created_at (timestamp)
- updated_at (timestamp)
```

---

## 📮 Postman Collection

يوجد ملف Postman Collection جاهز في جذر المشروع:
```
Bookstore_API.postman_collection.json
```

### استيراد Collection في Postman:
1. افتح Postman
2. اضغط على Import
3. اختر الملف `Bookstore_API.postman_collection.json`
4. Collection جاهز للاستخدام!

### Environment Variables:
- `base_url`: http://localhost:8000
- `auth_token`: يتم تعيينه تلقائياً عند Login
- `book_id`: يتم تعيينه تلقائياً عند إنشاء كتاب

---

## 🔍 أمثلة على Responses

### نجاح (Success Response)
```json
{
    "success": true,
    "message": "تم إضافة الكتاب بنجاح",
    "data": {
        "id": 1,
        "title": "البؤساء",
        "author": "فيكتور هيجو",
        "price": 200.00,
        ...
    }
}
```

### خطأ (Error Response)
```json
{
    "success": false,
    "message": "بيانات الدخول غير صحيحة",
    "errors": {
        "email": ["البريد الإلكتروني غير موجود"]
    }
}
```

---

## 🛠️ Development

### إنشاء بيانات تجريبية
```bash
php artisan tinker
```
ثم:
```php
App\Models\Book::factory()->count(50)->create();
```

### تشغيل Tests
```bash
php artisan test
```

---

## 📁 هيكل المشروع

```
auth-backend-app/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AuthController.php      # APIs المصادقة
│   │       └── BookController.php      # APIs الكتب
│   └── Models/
│       ├── User.php
│       └── Book.php
├── database/
│   └── migrations/
│       ├── create_users_table.php
│       ├── create_books_table.php
│       └── create_personal_access_tokens_table.php
├── routes/
│   └── api.php                         # جميع API Routes
├── .env                                # إعدادات البيئة
├── Bookstore_API.postman_collection.json
└── README_API.md
```

---

## 🔒 الأمان

- ✅ Laravel Sanctum للمصادقة
- ✅ Password Hashing باستخدام bcrypt
- ✅ Validation لجميع المدخلات
- ✅ CORS مفعل عبر `fruitcake/laravel-cors`
- ✅ حماية من SQL Injection عبر Eloquent ORM
- ✅ Token-based Authentication

---

## 🌐 ربط Frontend مع Backend

### في React Frontend:
```javascript
// Login Example
const response = await fetch('http://localhost:8000/api/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    body: JSON.stringify({
        email: 'ahmed@example.com',
        password: 'password123'
    })
});

const data = await response.json();
const token = data.data.token;

// حفظ Token
localStorage.setItem('auth_token', token);

// استخدام Token في Requests التالية
const booksResponse = await fetch('http://localhost:8000/api/books', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
    }
});
```

---

## ⚠️ ملاحظات هامة

1. **قاعدة البيانات**: تأكد من تثبيت MySQL أو تفعيل SQLite PHP Extension في php.ini
2. **CORS**: للسماح بـ Requests من React Frontend، قد تحتاج لإعداد CORS
3. **الصور**: الصور تُحفظ في `storage/app/public/books/` ويجب تشغيل `php artisan storage:link`
4. **Environment**: لا تنسَ نسخ `.env.example` إلى `.env` وتعديل الإعدادات

---

## 🐛 استكشاف الأخطاء

### خطأ "could not find driver"
قم بتفعيل SQLite أو MySQL driver في `php.ini`:
```ini
extension=pdo_mysql
extension=pdo_sqlite
```

### خطأ "No connection could be made"
تأكد من تشغيل MySQL Server أو استخدم SQLite.

### خطأ في Migrations
```bash
php artisan migrate:fresh
```

---

**مشروع متجر الكتب - Laravel Backend API**  
بني باستخدام ❤️ و Laravel 12
