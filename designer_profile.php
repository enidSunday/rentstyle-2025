<?php
require_once 'config.php';

// 1. 獲取設計師 ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. 查詢設計師資料
$stmt = $pdo->prepare("SELECT * FROM designers WHERE id = :id AND status = 'active'");
$stmt->execute(['id' => $id]);
$designer = $stmt->fetch();

if (!$designer) {
    die("找不到該設計師頁面");
}

// 3. 查詢該設計師的所有商品
$sql = "SELECT p.*, d.name AS designer_name, pi.image_url 
        FROM products p 
        JOIN designers d ON p.designer_id = d.id 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE p.designer_id = :did AND p.status = 'active'
        ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['did' => $id]);
$products = $stmt->fetchAll();

include 'includes/header.php';
?>

<style>
    .designer-hero {
        background-color: #f9f9f9;
        padding: 80px 20px;
        text-align: center;
        margin-bottom: 60px;
    }
    
    .designer-name-large {
        font-family: 'Playfair Display', serif;
        font-size: 40px;
        margin-bottom: 20px;
        letter-spacing: 1px;
    }

    .designer-desc {
        max-width: 600px;
        margin: 0 auto;
        font-size: 15px;
        color: #666;
        line-height: 1.8;
    }

    .designer-social {
        margin-top: 20px;
    }
    
    .designer-social a {
        margin: 0 10px;
        color: #000;
        font-size: 14px;
        text-decoration: underline;
    }
</style>

<div class="designer-hero">
    <h1 class="designer-name-large"><?php echo htmlspecialchars($designer['name']); ?></h1>
    <div class="designer-desc">
        <?php echo nl2br(htmlspecialchars($designer['description'])); ?>
    </div>
    
    <div class="designer-social">
        <?php if($designer['website_url']): ?>
            <a href="<?php echo htmlspecialchars($designer['website_url']); ?>" target="_blank">Official Website</a>
        <?php endif; ?>
        <?php if($designer['instagram_url']): ?>
            <a href="<?php echo htmlspecialchars($designer['instagram_url']); ?>" target="_blank">Instagram</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <h2 class="section-title">Collection</h2>
    
    <div class="product-grid">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                        <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'assets/images/default.jpg'); ?>" 
                             class="product-image">
                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-price">TWD <?php echo number_format($product['base_rental_price']); ?></p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center;">此設計師尚未上架商品。</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>