-- ================================================
-- مدوّنة القلم - قاعدة البيانات
-- ================================================
CREATE DATABASE IF NOT EXISTS qalam_blog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE qalam_blog;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    bio TEXT,
    avatar VARCHAR(255) DEFAULT 'default.png',
    role ENUM('user','admin') DEFAULT 'user',
    followers_count INT DEFAULT 0,
    following_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    category ENUM('tech','culture','lifestyle','quote','news') DEFAULT 'tech',
    likes_count INT DEFAULT 0,
    views_count INT DEFAULT 0,
    status ENUM('published','draft','archived') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS post_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO users (username, full_name, email, password, bio, role) VALUES
('admin', 'مدير النظام', 'admin@qalam.sa', '$2y$12$hash1', 'مدير الموقع', 'admin'),
('ahmed_qalam', 'أحمد القلم الحر', 'ahmed@qalam.sa', '$2y$12$hash2', 'كاتب ومدوّن متخصص في التقنية والفلسفة.', 'user'),
('salma_hakim', 'سلمى الحكيم', 'salma@qalam.sa', '$2y$12$hash3', 'محررة ثقافية وكاتبة قصة قصيرة.', 'user'),
('lina_mousa', 'لينا الموسى', 'lina@qalam.sa', '$2y$12$hash4', 'موسيقية وكاتبة محتوى.', 'user');

INSERT INTO posts (user_id, title, body, category, likes_count, views_count) VALUES
(2,'الذكاء الاصطناعي وإعادة تشكيل مستقبل الإبداع','لم يعد الذكاء الاصطناعي مجرد أداة تقنية باردة، بل أصبح شريكاً في رحلة الإبداع الإنساني.','tech',342,1840),
(3,'القهوة وفلسفة اللحظة الراهنة','في كل فنجان قهوة صباحي حكاية. تلك الرائحة التي تسبق الفكرة.','culture',218,930),
(2,'الجرأة لا تعني غياب الخوف','نيلسون مانديلا — الجرأة لا تعني غياب الخوف، بل إدراك أن ثمة ما هو أهم من الخوف.','quote',891,3200),
(2,'فن البطء: كيف نستعيد الوقت المسروق؟','نعيش في حضارة تُقدّس السرعة وتُدين البطء.','lifestyle',563,2100),
(2,'الخصوصية في الزمن الرقمي: وهم أم حق؟','بيانات تُباع دون علمك، خوارزميات تتنبأ بقراراتك قبل أن تقررها.','tech',427,1750),
(4,'الموسيقى العربية وجذور الروح','حين تسمع فيروز تُغني تشعر أن كل لحظة ضائعة في حياتك تعود إليك مُكتملة.','culture',734,2800);

INSERT INTO site_settings (`key`, value) VALUES
('site_name','مدوّنة القلم'),('site_tagline','أفكار · قصص · إلهام'),
('site_email','contact@qalam.sa'),('site_twitter','@qalam_blog'),
('posts_per_page','10'),('allow_comments','1'),('maintenance_mode','0'),
('primary_color','#f0c040'),('default_lang','ar');

INSERT INTO contact_messages (name, email, subject, message) VALUES
('محمد الأحمد','mohammad@example.com','استفسار عن الكتابة','أريد أن أعرف كيف يمكنني المساهمة في الموقع.'),
('فاطمة الزهراني','fatima@example.com','اقتراح موضوع','أقترح كتابة مقال عن تأثير وسائل التواصل على الصحة النفسية.');
