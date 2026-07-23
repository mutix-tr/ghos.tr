<?php
// admin/index.php
require '../core/config.php';

if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'mod')) {
    die("Erişim Reddedildi.");
}
$isAdmin = ($_SESSION['user_role'] === 'admin');

// 1. Kullanıcı Rolü ve Onay Güncelleme (Sadece Admin)
if ($isAdmin && isset($_POST['update_user'])) {
    $target_user_id = (int)$_POST['user_id'];
    $new_role = $_POST['role'];
    $is_verified = isset($_POST['is_verified']) ? 1 : 0;
    
    $pdo->prepare("UPDATE users SET role = ?, is_verified = ? WHERE id = ?")->execute([$new_role, $is_verified, $target_user_id]);
    header("Location: index.php");
    exit;
}

// 2. Yorum Silme (Admin & Mod)
if (isset($_GET['delete_comment'])) {
    $pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([(int)$_GET['delete_comment']]);
    header("Location: index.php");
    exit;
}

// Verileri Çek
$users = $isAdmin ? $pdo->query("SELECT id, username, email, role, is_verified FROM users ORDER BY created_at DESC")->fetchAll() : [];
$comments = $pdo->query("SELECT c.id, c.content, u.username, s.title FROM comments c JOIN users u ON c.user_id = u.id JOIN stories s ON c.story_id = s.id ORDER BY c.created_at DESC LIMIT 20")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Gelişmiş Yönetim Paneli</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.9em; }
        th, td { padding: 10px; border-bottom: 1px solid var(--glass-border); text-align: left; }
        .btn-small { padding: 3px 8px; border: 1px solid var(--neon-red); color: var(--neon-red); text-decoration: none; border-radius: 3px; font-size: 0.8em; }
    </style>
</head>
<body>
    <div style="max-width: 1400px; margin: 0 auto;">
        <h1 style="color: var(--neon-red);">Sistem Çekirdeği (Ghos.tr)</h1>
        <a href="../index.php" style="color: var(--neon-blue); text-decoration:none;">&lt; Ağa Dön</a>
        
        <div class="grid-3" style="margin-top: 20px;">
            
            <?php if($isAdmin): ?>
            <!-- KULLANICI YÖNETİMİ -->
            <div class="glass-panel" style="grid-column: span 2;">
                <h3 style="color: var(--neon-green);">Ajan Veritabanı (Kullanıcılar)</h3>
                <table>
                    <tr><th>Kullanıcı</th><th>E-posta</th><th>Rol</th><th>Onaylı (Yeşil Tik)</th><th>İşlem</th></tr>
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <form method="POST">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <td>
                                <select name="role" style="background: #000; color: #fff; padding: 2px;">
                                    <option value="user" <?= $u['role']=='user'?'selected':'' ?>>User</option>
                                    <option value="mod" <?= $u['role']=='mod'?'selected':'' ?>>Moderatör</option>
                                    <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
                                </select>
                            </td>
                            <td>
                                <input type="checkbox" name="is_verified" value="1" <?= $u['is_verified'] ? 'checked' : '' ?>>
                            </td>
                            <td>
                                <button type="submit" name="update_user" style="background: transparent; color: var(--neon-blue); border: 1px solid var(--neon-blue); cursor: pointer;">Kaydet</button>
                            </td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php endif; ?>

            <!-- YORUM MODERASYONU -->
            <div class="glass-panel">
                <h3 style="color: var(--neon-blue);">Son Sinyaller (Yorumlar)</h3>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach($comments as $c): ?>
                    <li style="border-bottom: 1px solid var(--glass-border); padding: 10px 0;">
                        <strong style="color: #fff;"><?= htmlspecialchars($c['username']) ?></strong> 
                        <span style="font-size: 0.8em; color: #888;">(<?= htmlspecialchars($c['title']) ?>)</span><br>
                        <span style="color: #ccc; font-size: 0.9em;"><?= htmlspecialchars($c['content']) ?></span>
                        <div style="margin-top: 5px;">
                            <a href="?delete_comment=<?= $c['id'] ?>" class="btn-small" onclick="return confirm('Silmek emin misiniz?');">Sinyali Kes (Sil)</a>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        </div>
    </div>
</body>
</html>
