<?php
require_once 'config.php';

// 檢查登入與購物車狀態
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php?redirect=checkout.php"); 
    exit; 
}
if (empty($_SESSION['cart'])) { 
    header("Location: index.php"); 
    exit; 
}

$cart = $_SESSION['cart'];
$total = array_sum(array_column($cart, 'price'));

include 'includes/header.php';
?>

<style>
    /* 整體佈局：左表單，右摘要 */
    .checkout-container {
        max-width: 1100px;
        margin: 60px auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 1.5fr 1fr; /* 左寬右窄 */
        gap: 60px;
        align-items: start;
        font-family: 'Lato', sans-serif;
    }

    /* 左側：表單區 */
    .checkout-section h2 {
        font-family: 'Playfair Display', serif;
        font-size: 24px;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
        color: #000;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 8px;
        letter-spacing: 1px;
        color: #333;
    }

    .form-control {
        width: 100%;
        padding: 15px;
        border: 1px solid #ddd;
        font-family: 'Lato', sans-serif;
        font-size: 14px;
        box-sizing: border-box;
        background-color: #fff;
        border-radius: 0; /* 直角風格 */
        transition: 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: #000;
        background-color: #fff;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    /* 付款方式專屬樣式 */
    .payment-options {
        margin-top: 10px;
    }

    .payment-label {
        display: flex;
        align-items: center;
        padding: 15px;
        border: 1px solid #ddd;
        margin-bottom: 10px;
        cursor: pointer;
        transition: 0.2s;
    }

    .payment-label:hover {
        border-color: #000;
        background: #fafafa;
    }

    .payment-label input[type="radio"] {
        margin-right: 15px;
        accent-color: #000; /* 修改 Radio Button 顏色 */
        transform: scale(1.2);
    }

    .payment-label span {
        font-size: 14px;
        font-weight: 700;
        color: #333;
    }

    /* 右側：訂單摘要區 (懸浮效果) */
    .order-summary-box {
        background: #f9f9f9; /* 淺灰背景 */
        padding: 30px;
        position: sticky; /* 讓它跟著捲動 */
        top: 100px;
    }

    .order-summary-box h3 {
        font-family: 'Playfair Display', serif;
        font-size: 20px;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid #ddd;
        color: #000;
    }

    /* 商品列表小圖 */
    .checkout-product {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        border-bottom: 1px solid #eee;
        padding-bottom: 20px;
    }

    .checkout-product img {
        width: 60px;
        height: 80px;
        object-fit: cover;
        background-color: #eee;
    }

    .checkout-product div {
        flex: 1;
    }

    .checkout-product b {
        font-size: 14px;
        display: block;
        margin-bottom: 5px;
        color: #000;
        font-weight: 400; /* 標題不加粗，維持優雅 */
        font-family: 'Playfair Display', serif;
    }

    .checkout-product p {
        font-size: 13px;
        color: #666;
        margin: 0;
    }

    /* 總金額列 */
    .summary-item.total {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px solid #000;
        font-size: 18px;
        font-weight: 700;
        color: #000;
        font-family: 'Playfair Display', serif;
    }

    /* 確認按鈕 */
    .btn-block {
        display: block;
        width: 100%;
        background: #000;
        color: #fff;
        padding: 18px;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 2px;
        font-weight: 700;
        border: 1px solid #000;
        cursor: pointer;
        transition: 0.3s;
        margin-top: 30px;
    }

    .btn-block:hover {
        background: #fff;
        color: #000;
    }

    /* 手機版適應 */
    @media (max-width: 768px) {
        .checkout-container {
            grid-template-columns: 1fr; /* 變單欄 */
            gap: 40px;
        }
        .order-summary-box {
            position: static; /* 手機版不懸浮 */
        }
    }
</style>

<div class="checkout-container">
    
    <div class="checkout-section">
        <h2>收件資訊 (Shipping Details)</h2>
        
        <form action="place_order.php" method="POST">
            <div class="form-group">
                <label>收件人姓名 (Recipient Name)</label>
                <input type="text" name="recipient_name" value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>" class="form-control" required>
            </div>

            <div class="form-group">
                <label>聯絡電話 (Phone Number)</label>
                <input type="text" name="phone" class="form-control" required>
            </div>

            <div style="display: flex; gap: 20px;">
                <div class="form-group" style="flex: 1;">
                    <label>縣市 (City)</label>
                    <input type="text" name="city" class="form-control" placeholder="例如：台北市" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>區域 (District)</label>
                    <input type="text" name="district" class="form-control" placeholder="例如：信義區" required>
                </div>
            </div>

            <div class="form-group">
                <label>詳細地址 (Address)</label>
                <input type="text" name="address_line" class="form-control" placeholder="街道、樓層..." required>
            </div>

            <div class="form-group">
                <label>訂單備註 (Order Notes)</label>
                <textarea name="note" class="form-control" placeholder="有什麼特殊需求嗎？"></textarea>
            </div>

            <h2 style="margin-top: 50px;">付款方式 (Payment Method)</h2>
            <div class="payment-options">
                <label class="payment-label">
                    <input type="radio" name="payment_method" value="credit_card" checked>
                    <span>信用卡線上刷卡 (Credit Card)</span>
                    <i class="far fa-credit-card" style="margin-left: auto; color: #666;"></i>
                </label>
                
                <label class="payment-label">
                    <input type="radio" name="payment_method" value="atm">
                    <span>ATM 轉帳 / 銀行匯款</span>
                    <i class="fas fa-university" style="margin-left: auto; color: #666;"></i>
                </label>
            </div>

            <button class="btn-block">確認訂單並付款</button>
        </form>
    </div>

    <div class="order-summary-box">
        <h3>訂單摘要 (Summary)</h3>
        
        <?php foreach($cart as $item): ?>
            <div class="checkout-product">
                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="product">
                <div>
                    <b><?php echo htmlspecialchars($item['name']); ?></b>
                    <p>Size: <?php echo htmlspecialchars($item['size']); ?></p>
                    <p>TWD <?php echo number_format($item['price']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div class="summary-item total">
            <span>Total</span>
            <span>TWD <?php echo number_format($total); ?></span>
        </div>
        
        <div style="font-size: 12px; color: #666; margin-top: 15px; line-height: 1.5;">
            * 按下確認訂單後，系統將會建立訂單並安排出貨。<br>
            * 運費: 免運費
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>