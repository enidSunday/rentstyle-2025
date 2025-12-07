<?php
// config.php - Zeabur 專用版
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 讀取環境變數 (如果讀不到，會顯示錯誤)
$host     = getenv('DB_HOST');
$dbname   = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$port     = getenv('DB_PORT');

// 如果沒讀到變數，直接報錯，方便除錯
if (!$host) die("錯誤：找不到 DB_HOST 環境變數，請確認 Zeabur 設定。");

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("資料庫連線失敗: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
