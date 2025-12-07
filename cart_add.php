<?php
require_once 'config.php';

// 檢查是否為 POST 請求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. 接收表單資料
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $sku_id     = isset($_POST['sku_id']) ? (int)$_POST['sku_id'] : 0;
    $start_date = $_POST['start_date'] ?? '';
    $end_date   = $_POST['end_date'] ?? '';

    // 基本驗證
    if ($product_id === 0 || $sku_id === 0 || empty($start_date) || empty($end_date)) {
        die("資料不完整，請返回上一頁重新選擇。");
    }

    // 2. 從資料庫撈取商品詳細資料
    $sql = "SELECT p.name, p.base_rental_price, d.name AS designer_name, s.size_label, pi.image_url
            FROM products p
            JOIN designers d ON p.designer_id = d.id
            JOIN product_skus s ON s.id = :sku_id
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            WHERE p.id = :pid";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['pid' => $product_id, 'sku_id' => $sku_id]);
    $item = $stmt->fetch();

    if (!$item) {
        die("找不到商品資料。");
    }

    // --- 3. 關鍵修改：計算租期與價格 ---
    $start = new DateTime($start_date);
    $end   = new DateTime($end_date);
    $interval = $start->diff($end);
    $days = $interval->days + 1; // 含頭尾

    // 防呆
    if ($end < $start) $days = 4; // 日期選錯就當作 4 天
    if ($days < 1) $days = 4;

    // 計算價格：基本租期 4 天
    // 超過 4 天，每天加收 (基本租金 / 4)
    $base_price = (float)$item['base_rental_price'];
    
    if ($days <= 4) {
        $final_price = $base_price;
    } else {
        $daily_rate = $base_price / 4; 
        $extra_days = $days - 4;
        $final_price = $base_price + ($extra_days * $daily_rate);
    }

    // 4. 建立購物車項目
    $cart_item = [
        'product_id' => $product_id,
        'sku_id'     => $sku_id,
        'name'       => $item['name'],
        'designer'   => $item['designer_name'],
        'image'      => $item['image_url'] ?? 'assets/images/default.jpg',
        'size'       => $item['size_label'],
        'price'      => $final_price, // 這裡存的是計算後的總價
        'start_date' => $start_date,
        'end_date'   => $end_date,
        'days'       => $days
    ];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $_SESSION['cart'][] = $cart_item;

    header("Location: cart.php");
    exit;

} else {
    header("Location: index.php");
    exit;
}
?>