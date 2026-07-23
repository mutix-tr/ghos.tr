<?php
// add_story.php
require 'core/config.php';

if (!isset($_SESSION['user_id'])) die("Giriş yapmalısınız.");

// Kategorileri Çek
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category_id = (int)$_POST['category_id'];
    $author_id = $_SESSION['user_id'];
    $status = 'approved'; // Veya config'den çek
    
    // Kapak Görseli Yükleme İşlemi
    $cover_path = null;
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
        $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array(strtolower($ext), $allowed)) {
            $new_name = uniqid('cover_') . '.' . $ext;
            move_uploaded_file($_FILES['cover']['tmp_name'], "uploads/" . $new_name);
            $cover_path = $new_name;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO stories (author_id, category_id, title, content, cover_image, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$author_id, $category_id, $title, $content, $cover_path, $status]);
    
    echo "<p style='color: var(--neon-green); text-align:center;'>Veri başarıyla ağa aktarıldı.</p>";
}
?>
<!-- HTML Kısmı -->
<div class="glass-panel" style="max-width: 800px; margin: 50px auto;">
    <h2 style="color: var(--neon-blue);">Sistem Kaydı Oluştur</h2>
    <form method="POST" enctype="multipart/form-data">
        <select name="category_id" style="width: 100%; margin-bottom: 15px; padding: 10px; background: #000; color: #fff; border: 1px solid var(--glass-border);">
            <?php foreach($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
        
        <input type="text" name="title" placeholder="Rapor Başlığı..." style="width: 100%; margin-bottom: 15px;" required>
        
        <label style="color: #aaa;">Kapak Görseli (Opsiyonel - PNG, JPG, WEBP)</label>
        <input type="file" name="cover" accept="image/*" style="width: 100%; margin-bottom: 15px;">
        
        <!-- İstersen buradaki textarea yerine TinyMCE editör scripti bağlayabilirsin -->
        <textarea name="content" rows="15" placeholder="Gördüklerini detaylandır..." style="width: 100%; margin-bottom: 15px;" required></textarea>
        
        <button type="submit" style="width: 100%; background: transparent; border: 1px solid var(--neon-green); color: var(--neon-green); padding: 12px; border-radius: 5px; cursor: pointer; font-weight: bold;">VERİYİ İŞLE</button>
    </form>
</div>
