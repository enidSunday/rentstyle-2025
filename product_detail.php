<?php
require_once 'config.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id === 0) { header("Location: index.php"); exit; }

$sql = "SELECT p.*, d.name AS designer_name 
        FROM products p 
        JOIN designers d ON p.designer_id = d.id 
        WHERE p.id = :id AND p.status = 'active'";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $product_id]);
$product = $stmt->fetch();

if (!$product) die("找不到該商品");

$stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = :id ORDER BY sort_order ASC");
$stmt->execute(['id' => $product_id]);
$images = $stmt->fetchAll();
if (empty($images)) $images[] = ['image_url' => 'assets/images/default.jpg'];

$stmt = $pdo->prepare("SELECT * FROM product_skus WHERE product_id = :id AND status = 'active'");
$stmt->execute(['id' => $product_id]);
$skus = $stmt->fetchAll();

include 'includes/header.php';
?>

<style>
    /* 保持原本的樣式 */
    .product-detail-container { max-width: 1100px; margin: 60px auto; padding: 0 20px; display: flex; flex-wrap: wrap; gap: 60px; align-items: flex-start; }
    .product-gallery { flex: 1 1 500px; width: 100%; }
    .gallery-main img { width: 100%; height: auto; display: block; object-fit: cover; }
    .gallery-thumbs { display: flex; gap: 10px; margin-top: 15px; }
    .gallery-thumbs img { width: 70px; height: 90px; object-fit: cover; cursor: pointer; opacity: 0.6; border: 1px solid #ddd; }
    .gallery-thumbs img:hover { opacity: 1; border-color: #000; }
    .product-info-col { flex: 1 1 400px; padding-top: 10px; }
    .designer-link { font-size: 14px; font-weight: 700; color: #666; text-transform: uppercase; margin-bottom: 10px; display: block; }
    .product-title { font-family: 'Playfair Display', serif; font-size: 34px; margin-bottom: 15px; line-height: 1.2; font-weight: 400; }
    .price-tag { font-size: 22px; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee; display: flex; align-items: baseline; gap: 10px; }
    .product-desc { font-size: 15px; line-height: 1.8; color: #555; margin-bottom: 30px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; }
    .custom-select, .custom-input { width: 100%; padding: 12px 15px; font-size: 14px; border: 1px solid #ccc; background-color: #fff; appearance: none; }
    .date-row { display: flex; gap: 15px; }
    .date-col { flex: 1; }
    .btn-black-large { display: block; width: 100%; background-color: #000; color: #fff; text-align: center; padding: 15px; font-size: 14px; text-transform: uppercase; border: 1px solid #000; cursor: pointer; margin-top: 30px; }
    .btn-black-large:hover { background-color: #fff; color: #000; }
    #displayPrice { font-weight: 700; transition: color 0.3s; }
</style>

<div class="product-detail-container">
    <div class="product-gallery">
        <div class="gallery-main">
            <img id="mainImage" src="<?php echo htmlspecialchars($images[0]['image_url']); ?>">
        </div>
        <?php if (count($images) > 1): ?>
        <div class="gallery-thumbs">
            <?php foreach ($images as $img): ?>
                <img src="<?php echo htmlspecialchars($img['image_url']); ?>" onclick="document.getElementById('mainImage').src=this.src">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="product-info-col">
        <a href="designer_profile.php?id=<?php echo $product['designer_id']; ?>" class="designer-link" style="text-decoration:none; cursor:pointer;">
    <?php echo htmlspecialchars($product['designer_name']); ?></a>
        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
        
        <div class="price-tag">
            <span id="displayPrice">TWD <?php echo number_format($product['base_rental_price']); ?></span>
            <span id="rentalDaysText" style="font-size: 14px; color: #666;">/ 4天租期</span>
        </div>
        
        <p style="font-size: 12px; color: #999; margin-top: -5px; margin-bottom: 25px;">
            * 基本租期為 4 天，超過將依天數加收費用。
        </p>

        <div class="product-desc"><?php echo nl2br(htmlspecialchars($product['description'])); ?></div>

        <form action="cart_add.php" method="POST">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            
            <div class="form-group">
                <label>尺寸 (Size)</label>
                <div style="position:relative;">
                    <select name="sku_id" class="custom-select" required>
                        <option value="" disabled selected>請選擇尺寸</option>
                        <?php foreach ($skus as $sku): ?>
                            <option value="<?php echo $sku['id']; ?>"><?php echo htmlspecialchars($sku['size_label']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span style="position:absolute; right:15px; top:12px; pointer-events:none; font-size:12px;">▼</span>
                </div>
            </div>

            <div class="form-group">
                <label>租借日期 (Rental Period)</label>
                <div class="date-row">
                    <div class="date-col">
                        <label style="font-size:10px;color:#888;">開始</label>
                        <input type="date" id="startDate" name="start_date" class="custom-input" required oninput="calculatePrice()" onchange="calculatePrice()">
                    </div>
                    <div class="date-col">
                        <label style="font-size:10px;color:#888;">結束</label>
                        <input type="date" id="endDate" name="end_date" class="custom-input" required oninput="calculatePrice()" onchange="calculatePrice()">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-black-large">加入購物車</button>
        </form>
    </div>
</div>

<script>
    // 確保這裡抓到的是數字
    var basePrice = <?php echo (float)$product['base_rental_price']; ?>;

    function calculatePrice() {
        var startInput = document.getElementById('startDate').value;
        var endInput = document.getElementById('endDate').value;
        
        // 只有當兩個日期都選好才計算
        if (startInput && endInput) {
            var start = new Date(startInput);
            var end = new Date(endInput);
            
            // 計算毫秒差 -> 轉天數
            var timeDiff = end.getTime() - start.getTime();
            var dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1; 

            // 除錯用：您可以把這行打開來看視窗
            // alert("選擇天數：" + dayDiff + " 天");

            var finalPrice = basePrice;
            var text = '/ 4天租期';
            var color = '#000';

            // 邏輯：超過 4 天才加錢
            if (dayDiff > 4) {
                var dailyRate = basePrice / 4;
                var extraDays = dayDiff - 4;
                finalPrice = basePrice + (extraDays * dailyRate);
                
                text = '/ 共 ' + dayDiff + ' 天';
                color = '#cc0000'; 
            } else if (dayDiff < 1) {
                text = '/ 日期無效';
                finalPrice = basePrice; // 保持原價或顯示錯誤
            } else {
                // 1~4 天的情況
                text = '/ 共 ' + dayDiff + ' 天 (未滿4天以基本價計)';
            }

            // 更新畫面
            var priceElement = document.getElementById('displayPrice');
            var textElement = document.getElementById('rentalDaysText');
            
            if(priceElement) {
                priceElement.innerText = 'TWD ' + Math.round(finalPrice).toLocaleString();
                priceElement.style.color = color;
            }
            if(textElement) {
                textElement.innerText = text;
            }
        }
    }
</script>

<?php include 'includes/footer.php'; ?>