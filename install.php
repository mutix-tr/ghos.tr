<?php
// install.php
$host = 'localhost';
$db   = 'ghos_tr'; // Cpanel/aaPanel'de oluşturduğun DB adı
$user = 'root';    // Veritabanı kullanıcısı
$pass = 'sifren';  // Veritabanı şifresi

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Veritabanını oluştur ve seç
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
    $pdo->exec("USE `$db`;");

    // db klasöründeki SQL dosyalarını çalıştır
    $sqlFiles = ['user.sql', 'story.sql', 'comment.sql', 'settings.sql'];
    
    foreach ($sqlFiles as $file) {
        $filePath = __DIR__ . '/db/' . $file;
        if (file_exists($filePath)) {
            $sql = file_get_contents($filePath);
            $pdo->exec($sql);
            echo "<p style='color: #00ffcc;'>✅ $file başarıyla kuruldu.</p>";
        } else {
            echo "<p style='color: #ff3366;'>❌ $file bulunamadı!</p>";
        }
    }
    
    // Varsayılan Admin Hesabı (Şifre: admin123)
    $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO users (username, email, password, role, is_verified) 
                VALUES ('GhosAdmin', 'admin@ghos.tr', '$adminPass', 'admin', 1)");
                
    echo "<h3 style='color: #00ffcc;'>Kurulum Tamamlandı! Lütfen install.php dosyasını silin.</h3>";

} catch (PDOException $e) {
    die("<p style='color: #ff3366;'>Bağlantı hatası: " . $e->getMessage() . "</p>");
}
?>
