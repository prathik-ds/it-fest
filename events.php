<?php 
include 'includes/header.php'; 

// CSRF check not implemented for brevity, but recommended
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

$stmt = $pdo->query("SELECT * FROM events ORDER BY category, name");
$events = $stmt->fetchAll();
?>

<div style="margin-bottom: 50px; text-align: center;">
    <h1 class="orbitron neon-text-blue" style="font-size: 3rem;">COMPETITIONS</h1>
    <p style="color: #666; margin-top: 10px;">Select your track and join the future of innovation.</p>
</div>

<?php if($success): ?>
    <div style="width: 100%; max-width: 1200px; padding: 15px; border: 1px solid var(--neon-blue); background: rgba(0, 243, 255, 0.1); color: #fff; margin-bottom: 40px; border-radius: 8px;">
        ✅ Registration Successful! View your events in the <a href="dashboard.php" class="neon-text-purple">Dashboard</a>.
    </div>
<?php endif; ?>

<?php if($error): ?>
    <div style="width: 100%; max-width: 1200px; padding: 15px; border: 1px solid var(--neon-pink); background: rgba(255, 0, 85, 0.1); color: var(--neon-pink); margin-bottom: 40px; border-radius: 8px;">
        ⚠️ Error: <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px;">
    <?php foreach($events as $event): ?>
        <div class="glass neon-border-blue" style="padding: 30px; position: relative; overflow: hidden; display: flex; flex-direction: column;">
            <div style="position: absolute; top: 15px; right: 15px; background: <?= $event['category'] == 'IT' ? 'var(--neon-blue)' : 'var(--neon-purple)' ?>; color: #000; padding: 4px 12px; font-size: 0.7rem; font-weight: 800; border-radius: 20px;">
                <?= $event['category'] ?>
            </div>
            
            <h3 class="orbitron" style="font-size: 1.4rem; margin-bottom: 15px; color: #fff;"><?= $event['name'] ?></h3>
            <p style="color: #888; font-size: 0.9rem; line-height: 1.6; margin-bottom: 20px; flex-grow: 1;">
                <?= $event['description'] ?>
            </p>
            
            <div style="margin-bottom: 25px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; font-size: 0.85rem; color: #aaa;">
                    <span style="color: var(--neon-blue)">🕒</span> <?= date('d M Y', strtotime($event['date'])) ?> | <?= date('H:i', strtotime($event['time'])) ?>
                </div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; font-size: 0.85rem; color: #aaa;">
                    <span style="color: var(--neon-blue)">📍</span> <?= $event['venue'] ?>
                </div>
                <div style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; color: #aaa;">
                    <span style="color: var(--neon-blue)">👥</span> Slots: <?= $event['current_participants'] ?> / <?= $event['max_participants'] ?>
                </div>
            </div>

            <?php if ($user): ?>
                <form action="register_event.php" method="POST">
                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                    <button type="submit" class="btn-neon" style="width: 100%;" <?= ($event['current_participants'] >= $event['max_participants']) ? 'disabled' : '' ?>>
                        <?= ($event['current_participants'] >= $event['max_participants']) ? 'FULLHOUSE' : 'REGISTER NOW' ?>
                    </button>
                </form>
            <?php else: ?>
                <a href="login.php" class="btn-neon" style="width: 100%; text-decoration: none;">LOG IN TO REGISTER</a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>
