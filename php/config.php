<?php
// ==========================================
// إعدادات قاعدة البيانات — مدوّنة القلم
// ==========================================
define('DB_HOST', 'localhost:3306');
define('DB_USER', 'root');
define('DB_PASS', '');           // ← غيّر كلمة المرور
define('DB_NAME', 'qalam_blog');
define('DB_CHARSET', 'utf8mb4');
define('BASE_URL', 'http://localhost:8080/qalam_php/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');

// ---- اتصال PDO ----
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

// ---- الجلسة ----
if (session_status() === PHP_SESSION_NONE) session_start();

// ---- ردود JSON ----
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ---- مصادقة ----
function isLoggedIn(): bool    { return !empty($_SESSION['user_id']); }
function isAdmin(): bool        { return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin'; }
function requireAuth(): void    { if (!isLoggedIn())  jsonResponse(['error' => 'يجب تسجيل الدخول'], 401); }
function requireAdmin(): void   { requireAuth(); if (!isAdmin()) jsonResponse(['error' => 'صلاحيات غير كافية'], 403); }

// ---- مساعدات ----
function h(string $s): string  { return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8'); }
function fmtDate(string $d): string {
    $months = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
    $t = strtotime($d);
    return $months[(int)date('n', $t) - 1] . ' ' . date('Y', $t);
}
function timeAgo(string $d): string {
    $diff = time() - strtotime($d);
    if ($diff < 60)    return 'منذ لحظات';
    if ($diff < 3600)  return 'منذ ' . floor($diff/60) . ' دقيقة';
    if ($diff < 86400) return 'منذ ' . floor($diff/3600) . ' ساعة';
    return 'منذ ' . floor($diff/86400) . ' يوم';
}
function avatarUrl(?string $avatar): string {
    if ($avatar && $avatar !== 'default.png') return UPLOAD_URL . $avatar;
    return '';
}
function getCatLabel(string $cat): string {
    return match($cat) {
        'tech'      => '💻 تقنية',
        'culture'   => '📚 ثقافة',
        'lifestyle' => '🌿 أسلوب حياة',
        'quote'     => '💬 اقتباسات',
        'news'      => '📰 أخبار',
        default     => $cat,
    };
}
function getCatClass(string $cat): string {
    return match($cat) {
        'tech'      => 'cat-tech',
        'culture'   => 'cat-culture',
        'lifestyle' => 'cat-lifestyle',
        'quote'     => 'cat-quote',
        default     => 'cat-tech',
    };
}

// ---- CSRF ----
function csrfToken(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verifyCsrf(): void {
    $t = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if ($t !== ($_SESSION['csrf'] ?? '')) jsonResponse(['error' => 'رمز CSRF غير صحيح'], 403);
}
