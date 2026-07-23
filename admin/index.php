<?php
// admin/index.php
require '../core/config.php';

// Yetki Kontrolü
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'mod')) {
    die("Bu alana erişim yetkiniz yok.");
}

$isAdmin = ($_SESSION['user_role'] === 'admin');

// 1. Ayar Güncelleme (Sadece Admin)
if ($isAdmin && isset($_POST['update_setting'])) {
    $val = $_POST['require_approval'] == '1' ? '1' : '0';
    $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'require_story_approval'")->execute([$val]);
}

// 2. Hikaye Onaylama/Silme (Admin ve Mod)
if (isset($_GET['action']) && isset($_GET['story_id'])) {
    $story_id = (int)$_GET['story_id'];
    if ($_GET['action'] == 'approve') {
        $pdo->prepare("UPDATE stories SET status = 'approved' WHERE id = ?")->execute([$story_id]);
    } elseif ($_GET['action'] == 'delete') {
        $pdo->prepare("DELETE FROM stories WHERE id = ?")->execute([$story_id]);
    }
    header("Location: index.php");
    exit;
}

// Onay Bekleyen Hikayeleri Çek
$pending_stories = $pdo->query("
    SELECT s.id, s.title, u.username 
    FROM stories s 
    JOIN users u ON s.author_id = u.id 
    WHERE s.status = 'pending'
")->fetchAll();

$approval_setting = isApprovalRequired($pdo);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ghos.tr Komuta Merkezi</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .admin-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .action-btn { padding: 5px 10px; border-radius: 3px; text-decoration: none; color: #fff; }
        .btn-approve { background: rgba(0, 255, 204, 0.2); border: 1px solid var(--neon-green); }
        .btn-delete { background: rgba(255, 51, 102, 0.2); border: 1px solid var(--neon-red); }
    </style>
</head>
<body>
    <div style="max-width: 1200px; margin: 0 auto;">
        <h1 style="color: var(--neon-red);">Ghos.tr Yönetim Terminali</h1>
        
        <div class="admin-grid">
            <!-- Sol Panel: Hikaye Onayları -->
            <div class="glass-panel">
                <h3>Bekleyen Anomaliler (Hikayeler)</h3>
                <?php if (count($pending_stories) > 0): ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach($pending_stories as $story): ?>
                            <li style="border-bottom: 1px solid var(--glass-border); padding: 10px 0;">
                                <strong><?= htmlspecialchars($story['title']) ?></strong> 
                                <span style="font-size: 0.8em; color: #888;">Yazar: <?= htmlspecialchars($story['username']) ?></span>
                                <div style="margin-top: 10px;">
                                    <a href="?action=approve&story_id=<?= $story['id'] ?>" class="action-btn btn-approve">Onayla</a>
                                    <a href="?action=delete&story_id=<?= $story['id'] ?>" class="action-btn btn-delete">İmha Et</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Onay bekleyen kayıt bulunmuyor.</p>
                <?php endif; ?>
            </div>

            <!-- Sağ Panel: Sistem Ayarları (Sadece Admin) -->
            <?php if ($isAdmin): ?>
            <div class="glass-panel">
                <h3 style="color: var(--neon-blue);">Sistem Protokolleri</h3>
                <form method="POST">
                    <label>Hikaye Onay Sistemi:</label><br>
                    <select name="require_approval" style="background: #000; color: #fff; padding: 5px; margin-top: 10px;">
                        <option value="1" <?= $approval_setting ? 'selected' : '' ?>>Açık (Moderasyon Gerekli)</option>
                        <option value="0" <?= !$approval_setting ? 'selected' : '' ?>>Kapalı (Direkt Yayınla)</option>
                    </select>
                    <button type="submit" name="update_setting" style="display:block; margin-top:15px; padding: 8px; border: 1px solid var(--neon-blue); background: transparent; color: var(--neon-blue);">Güncelle</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
