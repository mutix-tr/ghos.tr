<?php
// core/config.php
session_start();

$host = 'localhost';
$db   = 'ghos_tr';
$user = 'root'; 
$pass = 'sifren';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<div style='color:#ff3366; text-align:center; padding:20px;'>Veritabanı bağlantı hatası. Kurulumu yaptığınızdan emin olun.</div>");
}

// Genel Fonksiyon: Hikaye onayı gerekli mi?
function isApprovalRequired($pdo) {
    $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'require_story_approval'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? (bool)$result['setting_value'] : true;
}
?>
