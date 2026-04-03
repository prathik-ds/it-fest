<?php
include 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $college = $_POST['college'] ?? '';
    $course = $_POST['course'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Name, Email, and Password are required.";
    } else {
        // Unique ID generation
        $user_id = 'SYN-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (user_id, name, email, phone, college, course, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $name, $email, $phone, $college, $course, $hashedPassword]);
            $success = "Registration successful! Your ID is <span class='neon-text-blue'>$user_id</span>. Please <a href='login.php' class='neon-text-purple'>Login</a> here.";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Email or User ID already exists.";
            } else {
                $error = "Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>

<div style="max-width: 500px; margin: 50px auto;">
    <div class="glass neon-border-blue" style="padding: 40px;">
        <h2 class="orbitron neon-text-blue" style="margin-bottom: 30px; text-align: center;">REGISTRATION</h2>

        <?php if ($error): ?>
            <div
                style="padding: 15px; border-bottom: 2px solid var(--neon-pink); background: rgba(255, 0, 85, 0.1); color: var(--neon-pink); margin-bottom: 20px;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div
                style="padding: 15px; border-bottom: 2px solid var(--neon-blue); background: rgba(0, 243, 255, 0.1); color: #fff; margin-bottom: 20px;">
                <?= $success ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="John Doe" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="john@example.com" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" placeholder="9876543210" required>
            </div>
            <div class="form-group">
                <label>College Name</label>
                <input type="text" name="college" placeholder="ABC Institute" required>
            </div>
            <div class="form-group">
                <label>Course</label>
                <input type="text" name="course" placeholder="BCA / B.Com / MCA" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="********" required>
            </div>
            <button type="submit" class="btn-neon" style="width: 100%; margin-top: 20px;">CREATE ACCOUNT</button>
        </form>

        <p style="text-align: center; margin-top: 30px; font-size: 0.9rem; color: #777;">
            Already have an account? <a href="login.php" class="neon-text-purple" style="text-decoration: none;">Log
                In</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>