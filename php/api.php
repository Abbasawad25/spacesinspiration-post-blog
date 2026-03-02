<?php
// ==========================================
// api.php — الـ API الكامل لمدوّنة القلم
// ==========================================
require_once __DIR__ . '/config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$path   = trim($_GET['path'] ?? '', '/');
$method = $_SERVER['REQUEST_METHOD'];
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

// =========================================================
switch (true) {

// =====================  AUTH  ============================

case $path === 'auth/login' && $method === 'POST':
    $stmt = getDB()->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
    $stmt->execute([trim($body['email'] ?? '')]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($body['password'] ?? '', $user['password']))
        jsonResponse(['error' => 'البريد أو كلمة المرور غير صحيحة'], 401);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['username']  = $user['username'];
    jsonResponse(['success' => true, 'user' => [
        'id' => $user['id'], 'username' => $user['username'],
        'full_name' => $user['full_name'], 'role' => $user['role'],
        'avatar' => avatarUrl($user['avatar']),
    ]]);

case $path === 'auth/register' && $method === 'POST':
    $email    = trim($body['email'] ?? '');
    $username = trim($body['username'] ?? '');
    $name     = trim($body['full_name'] ?? '');
    $pass     = $body['password'] ?? '';
    if (!$email || !$username || !$name || strlen($pass) < 6)
        jsonResponse(['error' => 'جميع الحقول مطلوبة وكلمة المرور 6 أحرف على الأقل'], 422);
    try {
        $stmt = getDB()->prepare("INSERT INTO users (username,full_name,email,password) VALUES (?,?,?,?)");
        $stmt->execute([$username, $name, $email, password_hash($pass, PASSWORD_BCRYPT)]);
        $id = getDB()->lastInsertId();
        $_SESSION['user_id']   = $id;
        $_SESSION['user_role'] = 'user';
        $_SESSION['username']  = $username;
        jsonResponse(['success' => true, 'user_id' => $id]);
    } catch (PDOException $e) {
        jsonResponse(['error' => 'البريد أو اسم المستخدم مستخدم من قبل'], 409);
    }

case $path === 'auth/logout' && $method === 'POST':
    session_destroy();
    jsonResponse(['success' => true]);

case $path === 'auth/me' && $method === 'GET':
    if (!isLoggedIn()) jsonResponse(['user' => null]);
    $stmt = getDB()->prepare("SELECT id,username,full_name,email,bio,avatar,role,followers_count,following_count,created_at FROM users WHERE id=?");
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();
    $u['avatar_url'] = avatarUrl($u['avatar']);
    jsonResponse(['user' => $u]);

// =====================  POSTS  ===========================

case $path === 'posts' && $method === 'GET':
    $cat    = $_GET['category'] ?? '';
    $q      = $_GET['q'] ?? '';
    $page   = max(1, (int)($_GET['page'] ?? 1));
    $limit  = 10;
    $offset = ($page - 1) * $limit;
    $where  = "p.status='published'";
    $params = [];
    if ($cat && $cat !== 'all') { $where .= " AND p.category=?";           $params[] = $cat; }
    if ($q)                      { $where .= " AND (p.title LIKE ? OR p.body LIKE ?)"; $qp = "%$q%"; $params[] = $qp; $params[] = $qp; }

    // هل أعجب المستخدم الحالي؟
    $likedJoin = isLoggedIn()
        ? "LEFT JOIN post_likes pl ON pl.post_id=p.id AND pl.user_id=" . (int)$_SESSION['user_id']
        : '';
    $likedSel  = isLoggedIn() ? ", IF(pl.id IS NOT NULL,1,0) as liked" : ", 0 as liked";

    $sql = "SELECT p.id, p.title, p.body, p.category, p.likes_count, p.views_count, p.created_at,
                   u.id as author_id, u.full_name as author_name, u.username as author_username, u.avatar as author_avatar
                   $likedSel
            FROM posts p
            JOIN users u ON p.user_id=u.id
            $likedJoin
            WHERE $where
            ORDER BY p.created_at DESC
            LIMIT $limit OFFSET $offset";
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();
    foreach ($posts as &$p) {
        $p['author_avatar_url'] = avatarUrl($p['author_avatar']);
        $p['created_ago'] = timeAgo($p['created_at']);
        $p['cat_label']   = getCatLabel($p['category']);
        $p['cat_class']   = getCatClass($p['category']);
    }

    $cntStmt = getDB()->prepare("SELECT COUNT(*) FROM posts p WHERE $where");
    $cntStmt->execute($params);
    jsonResponse(['posts' => $posts, 'total' => (int)$cntStmt->fetchColumn(), 'page' => $page, 'limit' => $limit]);

case $path === 'posts' && $method === 'POST':
    requireAuth();
    $title = trim($body['title'] ?? '');
    $text  = trim($body['body'] ?? '');
    $cat   = $body['category'] ?? 'tech';
    if (!$title || !$text) jsonResponse(['error' => 'العنوان والمحتوى مطلوبان'], 422);
    $stmt = getDB()->prepare("INSERT INTO posts (user_id,title,body,category) VALUES (?,?,?,?)");
    $stmt->execute([$_SESSION['user_id'], $title, $text, $cat]);
    jsonResponse(['success' => true, 'id' => getDB()->lastInsertId()]);

case preg_match('/^posts\/(\d+)$/', $path, $m) && $method === 'GET':
    $stmt = getDB()->prepare("SELECT p.*, u.full_name as author_name, u.username as author_username, u.avatar as author_avatar, u.bio as author_bio
                               FROM posts p JOIN users u ON p.user_id=u.id WHERE p.id=?");
    $stmt->execute([$m[1]]);
    $post = $stmt->fetch();
    if (!$post) jsonResponse(['error' => 'غير موجود'], 404);
    getDB()->prepare("UPDATE posts SET views_count=views_count+1 WHERE id=?")->execute([$m[1]]);
    $post['author_avatar_url'] = avatarUrl($post['author_avatar']);
    $post['created_ago'] = timeAgo($post['created_at']);
    $post['cat_label']   = getCatLabel($post['category']);
    jsonResponse($post);

case preg_match('/^posts\/(\d+)$/', $path, $m) && $method === 'PUT':
    requireAuth();
    $stmt = getDB()->prepare("UPDATE posts SET title=?,body=?,category=?,updated_at=NOW() WHERE id=? AND user_id=?");
    $stmt->execute([$body['title'], $body['body'], $body['category'], $m[1], $_SESSION['user_id']]);
    jsonResponse(['success' => true]);

case preg_match('/^posts\/(\d+)$/', $path, $m) && $method === 'DELETE':
    requireAuth();
    $where = isAdmin() ? "id=?" : "id=? AND user_id=" . (int)$_SESSION['user_id'];
    getDB()->prepare("DELETE FROM posts WHERE $where")->execute([$m[1]]);
    jsonResponse(['success' => true]);

case preg_match('/^posts\/(\d+)\/like$/', $path, $m) && $method === 'POST':
    requireAuth();
    try {
        getDB()->prepare("INSERT INTO post_likes (post_id,user_id) VALUES (?,?)")->execute([$m[1], $_SESSION['user_id']]);
        getDB()->prepare("UPDATE posts SET likes_count=likes_count+1 WHERE id=?")->execute([$m[1]]);
        $cnt = (int)getDB()->prepare("SELECT likes_count FROM posts WHERE id=?")->execute([$m[1]]) && getDB()->query("SELECT likes_count FROM posts WHERE id={$m[1]}")->fetchColumn();
        jsonResponse(['liked' => true, 'count' => $cnt]);
    } catch (PDOException) {
        getDB()->prepare("DELETE FROM post_likes WHERE post_id=? AND user_id=?")->execute([$m[1], $_SESSION['user_id']]);
        getDB()->prepare("UPDATE posts SET likes_count=likes_count-1 WHERE id=?")->execute([$m[1]]);
        $cnt = (int)getDB()->query("SELECT likes_count FROM posts WHERE id={$m[1]}")->fetchColumn();
        jsonResponse(['liked' => false, 'count' => $cnt]);
    }

// =====================  USERS  ===========================

case preg_match('/^users\/(\d+)$/', $path, $m) && $method === 'GET':
    $stmt = getDB()->prepare("SELECT id,username,full_name,bio,avatar,role,followers_count,following_count,created_at FROM users WHERE id=?");
    $stmt->execute([$m[1]]);
    $user = $stmt->fetch();
    if (!$user) jsonResponse(['error' => 'غير موجود'], 404);
    $user['avatar_url'] = avatarUrl($user['avatar']);
    $postStmt = getDB()->prepare("SELECT id,title,category,likes_count,views_count,created_at FROM posts WHERE user_id=? AND status='published' ORDER BY created_at DESC LIMIT 20");
    $postStmt->execute([$m[1]]);
    $posts = $postStmt->fetchAll();
    foreach ($posts as &$p) { $p['created_ago'] = timeAgo($p['created_at']); $p['cat_label'] = getCatLabel($p['category']); }
    $user['posts'] = $posts;
    jsonResponse($user);

case $path === 'users/update' && $method === 'POST':
    requireAuth();
    $stmt = getDB()->prepare("UPDATE users SET full_name=?,bio=?,email=?,updated_at=NOW() WHERE id=?");
    $stmt->execute([$body['full_name'], $body['bio'], $body['email'], $_SESSION['user_id']]);
    if (!empty($body['password']) && strlen($body['password']) >= 6) {
        getDB()->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($body['password'], PASSWORD_BCRYPT), $_SESSION['user_id']]);
    }
    jsonResponse(['success' => true]);

