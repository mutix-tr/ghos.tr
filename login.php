<?php
// login.php
require 'core/config.php';

// Zaten giriş yapılmışsa ana sayfaya yönlendir
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Oturum değişkenlerini tanımla
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['is_verified'] = $user['is_verified'];
        
        // Rol tabanlı yönlendirme
        if ($user['role'] == 'admin' || $user['role'] == 'mod') {
            header("Location: admin/index.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $error = "Hatalı e-posta veya şifre kombinasyonu.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sisteme Giriş - Ghos.tr</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="glass-panel" style="max-width: 400px; margin: 100px auto;">
        <h2 style="color: var(--neon-blue); text-align: center;">Ağa Bağlan</h2>
        
        <?php if($error): ?>
            <p style="color: var(--neon-red); text-align:center;"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <div style="margin-bottom: 15px;">
                <label>E-posta Adresi</label><br>
                <input type="email" name="email" style="width: 100%;" required>
            </div>
            <div style="margin-bottom: 25px;">
                <label>Şifre</label><br>
                <input type="password" name="password" style="width: 100%;" required>
            </div>
            <button type="submit" style="background: transparent; border: 1px solid var(--neon-blue); color: var(--neon-blue); padding: 10px 20px; border-radius: 5px; cursor: pointer; width: 100%; font-weight: bold;">GİRİŞ YAP</button>
        </form>
        <p style="text-align:center; margin-top: 15px;">
            <a href="register.php" style="color: #aaa; text-decoration: none;">Hesabın yok mu? Ağa Katıl</a>
        </p>
    </div>
</body>
</html>
