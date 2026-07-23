<?php
// install.php - Tam Otomatik Ghos.tr Kurulum Motoru

// 1. Dizin Ayarları
$db_dir = __DIR__ . '/db';
$db_file = $db_dir . '/ghostr.sqlite';

// Klasör yoksa oluştur
if (!file_exists($db_dir)) {
    mkdir($db_dir, 0755, true);
}

// 2. Ayrı Ayrı Oluşturulacak SQL Dosyalarının İçerikleri
$sql_files_content = [
    'user.sql' => "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            role TEXT DEFAULT 'user' CHECK(role IN ('user', 'mod', 'admin')),
            is_verified INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ",
    'category.sql' => "
        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT NOT NULL
        );
        INSERT OR IGNORE INTO categories (name, slug) VALUES 
        ('Korku', 'korku'), ('Bilimkurgu', 'bilimkurgu'), ('Siberpunk', 'siberpunk'), ('Distopya', 'distopya');
    ",
    'settings.sql' => "
        CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            setting_key TEXT NOT NULL UNIQUE,
            setting_value TEXT NOT NULL
        );
        INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES ('require_story_approval', '1');
    ",
    'story.sql' => "
        CREATE TABLE IF NOT EXISTS stories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            author_id INTEGER NOT NULL,
            category_id INTEGER DEFAULT 1,
            title TEXT NOT NULL,
            content TEXT NOT NULL,
            cover_image TEXT DEFAULT NULL,
            status TEXT DEFAULT 'pending' CHECK(status IN ('pending', 'approved')),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        );
    ",
    'comment.sql' => "
        CREATE TABLE IF NOT EXISTS comments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            story_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
    "
];

// 3. SQL Dosyalarını db/ Klasörüne Fiziksel Olarak Yaz
foreach ($sql_files_content as $filename => $content) {
    file_put_contents($db_dir . '/' . $filename, trim($content));
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ghos.tr Sistem Kurulumu</title>
    <style>
        body { background: #0a0a0f; color: #00ffcc; font-family: monospace; padding: 40px; }
        .terminal { background: rgba(0,0,0,0.8); border: 1px solid #00e5ff; padding: 20px; border-radius: 10px; max-width: 800px; margin: 0 auto; box-shadow: 0 0 20px rgba(0, 229, 255, 0.2); }
        .success { color: #00ffcc; }
        .error { color: #ff3366; }
        .info { color: #aaa; }
    </style>
</head>
<body>
    <div class="terminal">
        <h2>GHOS.TR // OTOMATİK KURULUM PROTOKOLÜ</h2>
        <hr style="border-color: #00e5ff; opacity: 0.3;">
        
        <?php
        try {
            echo "<p class='info'>[SİSTEM] Veritabanı motoru başlatılıyor (SQLite)...</p>";
            
            // SQLite Bağlantısı
            $pdo = new PDO("sqlite:" . $db_file);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("PRAGMA foreign_keys = ON;");
            
            // Fiziksel dosyaları sırasıyla okuyup veritabanına kur
            $sqlFiles = ['user.sql', 'category.sql', 'settings.sql', 'story.sql', 'comment.sql'];
            
            foreach ($sqlFiles as $file) {
                $filePath = $db_dir . '/' . $file;
                if (file_exists($filePath)) {
                    $sql = file_get_contents($filePath);
                    $pdo->exec($sql);
                    echo "<p class='success'>[BAŞARILI] $file dosyası oluşturuldu ve veritabanına işlendi.</p>";
                } else {
                    echo "<p class='error'>[HATA] $file yazılamadı!</p>";
                }
            }

            // Varsayılan Admin Hesabı
            $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
            $checkAdmin = $pdo->query("SELECT id FROM users WHERE username = 'GhosAdmin'")->fetch();
            
            if (!$checkAdmin) {
                $pdo->exec("INSERT INTO users (username, email, password, role, is_verified) 
                            VALUES ('GhosAdmin', 'admin@ghos.tr', '$adminPass', 'admin', 1)");
                echo "<p class='success' style='color: #ff3366;'>[YETKİ] Admin hesabı oluşturuldu. (Kullanıcı: admin@ghos.tr | Şifre: admin123)</p>";
            }
            
            echo "<h3 class='success' style='margin-top: 30px;'>SİSTEM AĞA BAĞLANDI! ✅</h3>";
            echo "<p class='error' style='border: 1px dashed #ff3366; padding: 10px;'>GÜVENLİK UYARISI: Lütfen bu install.php dosyasını sunucunuzdan hemen silin.</p>";

        } catch (PDOException $e) {
            echo "<p class='error'>[KRİTİK HATA] Kurulum başarısız: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>
</body>
</html>
