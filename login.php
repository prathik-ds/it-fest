<?php 
include 'includes/header.php'; 

if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        header('Location: dashboard.php');
    } else {
        $error = "Invalid Email or Password.";
    }
}
?>

<div style="max-width: 400px; margin: 100px auto;">
    <div class="glass neon-border-blue" style="padding: 40px;">
        <h2 class="orbitron neon-text-blue" style="margin-bottom: 30px; text-align: center;">LOG IN</h2>
        
        <?php if($error): ?>
            <div style="padding: 10px; border-bottom: 2px solid var(--neon-pink); background: rgba(255, 0, 85, 0.1); color: var(--neon-pink); margin-bottom: 20px;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="john@example.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="********" required>
            </div>
            <button type="submit" class="btn-neon" style="width: 100%; margin-top: 20px;">ACCESS PORTAL</button>
        </form>
        
        <p style="text-align: center; margin-top: 30px; font-size: 0.9rem; color: #777;">
            New to SYNERGY? <a href="register.php" class="neon-text-purple" style="text-decoration: none;">Join Free</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
