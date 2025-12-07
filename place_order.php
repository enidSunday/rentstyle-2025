<?php
require_once 'config.php';
if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) { header("Location: index.php"); exit; }

try {
    $pdo->beginTransaction();
    
    // 1. Address
    $stmt = $pdo->prepare("INSERT INTO user_addresses (user_id, recipient_name, phone, city, district, address_line, is_default) VALUES (?,?,?,?,?,?,1)");
    $stmt->execute([$_SESSION['user_id'], $_POST['name'], $_POST['phone'], $_POST['city'], $_POST['dist'], $_POST['addr']]);
    $addr_id = $pdo->lastInsertId();

    // 2. Order
    $total = array_sum(array_column($_SESSION['cart'], 'price'));
    $ord_no = 'ORD-' . date('YmdHi') . rand(100,999);
    $stmt = $pdo->prepare("INSERT INTO rental_orders (user_id, order_number, total_amount, shipping_address_id, note) VALUES (?,?,?,?,?)");
    $stmt->execute([$_SESSION['user_id'], $ord_no, $total, $addr_id, $_POST['note']]);
    $oid = $pdo->lastInsertId();

    // 3. Items
    $stmt = $pdo->prepare("INSERT INTO rental_order_items (order_id, product_id, product_sku_id, product_name, size_label, rental_price, rental_start_date, rental_end_date, rental_days) VALUES (?,?,?,?,?,?,?,?,?)");
    foreach ($_SESSION['cart'] as $c) {
        $stmt->execute([$oid, $c['product_id'], $c['sku_id'], $c['name'], $c['size'], $c['price'], $c['start_date'], $c['end_date'], $c['days']]);
    }

    $pdo->commit();
    unset($_SESSION['cart']);
    header("Location: order_success.php?order=" . $ord_no);

} catch (Exception $e) {
    $pdo->rollBack();
    die("訂單建立失敗: " . $e->getMessage());
}
?>