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
        $user_id = 'NXS-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (user_id, name, email, phone, college, course, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $name, $email, $phone, $college, $course, $hashedPassword]);
            $success = "Registration successful! Your ID is <span style='color: var(--accent-1); font-weight: 700; font-family: JetBrains Mono, monospace;'>$user_id</span>. <a href='login.php' style='color: var(--accent-2); font-weight: 700; text-decoration: none;'>Sign In →</a>";
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

<div style="min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 40px 0;">
    <div style="max-width: 480px; width: 100%;">
        <!-- Register Card -->
        <div class="glass" style="padding: 44px 36px; border-color: rgba(0, 212, 255, 0.12);">
            <!-- Header -->
            <div style="text-align: center; margin-bottom: 36px;">
                <div style="width: 56px; height: 56px; background: var(--grad-cool); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 18px; box-shadow: 0 8px 30px rgba(0, 212, 255, 0.2);">
                    <i class="fa-solid fa-user-plus" style="color: white;"></i>
                </div>
                <h2 style="font-family: 'Space Grotesk', sans-serif; font-size: 1.6rem; font-weight: 700; margin-bottom: 8px; background: var(--grad-cool); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">Join FusionVerse</h2>
                <p style="font-size: 0.85rem; color: var(--text-muted);">Create your participant account</p>
            </div>

            <?php if ($error): ?>
                <div style="padding: 12px 16px; border-radius: 12px; background: rgba(244, 63, 94, 0.08); border: 1px solid rgba(244, 63, 94, 0.2); color: var(--danger); margin-bottom: 24px; font-size: 0.85rem; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div style="padding: 16px; border-radius: 12px; background: rgba(16, 185, 129, 0.06); border: 1px solid rgba(16, 185, 129, 0.2); color: var(--text-primary); margin-bottom: 24px; font-size: 0.88rem; line-height: 1.6;">
                    <i class="fa-solid fa-circle-check" style="color: var(--success); margin-right: 6px;"></i>
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group" style="grid-column: span 2;">
                        <label><i class="fa-solid fa-user" style="margin-right: 6px; color: var(--accent-1);"></i>Full Name</label>
                        <input type="text" name="name" placeholder="John Doe" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-envelope" style="margin-right: 6px; color: var(--accent-2);"></i>Email</label>
                        <input type="email" name="email" placeholder="you@email.com" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-phone" style="margin-right: 6px; color: var(--accent-3);"></i>Phone</label>
                        <input type="text" name="phone" placeholder="9876543210" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-building-columns" style="margin-right: 6px; color: var(--accent-5);"></i>College</label>
                        <input type="text" name="college" placeholder="Your College" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fa-solid fa-graduation-cap" style="margin-right: 6px; color: var(--accent-4);"></i>Course</label>
                        <input type="text" name="course" placeholder="BCA / MCA" required>
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label><i class="fa-solid fa-lock" style="margin-right: 6px; color: var(--accent-1);"></i>Password</label>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit" class="btn-neon" style="width: 100%; margin-top: 10px; padding: 14px; background: rgba(0, 212, 255, 0.06); border-color: var(--accent-1); color: var(--accent-1);">
                    <i class="fa-solid fa-rocket"></i> Create Account
                </button>
            </form>

            <div style="text-align: center; margin-top: 28px;">
                <p style="font-size: 0.85rem; color: var(--text-muted);">
                    Already have an account? 
                    <a href="login.php" style="color: var(--accent-2); text-decoration: none; font-weight: 700;">Sign In →</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>