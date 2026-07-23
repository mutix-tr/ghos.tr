<?php
// register.php
require 'core/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Tüm alanlar, özellikle e-posta zorunludur.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Geçersiz e-posta formatı.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password]);
            $success = "Kayıt başarılı! Siber ağa hoş geldin.";
        } catch (PDOException $e) {
            $error = "Bu kullanıcı adı veya e-posta zaten kullanımda.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kayıt Ol - Ghos.tr</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="glass-panel" style="max-width: 400px; margin: 50px auto;">
        <h2 style="color: var(--neon-green); text-align: center;">Ağa Katıl</h2>
        
        <?php if($error): ?>
            <p style="color: var(--neon-red);"><?= $error ?></p>
        <?php endif; ?>
        <?php if($success): ?>
            <p style="color: var(--neon-green);"><?= $success ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <div style="margin-bottom: 15px;">
                <label>Kullanıcı Adı</label><br>
                <input type="text" name="username" style="width: 100%;" required>
            </div>
            <div style="margin-bottom: 15px;">
                <label>E-posta (Zorunlu)</label><br>
                <input type="email" name="email" style="width: 100%;" required>
            </div>
            <div style="margin-bottom: 15px;">
                <label>Şifre</label><br>
                <input type="password" name="password" style="width: 100%;" required>
            </div>
            <button type="submit" style="background: transparent; border: 1px solid var(--neon-green); color: var(--neon-green); padding: 10px 20px; border-radius: 5px; cursor: pointer; width: 100%;">KAYIT OL</button>
        </form>
    </div>
</body>
</html>
