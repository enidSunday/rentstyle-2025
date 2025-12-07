<?php
require_once 'config.php';
$error = '';

// 使用您提供的 PHP 邏輯
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']); 
    $email = trim($_POST['email']);
    $password = $_POST['password']; 
    $confirm = $_POST['confirm_password'];
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->rowCount() > 0) $error = "Email 已存在";
    elseif ($password !== $confirm) $error = "密碼不一致";
    else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        // 您原本的寫法
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $hash]);
        header("Location: login.php?registered=1"); exit;
    }
}
include 'includes/header.php';
?>

<style>
    body { background-color: #fcfcfc; }
    .auth-container {
        max-width: 450px;
        margin: 80px auto;
        padding: 50px 40px;
        background: #fff;
        border: 1px solid #eee;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
    }
    .auth-title {
        font-family: 'Playfair Display', serif;
        font-size: 32px;
        margin-bottom: 30px;
        color: #000;
        letter-spacing: 1px;
    }
    .form-group { margin-bottom: 25px; text-align: left; }
    .form-group label {
        display: block; font-size: 12px; font-weight: 700;
        text-transform: uppercase; margin-bottom: 8px; letter-spacing: 1px; color: #333;
    }
    .form-control {
        width: 100%; padding: 15px; border: 1px solid #ddd;
        font-family: 'Lato', sans-serif; font-size: 14px;
        box-sizing: border-box; background-color: #fafafa; transition: 0.3s;
    }
    .form-control:focus { outline: none; border-color: #000; background-color: #fff; }
    
    /* 將原本的 btn-block 樣式美化為黑底白字 */
    .btn-block {
        display: block; width: 100%; background: #000; color: #fff;
        padding: 15px; font-size: 14px; text-transform: uppercase;
        letter-spacing: 2px; font-weight: 700; border: 1px solid #000;
        cursor: pointer; transition: 0.3s; margin-top: 20px;
    }
    .btn-block:hover { background: #fff; color: #000; }
    
    .error-message {
        background-color: #fff5f5; color: #c53030; padding: 12px;
        font-size: 13px; margin-bottom: 20px; text-align: left;
        border-left: 3px solid #c53030;
    }
    .login-link {
        display: block; margin-top: 30px; padding-top: 20px;
        border-top: 1px solid #eee; font-size: 13px; color: #666;
        text-decoration: none;
    }
    .login-link:hover { color: #000; }
</style>

<div class="auth-container">
    <h2 class="auth-title">建立帳戶</h2>
    
    <?php if($error) echo "<div class='error-message'>$error</div>"; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>姓名</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label>密碼</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label>確認密碼</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button class="btn-block">註冊</button>
    </form>
    
    <a href="login.php" class="login-link">已有帳號？登入</a>
</div>

<?php include 'includes/footer.php'; ?>