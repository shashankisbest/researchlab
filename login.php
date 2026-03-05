<?php
require_once 'config/db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$user]);
    $dbUser = $stmt->fetch();

    if ($dbUser && password_verify($pass, $dbUser['password'])) {
        $_SESSION['user_id'] = $dbUser['id'];
        $_SESSION['username'] = $dbUser['username'];
        $_SESSION['role'] = $dbUser['role'];
        header("Location: dashboard.php");
    } else {
        $error = "Invalid credentials protocol.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/style.css">
    <title>ARED Login</title>
</head>
<body style="display: flex; align-items: center; justify-content: center; height: 100vh;">
    <div class="stat-card" style="width: 350px; border: 1px solid var(--lime);">
        <h2 style="color: var(--lime); text-align: center;">ARED ACCESS</h2>
        <?php if($error): ?> <p style="color: red;"><?= $error ?></p> <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required 
                   style="width: 100%; margin-bottom: 1rem; padding: 10px; background: #000; color: #fff; border: 1px solid #333;">
            <input type="password" name="password" placeholder="Password" required 
                   style="width: 100%; margin-bottom: 1rem; padding: 10px; background: #000; color: #fff; border: 1px solid #333;">
            <button type="submit" class="btn" style="width: 100%;">AUTHENTICATE</button>
        </form>
    </div>
</body>
</html>