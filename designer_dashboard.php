<?php
require_once 'config.php';

// 1. 權限檢查
if (!isset($_SESSION['user_id'])) {
    // 沒登入，踢回登入頁
    header("Location: login.php");
    exit;
}

if ($_SESSION['user_role'] !== 'designer') {
    // 有登入但不是設計師，顯示錯誤
    die("錯誤：您沒有權限訪問此頁面。(您的身分是: " . htmlspecialchars($_SESSION['user_role']) . ")");
}

$user_id = $_SESSION['user_id'];

// 2. 找出這個 User 對應的 Designer ID
$stmt = $pdo->prepare("SELECT * FROM designers WHERE user_id = :uid");
$stmt->execute(['uid' => $user_id]);
$designer_info = $stmt->fetch();

if (!$designer_info) {
    die("錯誤：您的帳號 (ID: $user_id) 尚未綁定設計師資料表。請確認資料庫 designers 表的 user_id 欄位是否正確。");
}

$designer_id = $designer_info['id'];

// 3. 查詢租賃紀錄 (修正了這裡的 SQL)
// 修正點：移除了錯誤的 p.image_url，改成 pi.image_url
$sql = "SELECT roi.*, p.name AS product_name, pi.image_url, ro.order_number, u.name AS customer_name
        FROM rental_order_items roi
        JOIN products p ON roi.product_id = p.id
        JOIN rental_orders ro ON roi.order_id = ro.id
        JOIN users u ON ro.user_id = u.id
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE p.designer_id = :did
        ORDER BY roi.rental_start_date DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['did' => $designer_id]);
    $rentals = $stmt->fetchAll();
} catch (PDOException $e) {
    die("資料庫查詢失敗: " . $e->getMessage());
}

// 簡單統計
$total_rentals = count($rentals);
$active_rentals = 0;
foreach($rentals as $r) {
    if(strtotime($r['rental_end_date']) >= time()) $active_rentals++;
}

include 'includes/header.php';
?>

<style>
    .dashboard-container {
        max-width: 1200px;
        margin: 60px auto;
        padding: 0 20px;
    }
    .dash-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px;
        border-bottom: 1px solid #eee;
        padding-bottom: 20px;
    }
    .stats-row {
        display: flex;
        gap: 20px;
        margin-bottom: 40px;
    }
    .stat-card {
        flex: 1;
        background: #f9f9f9;
        padding: 25px;
        text-align: center;
        border: 1px solid #eee;
    }
    .stat-number { font-size: 32px; font-weight: 700; color: #000; font-family: 'Playfair Display', serif; }
    .stat-label { font-size: 13px; text-transform: uppercase; color: #666; letter-spacing: 1px; }

    /* 表格樣式 */
    .dashboard-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .dashboard-table th { text-align: left; padding: 15px; background: #000; color: #fff; text-transform: uppercase; font-size: 12px; }
    .dashboard-table td { padding: 15px; border-bottom: 1px solid #eee; }
    .status-badge { padding: 4px 10px; border-radius: 4px; font-size: 11px; text-transform: uppercase; font-weight: 700; }
    .status-active { background: #d4edda; color: #155724; }
    .status-past { background: #f8f9fa; color: #6c757d; }
    
    .prod-thumb { width: 40px; height: 50px; object-fit: cover; vertical-align: middle; margin-right: 10px; }
</style>

<div class="dashboard-container">
    <div class="dash-header">
        <div>
            <h1 style="font-family: 'Playfair Display', serif;">Designer Dashboard</h1>
            <p style="color: #666;">Welcome back, <?php echo htmlspecialchars($designer_info['name']); ?></p>
        </div>
        <div>
            <a href="designer_profile.php?id=<?php echo $designer_id; ?>" class="btn-black" target="_blank">查看我的品牌頁面</a>
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_rentals; ?></div>
            <div class="stat-label">總租賃次數 (Total Orders)</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $active_rentals; ?></div>
            <div class="stat-label">進行中租約 (Active)</div>
        </div>
    </div>

    <h3 style="margin-bottom: 20px; font-family: 'Playfair Display', serif;">訂單列表 (Rental History)</h3>
    
    <div style="overflow-x: auto;">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>商品 (Product)</th>
                    <th>租客 (Customer)</th>
                    <th>租借期間 (Date)</th>
                    <th>尺寸 (Size)</th>
                    <th>租金 (Price)</th>
                    <th>狀態 (Status)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($rentals) > 0): ?>
                    <?php foreach ($rentals as $rental): ?>
                        <tr>
                            <td>
                                <img src="<?php echo htmlspecialchars($rental['image_url'] ?? 'assets/images/default.jpg'); ?>" class="prod-thumb">
                                <?php echo htmlspecialchars($rental['product_name']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($rental['customer_name']); ?></td>
                            <td>
                                <?php echo date('Y/m/d', strtotime($rental['rental_start_date'])); ?> - 
                                <?php echo date('Y/m/d', strtotime($rental['rental_end_date'])); ?>
                            </td>
                            <td><?php echo htmlspecialchars($rental['size_label']); ?></td>
                            <td>TWD <?php echo number_format($rental['rental_price']); ?></td>
                            <td>
                                <?php 
                                    $today = date('Y-m-d');
                                    if ($today >= $rental['rental_start_date'] && $today <= $rental['rental_end_date']) {
                                        echo '<span class="status-badge status-active">租借中</span>';
                                    } elseif ($today > $rental['rental_end_date']) {
                                        echo '<span class="status-badge status-past">已結束</span>';
                                    } else {
                                        echo '<span class="status-badge" style="background:#fff3cd; color:#856404;">預約中</span>';
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #999; padding: 30px;">
                            目前尚無任何租借紀錄。
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>