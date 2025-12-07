<?php
require_once 'config.php';
include 'includes/header.php';

$cart_items = $_SESSION['cart'] ?? [];
$total = 0;
?>

<style>
    /* 容器設定 */
    .cart-container {
        max-width: 1000px;
        margin: 60px auto;
        padding: 0 20px;
        font-family: 'Lato', sans-serif;
    }

    .page-title {
        font-family: 'Playfair Display', serif;
        font-size: 36px;
        margin-bottom: 40px;
        text-align: center;
        letter-spacing: 1px;
    }

    /* 表格樣式 */
    .table-responsive {
        width: 100%;
        overflow-x: auto; /* 手機版可橫向捲動 */
    }

    .cart-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 50px;
        min-width: 600px; /* 防止表格在手機縮太小 */
    }

    .cart-table th {
        text-align: left;
        padding: 15px 0;
        border-bottom: 2px solid #000;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 1px;
        color: #666;
        font-weight: 700;
    }

    .cart-table td {
        padding: 25px 0;
        border-bottom: 1px solid #eee;
        vertical-align: top;
    }

    /* 商品資訊欄 */
    .cart-item-info {
        display: flex;
        gap: 20px;
    }

    .cart-item-info img {
        width: 90px;
        height: 120px;
        object-fit: cover;
        background-color: #f5f5f5;
    }

    .item-details h4 {
        font-size: 16px;
        margin: 5px 0;
        font-weight: 400;
        font-family: 'Playfair Display', serif;
    }

    .designer-name {
        font-size: 12px;
        text-transform: uppercase;
        color: #888;
        letter-spacing: 1px;
        margin-bottom: 5px;
    }

    .item-meta {
        font-size: 13px;
        color: #555;
        margin-top: 5px;
    }

    /* 移除按鈕 */
    .btn-remove {
        color: #999;
        font-size: 12px;
        text-decoration: underline;
        transition: 0.3s;
    }
    .btn-remove:hover {
        color: #cc0000;
    }

    /* 日期與價格文字 */
    .date-text {
        font-size: 14px;
        line-height: 1.6;
        color: #333;
    }
    
    .price-text {
        font-size: 16px;
        font-weight: 700;
    }

    /* 總計與結帳區塊 */
    .cart-summary-box {
        background-color: #f9f9f9;
        padding: 40px;
        max-width: 400px;
        margin-left: auto; /* 靠右對齊 */
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        font-size: 14px;
        color: #555;
    }

    .summary-total {
        display: flex;
        justify-content: space-between;
        font-size: 20px;
        font-weight: 700;
        color: #000;
        border-top: 1px solid #ddd;
        padding-top: 20px;
        margin-top: 20px;
        margin-bottom: 30px;
        font-family: 'Playfair Display', serif;
    }

    .btn-checkout {
        display: block;
        width: 100%;
        background-color: #000;
        color: #fff;
        text-align: center;
        padding: 15px;
        text-transform: uppercase;
        letter-spacing: 2px;
        font-weight: 700;
        text-decoration: none;
        transition: 0.3s;
        border: 1px solid #000;
    }

    .btn-checkout:hover {
        background-color: #fff;
        color: #000;
    }

    /* 空購物車樣式 */
    .empty-cart {
        text-align: center;
        padding: 80px 0;
    }
    .empty-cart p {
        font-size: 16px;
        color: #666;
        margin-bottom: 20px;
    }
</style>

<div class="cart-container">
    <h1 class="page-title">Shopping Bag</h1>

    <?php if ($cart_items): ?>
        
        <div class="table-responsive">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th width="50%">商品資訊 (Product)</th>
                        <th width="25%">租借日期 (Dates)</th>
                        <th width="15%">價格 (Price)</th>
                        <th width="10%"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $idx => $item): $total += $item['price']; ?>
                    <tr>
                        <td>
                            <div class="cart-item-info">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="product">
                                <div class="item-details">
                                    <p class="designer-name"><?php echo htmlspecialchars($item['designer']); ?></p>
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p class="item-meta">Size: <?php echo htmlspecialchars($item['size']); ?></p>
                                    <p class="item-meta">天數: <?php echo $item['days']; ?> 天</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="date-text">
                                <?php echo str_replace('-', '/', $item['start_date']); ?> <br>
                                <span style="font-size:12px; color:#999; display:block; margin: 2px 0;">至</span>
                                <?php echo str_replace('-', '/', $item['end_date']); ?>
                            </div>
                        </td>
                        <td>
                            <span class="price-text">TWD <?php echo number_format($item['price']); ?></span>
                        </td>
                        <td style="text-align: right;">
                            <a href="cart_remove.php?index=<?php echo $idx; ?>" class="btn-remove">移除</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="cart-summary-box">
            <div class="summary-row">
                <span>小計 (Subtotal)</span>
                <span>TWD <?php echo number_format($total); ?></span>
            </div>
            <div class="summary-row">
                <span>運費 (Shipping)</span>
                <span>免運費</span>
            </div>
            
            <div class="summary-total">
                <span>Total</span>
                <span>TWD <?php echo number_format($total); ?></span>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="checkout.php" class="btn-checkout">前往結帳</a>
            <?php else: ?>
                <a href="login.php?redirect=checkout.php" class="btn-checkout">登入後結帳</a>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <div class="empty-cart">
            <p>您的購物車目前是空的。</p>
            <a href="products.php" class="btn-checkout" style="display:inline-block; width:auto; padding: 12px 40px;">瀏覽商品</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>