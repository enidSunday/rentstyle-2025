<?php
require_once 'config.php';

// 1. 檢查是否登入
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. 查詢該會員的所有訂單 (最新的在最上面)
$sql = "SELECT * FROM rental_orders WHERE user_id = :uid ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['uid' => $user_id]);
$orders = $stmt->fetchAll();

include 'includes/header.php';
?>

<style>
    body {
        background-color: #fcfcfc;
    }

    .member-container {
        max-width: 900px;
        margin: 60px auto;
        padding: 0 20px;
        font-family: 'Lato', sans-serif;
    }

    .page-title {
        font-family: 'Playfair Display', serif;
        font-size: 32px;
        margin-bottom: 40px;
        text-align: center;
        letter-spacing: 1px;
    }

    /* 訂單卡片 */
    .order-card {
        background: #fff;
        border: 1px solid #eee;
        margin-bottom: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.02);
        transition: 0.3s;
    }

    .order-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.05);
    }

    /* 訂單頭部資訊 */
    .order-header {
        background: #fafafa;
        padding: 20px 25px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .order-info span {
        display: inline-block;
        margin-right: 20px;
        font-size: 13px;
        color: #555;
    }
    
    .order-info strong {
        color: #000;
        font-weight: 700;
    }

    /* 狀態標籤 */
    .status-badge {
        font-size: 12px;
        padding: 5px 12px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
    }

    .status-pending { background: #fff3cd; color: #856404; }
    .status-paid { background: #d4edda; color: #155724; }
    .status-shipped { background: #cce5ff; color: #004085; }
    .status-cancelled { background: #f8d7da; color: #721c24; }

    /* 訂單內容清單 */
    .order-body {
        padding: 25px;
    }

    .order-item {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
        border-bottom: 1px solid #f5f5f5;
        padding-bottom: 20px;
    }

    .order-item:last-child {
        margin-bottom: 0;
        border-bottom: none;
        padding-bottom: 0;
    }

    .order-item img {
        width: 70px;
        height: 90px;
        object-fit: cover;
        background-color: #eee;
    }

    .item-details h4 {
        margin: 0 0 5px;
        font-size: 15px;
        font-weight: 400;
        font-family: 'Playfair Display', serif;
    }

    .item-meta {
        font-size: 13px;
        color: #888;
        margin-bottom: 3px;
    }

    .order-footer {
        padding: 15px 25px;
        border-top: 1px solid #eee;
        text-align: right;
        background: #fff;
    }

    .total-amount {
        font-size: 18px;
        font-weight: 700;
        font-family: 'Playfair Display', serif;
    }

    /* 空狀態 */
    .empty-state {
        text-align: center;
        padding: 60px 0;
        color: #999;
    }

    @media (max-width: 600px) {
        .order-header { flex-direction: column; align-items: flex-start; }
        .order-item { align-items: flex-start; }
    }
</style>

<div class="member-container">
    <h1 class="page-title">My Orders</h1>

    <?php if (count($orders) > 0): ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <div class="order-info">
                        <span>訂單編號: <strong><?php echo htmlspecialchars($order['order_number']); ?></strong></span>
                        <span>下單日期: <?php echo date('Y/m/d', strtotime($order['created_at'])); ?></span>
                    </div>
                    
                    <?php
                        // 狀態顯示邏輯
                        $status = $order['status'];
                        $status_text = '處理中';
                        $status_class = 'status-pending';

                        if ($status == 'paid') { $status_text = '已付款'; $status_class = 'status-paid'; }
                        elseif ($status == 'shipped') { $status_text = '已出貨'; $status_class = 'status-shipped'; }
                        elseif ($status == 'completed') { $status_text = '已完成'; $status_class = 'status-paid'; }
                        elseif ($status == 'cancelled') { $status_text = '已取消'; $status_class = 'status-cancelled'; }
                    ?>
                    <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                </div>

                <div class="order-body">
                    <?php
                        // 查詢該訂單的商品與圖片
                        $sql_items = "SELECT roi.*, pi.image_url 
                                      FROM rental_order_items roi
                                      LEFT JOIN product_images pi ON roi.product_id = pi.product_id AND pi.is_primary = 1
                                      WHERE roi.order_id = :oid";
                        $stmt_items = $pdo->prepare($sql_items);
                        $stmt_items->execute(['oid' => $order['id']]);
                        $items = $stmt_items->fetchAll();
                    ?>

                    <?php foreach ($items as $item): ?>
                        <div class="order-item">
                            <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'assets/images/default.jpg'); ?>" alt="Product">
                            <div class="item-details">
                                <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                <p class="item-meta">尺寸: <?php echo htmlspecialchars($item['size_label']); ?></p>
                                <p class="item-meta">
                                    租期: <?php echo date('m/d', strtotime($item['rental_start_date'])); ?> - 
                                          <?php echo date('m/d', strtotime($item['rental_end_date'])); ?> 
                                    (共 <?php echo $item['rental_days']; ?> 天)
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-footer">
                    總金額 (Total): <span class="total-amount">TWD <?php echo number_format($order['total_amount']); ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <p>您目前還沒有任何訂單。</p>
            <a href="products.php" class="btn-black" style="display:inline-block; padding: 10px 20px; border:1px solid #000; text-decoration:none; margin-top:15px; color:#fff; background:#000;">去逛逛</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>