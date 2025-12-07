<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUMIERE | 設計師服飾租賃</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<header>
    <a href="index.php" class="brand-logo">LUMIERE</a>
    <nav>
        <ul class="nav-links">
            <li><a href="products.php">最新上架</a></li>
            <li><a href="products.php">全部商品</a></li>
            <li><a href="index.php#how-it-works">如何租賃</a></li>
        </ul>
    </nav>
    <div class="nav-icons">
        <a href="cart.php">
            <i class="fas fa-shopping-bag"></i> 
            <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                <span style="font-size:12px;">(<?php echo count($_SESSION['cart']); ?>)</span>
            <?php endif; ?>
        </a>

        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="member_orders.php" title="我的訂單"><i class="far fa-user"></i></a>
            <a href="logout.php" class="btn-black">登出</a>
        <?php else: ?>
            <a href="login.php" class="btn-black">登入</a>
        <?php endif; ?>
    </div>
</header>