<?php
require_once 'config.php';

// 如果已經登入，直接導向首頁
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

// 接收 redirect 參數 (例如從購物車來的)
$redirect_to = $_GET['redirect'] ?? 'index.php';

// 處理登入表單
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $redirect_url = $_POST['redirect_url'];

    if (empty($email) || empty($password)) {
        $error = "請輸入 Email 和密碼。";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role']; // 重要：紀錄角色

            // --- 修改這裡：判斷角色導向 ---
            if ($user['role'] === 'designer') {
                header("Location: designer_dashboard.php");
            } elseif ($user['role'] === 'admin') {
                // 如果未來有管理員後台可以加在這裡
                header("Location: admin_dashboard.php"); 
            } else {
                header("Location: " . $redirect_url);
            }
            exit;
            // ---------------------------
        } else {
            $error = "帳號或密碼錯誤。";
        }
    }
}

include 'includes/header.php';
?>

<style>
    /* 登入頁面專屬樣式 */
    body {
        background-color: #fcfcfc; /* 極淡的灰色背景，突顯白色的登入框 */
    }

    .auth-container {
        max-width: 450px;
        margin: 80px auto;
        padding: 50px 40px;
        background: #fff;
        border: 1px solid #eee; /* 輕微邊框 */
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03); /* 更有質感的陰影 */
    }

    .auth-title {
        font-family: 'Playfair Display', serif;
        font-size: 32px;
        margin-bottom: 10px;
        color: #000;
        letter-spacing: 1px;
    }

    .auth-subtitle {
        font-size: 13px;
        color: #888;
        margin-bottom: 40px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .form-group {
        margin-bottom: 25px;
        text-align: left;
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
        box-sizing: border-box; /* 確保 padding 不會撐大寬度 */
        background-color: #fafafa;
        transition: 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: #000;
        background-color: #fff;
    }

    .btn-auth {
        display: block;
        width: 100%;
        background: #000;
        color: #fff;
        padding: 15px;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 2px;
        font-weight: 700;
        border: 1px solid #000;
        cursor: pointer;
        transition: 0.3s;
        margin-top: 20px;
    }

    .btn-auth:hover {
        background: #fff;
        color: #000;
    }

    .auth-footer {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
        font-size: 13px;
        color: #666;
    }

    .auth-footer a {
        color: #000;
        text-decoration: underline;
        font-weight: 700;
    }

    /* 錯誤訊息 */
    .alert-error {
        background-color: #fff5f5;
        color: #c53030;
        padding: 12px;
        font-size: 13px;
        margin-bottom: 20px;
        text-align: left;
        border-left: 3px solid #c53030;
    }

    .alert-success {
        background-color: #f0fff4;
        color: #2f855a;
        padding: 12px;
        font-size: 13px;
        margin-bottom: 20px;
        text-align: left;
        border-left: 3px solid #2f855a;
    }
</style>

<div class="auth-container">
    <h2 class="auth-title">Welcome Back</h2>
    <p class="auth-subtitle">Login to your account</p>

    <?php if(isset($_GET['registered'])): ?>
        <div class="alert-success">
            註冊成功！請使用您的帳號登入。
        </div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($redirect_to); ?>">

        <div class="form-group">
            <label>電子郵件 (Email)</label>
            <input type="email" name="email" class="form-control" placeholder="example@email.com" required>
        </div>

        <div class="form-group">
            <label>密碼 (Password)</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-auth">登入</button>
    </form>

    <div class="auth-footer">
        還沒有帳號？ <a href="register.php">建立新帳戶</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>