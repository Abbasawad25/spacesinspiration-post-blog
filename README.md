# مدوّنة فضاات الالهام 
##— دليل التثبيت

## هيكل الملفات
```
qalam/
├── index.html          ← الصفحة الرئيسية
├── database.sql        ← قاعدة البيانات
├── css/
│   └── qalam.css       ← السمات: داكن | فاتح | rixpost
├── js/
│   └── qalam.js        ← منطق التطبيق + API calls
├── php/
│   ├── config.php      ← إعدادات + PDO
│   └── api.php         ← REST API الكامل
└── uploads/            ← صور المستخدمين
```

## خطوات التثبيت

### 1. إنشاء قاعدة البيانات
```sql
-- في phpMyAdmin أو MySQL CLI:
SOURCE /path/to/qalam/database.sql;
```

### 2. ضبط الاتصال
في `php/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // اسم مستخدم MySQL
define('DB_PASS', 'كلمة_المرور'); // كلمة مرور MySQL
define('DB_NAME', 'qalam_blog');
define('BASE_URL', 'http://localhost/qalam');
```

### 3. رفع المشروع
ضع المجلد في: `htdocs/qalam/` (XAMPP) أو `www/qalam/` (WAMP)

### 4. تسجيل الدخول كمدير
```
البريد: admin@qalam.sa
كلمة المرور: Admin@123
```

## السمات الثلاث (من rixpost + qalam)
| الزر | السمة | الوصف |
|------|-------|-------|
| 🌙 | Dark | الوضع الداكن (افتراضي) |
| ☀️ | Light | الوضع الفاتح |
| 🟣 | RixPost | بنفسجي مستوحى من rixpost |

## نقاط الـ API (PHP)

| المسار | الطريقة | الوصف |
|--------|---------|-------|
| `php/api.php?path=posts` | GET | جلب المنشورات |
| `php/api.php?path=posts` | POST | إضافة منشور |
| `php/api.php?path=posts/{id}/like` | POST | إعجاب/إلغاء |
| `php/api.php?path=auth/login` | POST | تسجيل الدخول |
| `php/api.php?path=auth/register` | POST | تسجيل مستخدم |
| `php/api.php?path=auth/me` | GET | بيانات المستخدم الحالي |
| `php/api.php?path=users/{id}` | GET | ملف الناشر |
| `php/api.php?path=admin/stats` | GET | إحصائيات الإدارة |
| `php/api.php?path=admin/users` | GET | قائمة المستخدمين |
| `php/api.php?path=admin/posts` | GET | جميع المنشورات |
| `php/api.php?path=admin/messages` | GET | رسائل التواصل |
| `php/api.php?path=settings` | GET/POST | إعدادات الموقع |
| `php/api.php?path=contact` | POST | إرسال رسالة |
