<?php
require_once 'config.php';

// --- 1. 新增：撈取所有分類 (給左邊選單用) ---
try {
    // 依照 sort_order 排序
    $stmt_cat = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC");
    $categories = $stmt_cat->fetchAll();
} catch (PDOException $e) {
    $categories = []; // 若資料庫沒分類表，預防報錯
}

// --- 2. 修改：撈取商品 (加入篩選邏輯) ---
$current_cat_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// 基礎 SQL
$sql = "SELECT p.*, d.name AS designer_name, pi.image_url 
        FROM products p 
        JOIN designers d ON p.designer_id = d.id 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE p.status = 'active'";

// 如果有選分類，就多加一個 AND 條件
$params = [];
if ($current_cat_id > 0) {
    $sql .= " AND p.category_id = :cid";
    $params['cid'] = $current_cat_id;
}

$sql .= " ORDER BY p.created_at DESC";

// 執行查詢
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

include 'includes/header.php';
?>

<style>
    /* 容器：改成彈性佈局 (Flexbox) */
    .shop-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
        display: flex;
        gap: 40px; /* 側邊欄與商品區的間距 */
        align-items: flex-start;
    }

    /* 左側邊欄 */
    .shop-sidebar {
        width: 250px;       /* 固定寬度 */
        flex-shrink: 0;     /* 防止被壓縮 */
        position: sticky;   /* 捲動時固定 */
        top: 100px;
    }

    .sidebar-title {
        font-family: 'Playfair Display', serif;
        font-size: 18px;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #ddd;
        font-weight: 700;
        color: #000;
    }

    .cat-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .cat-list li {
        margin-bottom: 8px;
    }

    .cat-list a {
        display: block;
        padding: 8px 10px;
        color: #666;
        text-decoration: none;
        font-size: 14px;
        transition: 0.3s;
        border-radius: 4px;
    }

    .cat-list a:hover {
        background-color: #f5f5f5;
        color: #000;
    }

    /* 當前選中的分類樣式 */
    .cat-list a.active {
        background-color: #000;
        color: #fff;
    }

    /* 右側主要內容區 */
    .shop-main {
        flex: 1; /* 佔滿剩下空間 */
    }

    .page-header {
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: baseline;
    }

    .page-header h1 {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        margin: 0;
    }

    /* 確保原本的 Grid 在這裡能正常運作 */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }

    /* 手機版響應式調整 */
    @media (max-width: 768px) {
        .shop-container {
            flex-direction: column; /* 改成上下排列 */
            gap: 20px;
        }
        .shop-sidebar {
            width: 100%;
            position: static;
        }
        .cat-list {
            display: flex; /* 變成橫向捲動選單 */
            overflow-x: auto;
            gap: 10px;
            padding-bottom: 5px;
        }
        .cat-list li {
            margin-bottom: 0;
            white-space: nowrap;
        }
        .product-grid {
            grid-template-columns: repeat(2, 1fr); /* 手機兩欄 */
        }
    }
</style>

<div class="shop-container">
    
    <aside class="shop-sidebar">
        <div class="sidebar-title">Categories</div>
        <ul class="cat-list">
            <li>
                <a href="products.php" class="<?php echo ($current_cat_id == 0) ? 'active' : ''; ?>">
                    全部商品 (All)
                </a>
            </li>
            
            <?php foreach ($categories as $cat): ?>
                <li>
                    <a href="products.php?category=<?php echo $cat['id']; ?>" 
                       class="<?php echo ($current_cat_id == $cat['id']) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <main class="shop-main">
        <div class="page-header">
            <h1 class="section-title">
                <?php 
                    if ($current_cat_id == 0) echo "所有系列 (All Collection)";
                    else {
                        // 顯示當前選中的分類名稱
                        foreach($categories as $c) {
                            if ($c['id'] == $current_cat_id) {
                                echo htmlspecialchars($c['name']);
                                break;
                            }
                        }
                    }
                ?>
            </h1>
            <span style="font-size:13px; color:#888;">共 <?php echo count($products); ?> 件商品</span>
        </div>

        <div class="product-grid">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
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
                <div style="grid-column: 1/-1; padding:50px; text-align:center; color:#999; border:1px dashed #ddd;">
                    <p>此分類目前尚無商品上架。</p>
                    <a href="products.php" style="text-decoration:underline; color:#000;">查看全部商品</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

</div>

<?php include 'includes/footer.php'; ?>