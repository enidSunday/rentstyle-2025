<?php
include 'includes/header.php';
?>
<div class="container" style="text-align: center; padding: 100px 0;">
    <h1 style="font-size:50px; color:green;"><i class="fas fa-check-circle"></i></h1>
    <h1>訂購成功！</h1>
    <p>您的訂單編號：<strong><?php echo htmlspecialchars($_GET['order']); ?></strong></p>
    <a href="index.php" class="btn-black" style="display:inline-block; margin-top:20px;">回首頁</a>
</div>
<?php include 'includes/footer.php'; ?>