<?php
session_start();

if (!empty($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Demo credentials for presentation
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['user'] = $username;
        header('Location: index.php');
        exit;
    }

    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Beyond The Map</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e9f2f5 0%, #f8f9fa 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            padding: 32px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.1);
        }
        .logo-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .logo-wrap img {
            max-width: 180px;
            height: auto;
        }
        .brand-title {
            text-align: center;
            font-weight: 700;
            color: #2c5e6d;
            margin-bottom: 6px;
        }
        .brand-subtitle {
            text-align: center;
            color: #6c757d;
            margin-bottom: 24px;
            font-size: 14px;
        }
        .btn-primary {
            background: #367588;
            border-color: #367588;
        }
        .btn-primary:hover {
            background: #2c5e6d;
            border-color: #2c5e6d;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-wrap">
            <img src="assets/LOGO.png" alt="Beyond The Map">
        </div>
        <div class="brand-title">Beyond The Map</div>
        <div class="brand-subtitle">Travel & Tours System</div>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <div class="text-center text-muted mt-3" style="font-size: 12px;">
        
        </div>
    </div>
</body>
</html>
