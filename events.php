<?php
include 'includes/header.php';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

$stmt = $pdo->query("SELECT * FROM events ORDER BY category, name");
$events = $stmt->fetchAll();

// Group by category for filtering
$categories = array_unique(array_column($events, 'category'));
?>

<!-- Page Header -->
<div style="margin-bottom: 50px; text-align: center; padding-top: 20px;">
    <div
        style="display: inline-flex; align-items: center; gap: 8px; background: rgba(0, 212, 255, 0.06); border: 1px solid rgba(0, 212, 255, 0.15); padding: 6px 16px; border-radius: 50px; margin-bottom: 16px;">
        <i class="fa-solid fa-trophy" style="font-size: 0.7rem; color: var(--accent-1);"></i>
        <span
            style="font-size: 0.7rem; font-weight: 700; color: var(--accent-1); text-transform: uppercase; letter-spacing: 2px;">Live
            Events</span>
    </div>
    <h1
        style="font-family: 'Space Grotesk', sans-serif; font-size: 3rem; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 10px;">
        <span
            style="background: var(--grad-primary); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">Competitions</span>
    </h1>
    <p style="color: var(--text-secondary); max-width: 400px; margin: 0 auto; font-size: 0.9rem;">Choose your challenge
        and register to compete at NEXUS 2026</p>
</div>

<?php if ($success): ?>
    <div
        style="max-width: 1200px; padding: 14px 20px; border-radius: 14px; border: 1px solid rgba(16, 185, 129, 0.2); background: rgba(16, 185, 129, 0.06); color: var(--text-primary); margin-bottom: 30px; font-size: 0.88rem; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-check" style="color: var(--success);"></i>
        Registration Successful! View your events in the <a href="dashboard.php"
            style="color: var(--accent-2); font-weight: 700; text-decoration: none;">Dashboard →</a>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div
        style="max-width: 1200px; padding: 14px 20px; border-radius: 14px; border: 1px solid rgba(244, 63, 94, 0.2); background: rgba(244, 63, 94, 0.06); color: var(--danger); margin-bottom: 30px; font-size: 0.88rem; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-exclamation"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<!-- Events Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 24px;">
    <?php foreach ($events as $index => $event): ?>
        <div class="glass"
            style="padding: 0; display: flex; flex-direction: column; animation: fadeInUp <?= 0.3 + ($index * 0.08) ?>s ease forwards; opacity: 0;">
            <!-- Card Header with Gradient -->
            <div style="padding: 24px 24px 18px; position: relative; overflow: hidden;">
                <!-- Category Badge -->
                <div
                    style="display: inline-flex; align-items: center; gap: 6px; background: <?= $event['category'] == 'IT' ? 'rgba(0, 212, 255, 0.1)' : 'rgba(124, 58, 237, 0.1)' ?>; border: 1px solid <?= $event['category'] == 'IT' ? 'rgba(0, 212, 255, 0.2)' : 'rgba(124, 58, 237, 0.2)' ?>; padding: 5px 14px; border-radius: 50px; margin-bottom: 16px;">
                    <span
                        style="width: 6px; height: 6px; border-radius: 50%; background: <?= $event['category'] == 'IT' ? 'var(--accent-1)' : 'var(--accent-2)' ?>;"></span>
                    <span
                        style="font-size: 0.65rem; font-weight: 800; color: <?= $event['category'] == 'IT' ? 'var(--accent-1)' : 'var(--accent-2)' ?>; text-transform: uppercase; letter-spacing: 1.5px;"><?= $event['category'] ?></span>
                </div>

                <h3
                    style="font-family: 'Space Grotesk', sans-serif; font-size: 1.3rem; margin-bottom: 10px; color: var(--text-primary); font-weight: 700; letter-spacing: -0.01em;">
                    <?= $event['name'] ?></h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; line-height: 1.7; min-height: 48px;">
                    <?= $event['description'] ?>
                </p>
            </div>

            <!-- Event Details -->
            <div style="padding: 0 24px 18px; display: flex; flex-direction: column; gap: 10px; flex-grow: 1;">
                <div
                    style="display: flex; align-items: center; gap: 10px; font-size: 0.82rem; color: var(--text-secondary);">
                    <i class="fa-regular fa-calendar" style="color: var(--accent-1); width: 16px; text-align: center;"></i>
                    <?= date('d M Y', strtotime($event['date'])) ?> · <?= date('h:i A', strtotime($event['time'])) ?>
                </div>
                <div
                    style="display: flex; align-items: center; gap: 10px; font-size: 0.82rem; color: var(--text-secondary);">
                    <i class="fa-solid fa-location-dot"
                        style="color: var(--accent-2); width: 16px; text-align: center;"></i>
                    <?= $event['venue'] ?>
                </div>

                <!-- Capacity Bar -->
                <div style="margin-top: 8px;">
                    <div
                        style="display: flex; justify-content: space-between; font-size: 0.72rem; color: var(--text-muted); margin-bottom: 6px;">
                        <span>Capacity</span>
                        <span style="font-weight: 700; color: var(--text-secondary);"><?= $event['current_participants'] ?>
                            / <?= $event['max_participants'] ?></span>
                    </div>
                    <div
                        style="width: 100%; height: 4px; background: rgba(255,255,255,0.05); border-radius: 4px; overflow: hidden;">
                        <?php $fill = $event['max_participants'] > 0 ? ($event['current_participants'] / $event['max_participants']) * 100 : 0; ?>
                        <div
                            style="width: <?= $fill ?>%; height: 100%; background: <?= $fill > 80 ? 'var(--danger)' : 'var(--grad-primary)' ?>; border-radius: 4px; transition: width 0.5s;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div style="padding: 0 24px 24px; margin-top: auto;">
                <?php if ($user): ?>
                    <form action="register_event.php" method="POST">
                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                        <button type="submit" class="btn-neon" style="width: 100%; padding: 12px;"
                            <?= ($event['current_participants'] >= $event['max_participants']) ? 'disabled' : '' ?>>
                            <?php if ($event['current_participants'] >= $event['max_participants']): ?>
                                <i class="fa-solid fa-ban"></i> Full House
                            <?php else: ?>
                                <i class="fa-solid fa-bolt"></i> Register Now
                            <?php endif; ?>
                        </button>
                    </form>
                <?php else: ?>
                    <a href="login.php" class="btn-neon" style="width: 100%; text-decoration: none; padding: 12px;">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In to Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>