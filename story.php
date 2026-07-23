<?php
// story.php
require 'core/config.php';

if (!isset($_GET['id'])) {
    die("Sinyal kayboldu. (Hikaye bulunamadı)");
}

$story_id = (int)$_GET['id'];

// Hikaye detaylarını çek
$stmt = $pdo->prepare("
    SELECT s.*, u.username, u.role, u.is_verified 
    FROM stories s 
    JOIN users u ON s.author_id = u.id 
    WHERE s.id = ? AND s.status = 'approved'
");
$stmt->execute([$story_id]);
$story = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$story) {
    die("Bu veri dosyası silinmiş veya henüz onaylanmamış.");
}

// Yorum Gönderme İşlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $comment_content = trim($_POST['comment']);
    if (!empty($comment_content)) {
        $add_comment = $pdo->prepare("INSERT INTO comments (story_id, user_id, content) VALUES (?, ?, ?)");
        $add_comment->execute([$story_id, $_SESSION['user_id'], $comment_content]);
        // Aynı sayfaya yönlendir ki formu tekrar göndermesin
        header("Location: story.php?id=" . $story_id);
        exit;
    }
}

// Yorumları Çek
$comments_stmt = $pdo->prepare("
    SELECT c.content, c.created_at, u.username, u.role, u.is_verified 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.story_id = ? 
    ORDER BY c.created_at DESC
");
$comments_stmt->execute([$story_id]);
$comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);

function getRoleBadge($role, $is_verified) {
    if ($role === 'admin') return '<span class="badge admin" title="Sistem Yöneticisi">✓</span>';
    if ($role === 'mod') return '<span class="badge mod" title="Moderatör">✓</span>';
    if ($is_verified == 1) return '<span class="badge user" title="Onaylı Kullanıcı">✓</span>';
    return '';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($story['title']) ?> - Ghos.tr</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .comment-box { border-left: 2px solid var(--neon-blue); padding-left: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div style="max-width: 800px; margin: 0 auto; padding-top: 20px;">
        <a href="index.php" style="color: var(--neon-blue); text-decoration: none;">&lt; Ana Ağa Dön</a>
        
        <div class="glass-panel" style="margin-top: 20px;">
            <h1 style="color: #fff; margin-top: 0;"><?= htmlspecialchars($story['title']) ?></h1>
            <div style="color: #888; font-size: 0.9em; margin-bottom: 30px; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px;">
                Aktaran: <strong style="color: #fff;"><?= htmlspecialchars($story['username']) ?></strong> <?= getRoleBadge($story['role'], $story['is_verified']) ?> 
                | Tarih: <?= date('d.m.Y H:i', strtotime($story['created_at'])) ?>
            </div>
            
            <div style="line-height: 1.8; color: #ddd; font-size: 1.1em; white-space: pre-wrap;"><?= htmlspecialchars($story['content']) ?></div>
        </div>

        <div class="glass-panel" style="margin-top: 40px;">
            <h3 style="color: var(--neon-green);">Bağlantı Yorumları</h3>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <form method="POST" style="margin-bottom: 30px;">
                    <textarea name="comment" rows="3" placeholder="Senin teorin ne?" style="width: 100%; margin-bottom: 10px;" required></textarea>
                    <button type="submit" style="background: transparent; border: 1px solid var(--neon-green); color: var(--neon-green); padding: 8px 15px; border-radius: 5px; cursor: pointer;">Yorumu İlet</button>
                </form>
            <?php else: ?>
                <p style="color: #ff3366; border: 1px dashed #ff3366; padding: 10px;">Yorum yapmak için <a href="login.php" style="color: var(--neon-blue);">sisteme bağlanmalısınız</a>.</p>
            <?php endif; ?>

            <div>
                <?php if(count($comments) > 0): ?>
                    <?php foreach($comments as $comment): ?>
                        <div class="comment-box">
                            <div style="font-size: 0.9em; margin-bottom: 5px;">
                                <strong style="color: #fff;"><?= htmlspecialchars($comment['username']) ?></strong> <?= getRoleBadge($comment['role'], $comment['is_verified']) ?> 
                                <span style="color: #666; font-size: 0.8em; margin-left: 10px;"><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></span>
                            </div>
                            <div style="color: #ccc;">
                                <?= nl2br(htmlspecialchars($comment['content'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #888;">Henüz sinyal yakalanamadı. İlk yorumu sen yap.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
