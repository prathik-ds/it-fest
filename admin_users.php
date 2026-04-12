<?php 
include 'includes/header.php'; 

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_role') {
        $u_id = $_POST['user_id'];
        $new_role = $_POST['role'];
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        $stmt->execute([$new_role, $u_id]);
        $msg = "USER ROLE UPDATED SUCCESSFULLY.";
    }
}

$all_users = $pdo->query("SELECT * FROM users ORDER BY role DESC, created_at DESC")->fetchAll();
?>

<div style="padding: 40px;">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>User Management</h1>
            <p>Control system access levels and assign roles to participants.</p>
        </div>
        <div class="header-actions">
            <span class="status-tag" style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 8px 16px; font-size: 0.8rem; border-radius: 8px;">
                <i class="fa-solid fa-users-gear"></i> GLOBAL ACCESS CONTROL
            </span>
        </div>
    </div>

    <?php if($msg): ?>
        <div style="margin-bottom: 30px; padding: 15px; background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); color: var(--success); border-radius: 12px; font-size: 0.9rem;">
            <i class="fa-solid fa-circle-check"></i> <?= $msg ?>
        </div>
    <?php endif; ?>

    <div class="glass-panel-dash" style="padding: 30px;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <th style="padding: 16px; text-align: left; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">User</th>
                        <th style="padding: 16px; text-align: left; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Contact</th>
                        <th style="padding: 16px; text-align: center; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Role</th>
                        <th style="padding: 16px; text-align: right; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($all_users as $u): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 16px;">
                            <div style="font-weight: 600;"><?= htmlspecialchars($u['name']) ?></div>
                            <div style="font-size: 0.7rem; color: var(--text-dim);"><?= $u['user_id'] ?></div>
                        </td>
                        <td style="padding: 16px;">
                            <div style="font-size: 0.85rem;"><?= htmlspecialchars($u['email']) ?></div>
                            <div style="font-size: 0.7rem; color: var(--text-dim);"><?= htmlspecialchars($u['phone']) ?></div>
                        </td>
                        <td style="padding: 16px; text-align: center;">
                            <span style="padding: 4px 10px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; background: rgba(99, 102, 241, 0.1); color: var(--primary); text-transform: uppercase;">
                                <?= $u['role'] ?>
                            </span>
                        </td>
                        <td style="padding: 16px; text-align: right;">
                            <form method="POST" style="display: flex; gap: 8px; justify-content: flex-end;">
                                <input type="hidden" name="action" value="update_role">
                                <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                <select name="role" onchange="this.form.submit()" style="padding: 6px; background: var(--bg-dark); border: 1px solid var(--border); color: white; border-radius: 6px; font-size: 0.75rem;">
                                    <option value="student" <?= $u['role'] == 'student' ? 'selected' : '' ?>>Student</option>
                                    <option value="coordinator" <?= $u['role'] == 'coordinator' ? 'selected' : '' ?>>Coord</option>
                                    <option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
