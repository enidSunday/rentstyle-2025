<?php
// config.php - Zeabur 專用版

// 開啟錯誤顯示 (除錯用)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- 從環境變數讀取 Zeabur 的資料庫設定 ---
// 這些變數名稱 (DB_HOST 等) 等一下我們會在 Zeabur 後台設定
$host     = getenv('DB_HOST')     ?: '127.0.0.1';
$dbname   = getenv('DB_NAME')     ?: 'rentstyle';
$username = getenv('DB_USER')     ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$port     = getenv('DB_PORT')     ?: '3306';

try {
    // 建立連線 (注意這裡多加了 port)
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