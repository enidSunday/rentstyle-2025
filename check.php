<?php
// check.php - 資料庫連線診斷工具

// 1. 讀取變數
$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$name = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');

echo "<h1>LUMIERE 網站連線診斷</h1>";
echo "<hr>";

// 2. 檢查變數內容 (用括號包起來，檢查有沒有多餘空白)
echo "<h3>1. 環境變數檢查：</h3>";
echo "<ul>";
echo "<li><strong>DB_HOST:</strong> [" . $host . "] " . (empty($host) ? "❌ (空的)" : "✅") . "</li>";
echo "<li><strong>DB_PORT:</strong> [" . $port . "] " . (empty($port) ? "❌ (空的)" : "✅") . "</li>";
echo "<li><strong>DB_NAME:</strong> [" . $name . "] " . (empty($name) ? "❌ (空的)" : "✅") . "</li>";
echo "<li><strong>DB_USER:</strong> [" . $user . "] " . (empty($user) ? "❌ (空的)" : "✅") . "</li>";
// 密碼不顯示明碼，只顯示長度
echo "<li><strong>DB_PASSWORD:</strong> [" . substr($pass, 0, 3) . "***] (長度: " . strlen($pass) . ") " . (empty($pass) ? "❌ (空的)" : "✅") . "</li>";
echo "</ul>";

// 3. 嘗試連線
echo "<h3>2. 連線測試結果：</h3>";
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
    echo "<div>嘗試連線字串: <code>$dsn</code></div><br>";
    
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2 style='color:green;'>🎉 連線成功！ (Success)</h2>";
    echo "資料庫功能正常。請檢查首頁程式碼是否正確。";

} catch (PDOException $e) {
    echo "<h2 style='color:red;'>💥 連線失敗 (Failed)</h2>";
    echo "<strong>錯誤訊息 (Error):</strong> " . $e->getMessage() . "<br>";
    echo "<strong>錯誤代碼 (Code):</strong> " . $e->getCode() . "<br>";
    
    // 智慧診斷建議
    echo "<br><strong>💡 診斷建議：</strong><br>";
    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "- 主機 ($host) 拒絕連線。請確認：<br>";
        echo "  1. <strong>PORT 對嗎？</strong> (內網通常是 3306，公網通常是 5 位數，如 2xxxx)<br>";
        echo "  2. <strong>變數有空白嗎？</strong> (看上面的檢查，括號內不能有空白)<br>";
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "- 帳號或密碼錯誤。請重新複製 mysql-swing 的密碼。";
    } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "- 找不到資料庫 `$name`。請確認 phpMyAdmin 裡面的資料庫名稱是否正確。";
    } else {
        echo "- 請截圖此畫面尋求協助。";
    }
}
?>