case $path === 'users/avatar' && $method === 'POST':
    requireAuth();
    if (!isset($_FILES['avatar'])) jsonResponse(['error' => 'لا توجد صورة'], 400);
    $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) jsonResponse(['error' => 'نوع الصورة غير مدعوم'], 400);
    if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) jsonResponse(['error' => 'الصورة أكبر من 2MB'], 400);
    $filename = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    move_uploaded_file($_FILES['avatar']['tmp_name'], UPLOAD_DIR . $filename);
    getDB()->prepare("UPDATE users SET avatar=? WHERE id=?")->execute([$filename, $_SESSION['user_id']]);
    jsonResponse(['success' => true, 'avatar_url' => UPLOAD_URL . $filename]);

// =====================  ADMIN  ===========================

case $path === 'admin/stats' && $method === 'GET':
    requireAdmin();
    $db = getDB();
    jsonResponse([
        'users'    => (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'posts'    => (int)$db->query("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn(),
        'likes'    => (int)$db->query("SELECT COALESCE(SUM(likes_count),0) FROM posts")->fetchColumn(),
        'views'    => (int)$db->query("SELECT COALESCE(SUM(views_count),0) FROM posts")->fetchColumn(),
        'messages' => (int)$db->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn(),
    ]);

case $path === 'admin/users' && $method === 'GET':
    requireAdmin();
    $stmt = getDB()->query("SELECT id,username,full_name,email,role,followers_count,created_at,
                             (SELECT COUNT(*) FROM posts WHERE user_id=users.id) as posts_count
                            FROM users ORDER BY created_at DESC");
    jsonResponse($stmt->fetchAll());

case preg_match('/^admin\/users\/(\d+)\/role$/', $path, $m) && $method === 'PUT':
    requireAdmin();
    getDB()->prepare("UPDATE users SET role=? WHERE id=?")->execute([$body['role'], $m[1]]);
    jsonResponse(['success' => true]);

case preg_match('/^admin\/users\/(\d+)$/', $path, $m) && $method === 'DELETE':
    requireAdmin();
    if ((int)$m[1] === 1) jsonResponse(['error' => 'لا يمكن حذف المدير الرئيسي'], 403);
    getDB()->prepare("DELETE FROM users WHERE id=?")->execute([$m[1]]);
    jsonResponse(['success' => true]);

case $path === 'admin/posts' && $method === 'GET':
    requireAdmin();
    $stmt = getDB()->query("SELECT p.id,p.title,p.category,p.status,p.likes_count,p.views_count,p.created_at,u.full_name as author_name
                            FROM posts p JOIN users u ON p.user_id=u.id ORDER BY p.created_at DESC");
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) { $r['cat_label'] = getCatLabel($r['category']); }
    jsonResponse($rows);

case preg_match('/^admin\/posts\/(\d+)\/status$/', $path, $m) && $method === 'PUT':
    requireAdmin();
    getDB()->prepare("UPDATE posts SET status=? WHERE id=?")->execute([$body['status'], $m[1]]);
    jsonResponse(['success' => true]);

case preg_match('/^admin\/posts\/(\d+)$/', $path, $m) && $method === 'DELETE':
    requireAdmin();
    getDB()->prepare("DELETE FROM posts WHERE id=?")->execute([$m[1]]);
    jsonResponse(['success' => true]);

case $path === 'admin/messages' && $method === 'GET':
    requireAdmin();
    $stmt = getDB()->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
    jsonResponse($stmt->fetchAll());

case preg_match('/^admin\/messages\/(\d+)\/read$/', $path, $m) && $method === 'PUT':
    requireAdmin();
    getDB()->prepare("UPDATE contact_messages SET is_read=1 WHERE id=?")->execute([$m[1]]);
    jsonResponse(['success' => true]);

case preg_match('/^admin\/messages\/(\d+)$/', $path, $m) && $method === 'DELETE':
    requireAdmin();
    getDB()->prepare("DELETE FROM contact_messages WHERE id=?")->execute([$m[1]]);
    jsonResponse(['success' => true]);

// =====================  SETTINGS  ========================

case $path === 'settings' && $method === 'GET':
    $rows = getDB()->query("SELECT `key`,value FROM site_settings")->fetchAll();
    $s = [];
    foreach ($rows as $r) $s[$r['key']] = $r['value'];
    jsonResponse($s);

case $path === 'settings' && $method === 'POST':
    requireAdmin();
    foreach ($body as $k => $v) {
        getDB()->prepare("INSERT INTO site_settings(`key`,value) VALUES(?,?) ON DUPLICATE KEY UPDATE value=?")->execute([$k, $v, $v]);
    }
    jsonResponse(['success' => true]);

// =====================  CONTACT  =========================

case $path === 'contact' && $method === 'POST':
    $name    = trim($body['name'] ?? '');
    $email   = trim($body['email'] ?? '');
    $subject = trim($body['subject'] ?? '');
    $message = trim($body['message'] ?? '');
    if (!$name || !$email || !$message) jsonResponse(['error' => 'جميع الحقول مطلوبة'], 422);
    getDB()->prepare("INSERT INTO contact_messages (name,email,subject,message) VALUES (?,?,?,?)")->execute([$name, $email, $subject, $message]);
    jsonResponse(['success' => true]);

// ====================================================
default:
    jsonResponse(['error' => 'المسار غير موجود: ' . $path], 404);
}
