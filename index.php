<?php
// index.php
require 'core/config.php';

// Sadece onaylanmış hikayeleri ve yazar bilgilerini çek
$stmt = $pdo->query("
    SELECT s.id, s.title, s.created_at, LEFT(s.content, 150) as excerpt, 
           u.username, u.role, u.is_verified 
    FROM stories s 
    JOIN users u ON s.author_id = u.id 
    WHERE s.status = 'approved' 
    ORDER BY s.created_at DESC
");
$stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tik oluşturma fonksiyonu
function getRoleBadge($role, $is_verified) {
    if ($role === 'admin') return '<span class="badge admin" title="Sistem Yöneticisi">✓</span>';
    if ($role === 'mod') return '<span class="badge mod" title="Moderatör">✓</span>';
    if ($is_verified == 1) return '<span class="badge user" title="Onaylı Kullanıcı">✓</span>';
    return ''; // Normal kayıtsız/onaysız kullanıcı
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ghos.tr - Korku ve Bilimkurgu Ağı</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .story-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 30px; }
        .story-card { transition: transform 0.3s ease; cursor: pointer; text-decoration: none; display: block; color: inherit; }
        .story-card:hover { transform: translateY(-5px); border-color: var(--neon-blue); box-shadow: 0 0 15px rgba(0, 229, 255, 0.2); }
        .nav-bar { display: flex; justify-content: space-between; align-items: center; padding-bottom: 20px; border-bottom: 1px solid var(--glass-border); }
    </style>
</head>
<body>
    <div style="max-width: 1200px; margin: 0 auto;">
        
        <div class="nav-bar">
            <h1 style="color: var(--neon-green); margin: 0; text-shadow: 0 0 10px var(--neon-green);">GHOS.TR</h1>
            <div>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span style="margin-right: 15px;">Ajan <?= htmlspecialchars($_SESSION['username']) ?></span>
                    <a href="add_story.php" style="color: var(--neon-blue); text-decoration: none; margin-right: 15px;">+ İleti Gönder</a>
                    <?php if($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'mod'): ?>
                        <a href="admin/index.php" style="color: var(--neon-red); text-decoration: none; margin-right: 15px;">[Terminal]</a>
                    <?php endif; ?>
                    <a href="logout.php" style="color: #aaa; text-decoration: none;">Çıkış</a>
                <?php else: ?>
                    <a href="login.php" style="color: var(--neon-blue); text-decoration: none; margin-right: 15px;">Bağlan</a>
                    <a href="register.php" style="color: var(--neon-green); text-decoration: none;">Ağa Katıl</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="story-grid">
            <?php foreach($stories as $story): ?>
                <a href="story.php?id=<?= $story['id'] ?>" class="glass-panel story-card">
                    <h3 style="margin-top:0; color: #fff;"><?= htmlspecialchars($story['title']) ?></h3>
                    <p style="color: #aaa; font-size: 0.9em;"><?= htmlspecialchars($story['excerpt']) ?>...</p>
                    <div style="margin-top: 15px; font-size: 0.85em; color: #888; display: flex; justify-content: space-between;">
                        <span>Yazar: <strong style="color:#ddd;"><?= htmlspecialchars($story['username']) ?></strong> <?= getRoleBadge($story['role'], $story['is_verified']) ?></span>
                        <span><?= date('d.m.Y', strtotime($story['created_at'])) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
