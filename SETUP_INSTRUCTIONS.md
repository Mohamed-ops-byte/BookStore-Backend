# تعليمات تشغيل Backend (Laravel)

## خطوات التشغيل السريع

### 1. تفعيل SQLite Driver في PHP

#### على Windows:
1. افتح ملف `php.ini` (موقعه عادة في: `C:\xampp\php\php.ini` أو `C:\php\php.ini`)
2. ابحث عن السطور التالية وتأكد من حذف `;` من بدايتها:
```ini
extension=pdo_sqlite
extension=sqlite3
```
3. احفظ الملف وأعد تشغيل Terminal

### 2. تشغيل Migrations وإضافة البيانات التجريبية

```bash
cd auth-backend-app

# تشغيل Migrations لإنشاء الجداول
php artisan migrate

# إضافة بيانات تجريبية (15 كتاب)
php artisan db:seed --class=BookSeeder

# أو تشغيل الأمرين معاً (حذف وإعادة إنشاء كل شيء)
php artisan migrate:fresh --seed
```

### 3. تشغيل السيرفر

```bash
php artisan serve
```

السيرفر سيعمل على: `http://localhost:8000`

---

## اختبار APIs

### طريقة 1: استخدام Postman
1. افتح Postman
2. اذهب إلى Import
3. اختر ملف `Bookstore_API.postman_collection.json`
4. جرب الـ APIs:
   - ابدأ بـ Register لإنشاء حساب
   - Login للحصول على Token
   - استخدم Token في باقي الـ APIs

### طريقة 2: استخدام CURL

#### تسجيل مستخدم جديد
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"name\":\"أحمد محمد\",\"email\":\"ahmed@test.com\",\"password\":\"password123\",\"password_confirmation\":\"password123\"}"
```

#### تسجيل الدخول
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"ahmed@test.com\",\"password\":\"password123\"}"
```

#### عرض جميع الكتب
```bash
curl -X GET http://localhost:8000/api/books \
  -H "Accept: application/json"
```

#### إضافة كتاب (يحتاج Token)
```bash
curl -X POST http://localhost:8000/api/books \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d "{\"title\":\"كتاب جديد\",\"author\":\"مؤلف\",\"isbn\":\"978-0000000000\",\"category\":\"روايات\",\"price\":100,\"stock\":10,\"status\":\"available\"}"
```

---

## ملف قاعدة البيانات

قاعدة البيانات SQLite موجودة في:
```
auth-backend-app/database/database.sqlite
```

---

## الجداول المتوفرة

### users
- id
- name
- email
- password
- created_at
- updated_at

### books
- id
- title
- author
- isbn
- publisher
- pages
- category
- price
- discount_price
- description
- cover_image
- stock
- status (available, out_of_stock, coming_soon)
- rating
- reviews_count
- created_at
- updated_at

### personal_access_tokens (للمصادقة)
- id
- tokenable_type
- tokenable_id
- name
- token
- abilities
- last_used_at
- expires_at
- created_at
- updated_at

---

## البيانات التجريبية المتوفرة (15 كتاب)

بعد تشغيل `php artisan db:seed --class=BookSeeder`، ستجد:

1. البؤساء - فيكتور هيجو (روايات)
2. الحرب والسلام - ليو تولستوي (روايات)
3. مئة عام من العزلة - غابرييل غارسيا ماركيز (روايات)
4. أصل الأنواع - تشارلز داروين (علمية)
5. موجز تاريخ الزمن - ستيفن هوكينج (علمية)
6. تاريخ الحضارات - ويل ديورانت (تاريخية)
7. فن الحرب - سون تزو (فلسفة)
8. ذاكرة الجسد - أحلام مستغانمي (روايات)
9. السيرة النبوية - ابن هشام (دينية)
10. الخيميائي - باولو كويلو (روايات)
11. العادات السبع للناس الأكثر فعالية - ستيفن كوفي (تطوير ذاتي)
12. الأمير - نيكولو مكيافيلي (فلسفة) - **نفذت الكمية**
13. الجريمة والعقاب - فيودور دوستويفسكي (روايات)
14. الذكاء العاطفي - دانيال جولمان (تطوير ذاتي)
15. قصة الحضارة الإسلامية - راغب السرجاني (تاريخية) - **قريباً**

---

## ربط React Frontend مع Backend

في مشروع React (`auth-ui-app`)، استخدم الكود التالي:

### مثال: Login في React
```javascript
// في صفحة Login.js

const handleSubmit = async (e) => {
  e.preventDefault();
  
  try {
    const response = await fetch('http://localhost:8000/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        email: email,
        password: password
      })
    });

    const data = await response.json();
    
    if (data.success) {
      // حفظ Token
      localStorage.setItem('auth_token', data.data.token);
      localStorage.setItem('user', JSON.stringify(data.data.user));
      
      // التوجيه إلى Dashboard
      window.location.href = '/dashboard';
    } else {
      alert('خطأ في تسجيل الدخول');
    }
  } catch (error) {
    console.error('Error:', error);
    alert('حدث خطأ أثناء الاتصال بالسيرفر');
  }
};
```

### مثال: عرض الكتب في React
```javascript
// في صفحة BookList.js

useEffect(() => {
  const fetchBooks = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/books');
      const data = await response.json();
      
      if (data.success) {
        setBooks(data.data.data); // Pagination data
      }
    } catch (error) {
      console.error('Error:', error);
    }
  };

  fetchBooks();
}, []);
```

### مثال: إضافة كتاب (Protected)
```javascript
// في صفحة BookCreate.js

const handleSubmit = async (e) => {
  e.preventDefault();
  
  const token = localStorage.getItem('auth_token');
  
  try {
    const response = await fetch('http://localhost:8000/api/books', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        title: title,
        author: author,
        isbn: isbn,
        category: category,
        price: price,
        stock: stock,
        status: 'available'
      })
    });

    const data = await response.json();
    
    if (data.success) {
      alert('تم إضافة الكتاب بنجاح');
      // التوجيه إلى قائمة الكتب
    }
  } catch (error) {
    console.error('Error:', error);
  }
};
```

---

## استكشاف الأخطاء الشائعة

### خطأ: "could not find driver"
**الحل**: تأكد من تفعيل SQLite في php.ini

### خطأ: CORS
إذا ظهر خطأ CORS عند الاتصال من React:
```
Access to fetch at 'http://localhost:8000/api/books' from origin 'http://localhost:3000' has been blocked by CORS policy
```

**الحل**: Laravel 12 يأتي مع CORS مفعل افتراضياً، لكن تأكد من:
1. إرسال Header: `Accept: application/json` في جميع الطلبات
2. إذا استمرت المشكلة، تحقق من `config/cors.php`

### خطأ: "Unauthenticated"
**الحل**: تأكد من إرسال Token في Header:
```
Authorization: Bearer YOUR_TOKEN_HERE
```

---

## أوامر مفيدة

```bash
# مسح قاعدة البيانات وإعادة إنشائها مع البيانات التجريبية
php artisan migrate:fresh --seed

# عرض جميع الـ Routes
php artisan route:list

# فتح Tinker للتجربة
php artisan tinker

# في Tinker - إنشاء مستخدم جديد
User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => Hash::make('password123')]);

# في Tinker - عرض جميع الكتب
Book::all();

# في Tinker - عرض أول كتاب
Book::first();

# مسح Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

**جاهز للاستخدام! 🚀**
