<?php
require_once 'config.php';
// 取最新的 3 件 active 商品
$sql = "SELECT p.*, d.name AS designer_name, pi.image_url 
        FROM products p 
        JOIN designers d ON p.designer_id = d.id 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE p.status = 'active' ORDER BY p.created_at DESC LIMIT 3";
try {
    $stmt = $pdo->query($sql);
    $featured_products = $stmt->fetchAll();
} catch (PDOException $e) { $featured_products = []; }
include 'includes/header.php'; 
?>
<section class="hero">
    <div class="hero-content">
        <h2>Own The Moment, Rent The Look</h2>
        <h1>擁抱奢華，<br>無需擁有。</h1>
        <p>探索全球頂級設計師禮服，4天租期只需 TWD 2,500 起。</p>
        <div class="btn-group">
            <a href="products.php" class="btn-outline" style="background: #fff; color: #000;">瀏覽全系列</a>
            <a href="#how-it-works" class="btn-outline">了解流程</a>
        </div>
    </div>
</section>

<section class="container">
    <h2 class="section-title">本週熱門精選</h2>
    <div class="product-grid">
        <?php if (count($featured_products) > 0): ?>
            <?php foreach ($featured_products as $product): ?>
                <div class="product-card">
                    <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                        <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'assets/images/default.jpg'); ?>" class="product-image">
                        <div class="product-info">
                            <p class="product-brand"><?php echo htmlspecialchars($product['designer_name']); ?></p>
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-price">TWD <?php echo number_format($product['base_rental_price']); ?></p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center;width:100%;">暫無商品，請先在資料庫新增資料。</p>
        <?php endif; ?>
    </div>
</section>

<section class="steps-section" id="how-it-works">
    <div class="container">
        <div class="steps-grid">
            <div class="step-item"><i class="far fa-calendar-check fa-2x"></i><h3>1. 線上預約</h3></div>
            <div class="step-item"><i class="fas fa-box-open fa-2x"></i><h3>2. 收到美衣</h3></div>
            <div class="step-item"><i class="fas fa-glass-cheers fa-2x"></i><h3>3. 盡情閃耀</h3></div>
            <div class="step-item"><i class="fas fa-shipping-fast fa-2x"></i><h3>4. 輕鬆歸還</h3></div>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>