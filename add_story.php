<?php
// add_story.php
require 'core/config.php';

// Kullanıcı giriş yapmamışsa engelle
if (!isset($_SESSION['user_id'])) {
    die("<div class='glass-panel'>Hikaye yazmak için giriş yapmalısınız.</div>");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $author_id = $_SESSION['user_id'];
    
    // Onay ayarını kontrol et
    $status = isApprovalRequired($pdo) ? 'pending' : 'approved';
    
    if (!empty($title) && !empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO stories (author_id, title, content, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$author_id, $title, $content, $status]);
        
        $msg = $status == 'pending' ? "Hikayeniz onaya gönderildi." : "Hikayeniz yayınlandı!";
        echo "<p style='color: var(--neon-green);'>$msg</p>";
    }
}
?>
<!-- HTML Formu -->
<div class="glass-panel" style="max-width: 800px; margin: 20px auto;">
    <h2>Yeni Hikaye İletimi</h2>
    <form method="POST">
        <input type="text" name="title" placeholder="Hikaye Başlığı..." style="width: 100%; margin-bottom: 15px;" required>
        <textarea name="content" rows="10" placeholder="Karanlıkta ne gördün?..." style="width: 100%; margin-bottom: 15px;" required></textarea>
        <button type="submit" style="border: 1px solid var(--neon-blue); color: var(--neon-blue); background: transparent; padding: 10px; border-radius: 5px;">AĞA YÜKLE</button>
    </form>
</div>
