<?php
include 'includes/header.php';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

$stmt = $pdo->query("SELECT * FROM events ORDER BY category, name");
$events = $stmt->fetchAll();
?>

<style>
/* Override container width for events page to use full width */
.container { max-width: 100% !important; padding: 0 40px !important; }

/* Events grid */
.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 32px;
    margin-top: 40px;
    margin-bottom: 80px;
}

/* Event card */
.ev-card {
    background: rgba(15, 22, 41, 0.6);
    border: 1px solid rgba(100, 130, 200, 0.15);
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
    position: relative;
}
.ev-card:hover {
    transform: translateY(-8px);
    border-color: rgba(0, 212, 255, 0.35);
    box-shadow: 0 20px 50px rgba(0,0,0,0.35), 0 0 30px rgba(0,212,255,0.08);
}
.ev-card-img {
    height: 200px;
    position: relative;
    overflow: hidden;
    background: rgba(8, 12, 26, 0.8);
    flex-shrink: 0;
}
.ev-card-img img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.ev-card:hover .ev-card-img img { transform: scale(1.08); }
.ev-card-img-placeholder {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, rgba(124,58,237,0.15), rgba(0,212,255,0.08));
}
.ev-card-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to bottom, transparent 40%, rgba(8,12,26,0.85) 100%);
    pointer-events: none;
}
.ev-card-badge {
    position: absolute; top: 14px; left: 14px; z-index: 2;
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(8,12,26,0.65);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.12);
    padding: 4px 12px; border-radius: 50px;
}
.ev-card-body {
    padding: 22px 24px 16px;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.ev-card-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.3rem; font-weight: 700;
    color: #f0f4ff; letter-spacing: -0.01em;
    margin-bottom: 8px;
}
.ev-card-desc {
    color: #94a3c7; font-size: 0.84rem;
    line-height: 1.65; margin-bottom: 18px;
    flex: 1;
}
.ev-card-meta {
    display: flex; flex-direction: column; gap: 6px;
    margin-bottom: 16px;
}
.ev-card-meta-item {
    display: flex; align-items: center; gap: 8px;
    font-size: 0.8rem; color: #94a3c7;
}
.ev-card-meta-item i { width: 16px; text-align: center; }
.ev-cap-label {
    display: flex; justify-content: space-between;
    font-size: 0.7rem; color: #5b6a8a; margin-bottom: 5px;
}
.ev-cap-bar {
    width: 100%; height: 4px;
    background: rgba(255,255,255,0.06);
    border-radius: 4px; overflow: hidden;
}
.ev-cap-fill {
    height: 100%; border-radius: 4px;
    transition: width 0.5s ease;
}
.ev-card-footer {
    padding: 0 24px 22px;
}
.ev-register-btn {
    width: 100%;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 13px;
    background: transparent;
    border: 2px solid #00d4ff;
    color: #00d4ff;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 0.82rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 1.5px;
    border-radius: 12px; cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}
.ev-register-btn:hover {
    background: rgba(0,212,255,0.1);
    box-shadow: 0 0 20px rgba(0,212,255,0.2);
    color: #fff;
}
.ev-register-btn:disabled {
    opacity: 0.4; cursor: not-allowed;
    border-color: rgba(255,255,255,0.15); color: #5b6a8a;
}

@media (max-width: 900px) {
    .events-grid { grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px; }
    .container { padding: 0 20px !important; }
}
@media (max-width: 600px) {
    .events-grid { grid-template-columns: 1fr; gap: 20px; }
    .container { padding: 0 14px !important; }
}
</style>

<!-- Page Header -->
<div style="margin-bottom: 10px; text-align: center; padding-top: 10px;">
    <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(0,212,255,0.06); border: 1px solid rgba(0,212,255,0.15); padding: 6px 16px; border-radius: 50px; margin-bottom: 16px;">
        <i class="fa-solid fa-trophy" style="font-size: 0.7rem; color: var(--accent-1);"></i>
        <span style="font-size: 0.7rem; font-weight: 700; color: var(--accent-1); text-transform: uppercase; letter-spacing: 2px;">Live Events</span>
    </div>
    <h1 style="font-family: 'Space Grotesk', sans-serif; font-size: 3rem; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 10px;">
        <span style="background: var(--grad-primary); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">Competitions</span>
    </h1>
    <p style="color: var(--text-secondary); max-width: 400px; margin: 0 auto; font-size: 0.9rem;">Choose your challenge and register to compete at NEXUS 2026</p>
</div>

<?php if ($success): ?>
<div style="padding: 14px 20px; border-radius: 14px; border: 1px solid rgba(16,185,129,0.2); background: rgba(16,185,129,0.06); color: var(--text-primary); margin-bottom: 20px; font-size: 0.88rem; display: flex; align-items: center; gap: 10px;">
    <i class="fa-solid fa-circle-check" style="color: var(--success);"></i>
    Registration Successful! View your events in the <a href="dashboard.php" style="color: var(--accent-2); font-weight: 700; text-decoration: none;">Dashboard →</a>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div style="padding: 14px 20px; border-radius: 14px; border: 1px solid rgba(244,63,94,0.2); background: rgba(244,63,94,0.06); color: var(--danger); margin-bottom: 20px; font-size: 0.88rem; display: flex; align-items: center; gap: 10px;">
    <i class="fa-solid fa-circle-exclamation"></i>
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<!-- Events Grid -->
<div class="events-grid">
    <?php foreach ($events as $index => $event): ?>
    <?php
        $fill = $event['max_participants'] > 0 ? ($event['current_participants'] / $event['max_participants']) * 100 : 0;
        $is_full = $event['current_participants'] >= $event['max_participants'];
        $cat_color = $event['category'] == 'IT' ? 'var(--accent-1)' : 'var(--accent-2)';
        $cap_color = $fill > 80 ? 'var(--danger)' : 'linear-gradient(90deg, var(--accent-2), var(--accent-1))';
    ?>
    <div class="ev-card" style="animation: fadeInUp <?= 0.2 + ($index * 0.07) ?>s ease forwards; opacity: 0;">
        
        <!-- Image Header -->
        <div class="ev-card-img">
            <?php if (!empty($event['image'])): ?>
                <img src="<?= htmlspecialchars($event['image']) ?>" alt="<?= htmlspecialchars($event['name']) ?>" loading="lazy">
            <?php else: ?>
                <div class="ev-card-img-placeholder">
                    <i class="fa-solid fa-microchip" style="font-size: 3.5rem; color: <?= $cat_color ?>; opacity: 0.3;"></i>
                </div>
            <?php endif; ?>
            <div class="ev-card-overlay"></div>
            <div class="ev-card-badge">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: <?= $cat_color ?>;"></span>
                <span style="font-size: 0.62rem; font-weight: 800; color: white; text-transform: uppercase; letter-spacing: 1.5px;"><?= htmlspecialchars($event['category']) ?></span>
            </div>
        </div>

        <!-- Card Body -->
        <div class="ev-card-body">
            <h3 class="ev-card-title"><?= htmlspecialchars($event['name']) ?></h3>
            <p class="ev-card-desc">
                <?= htmlspecialchars($event['description'] ?: 'Challenge yourself in the ' . $event['name'] . ' competition track at NEXUS 2026.') ?>
            </p>

            <!-- Meta Info -->
            <div class="ev-card-meta">
                <div class="ev-card-meta-item">
                    <i class="fa-regular fa-calendar" style="color: var(--accent-1);"></i>
                    <?= date('d M Y', strtotime($event['date'])) ?> · <?= date('h:i A', strtotime($event['time'])) ?>
                </div>
                <div class="ev-card-meta-item">
                    <i class="fa-solid fa-location-dot" style="color: var(--accent-2);"></i>
                    <?= htmlspecialchars($event['venue']) ?>
                </div>
            </div>

            <!-- Capacity Bar -->
            <div style="margin-bottom: 4px;">
                <div class="ev-cap-label">
                    <span>Capacity</span>
                    <span style="color: var(--text-secondary); font-weight: 700;"><?= $event['current_participants'] ?> / <?= $event['max_participants'] ?></span>
                </div>
                <div class="ev-cap-bar">
                    <div class="ev-cap-fill" style="width: <?= $fill ?>%; background: <?= $cap_color ?>;"></div>
                </div>
            </div>
        </div>

        <!-- Action Footer -->
        <div class="ev-card-footer">
            <?php if ($user): ?>
                <form action="register_event.php" method="POST">
                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                    <button type="submit" class="ev-register-btn" <?= $is_full ? 'disabled' : '' ?>>
                        <?php if ($is_full): ?>
                            <i class="fa-solid fa-ban"></i> Full House
                        <?php else: ?>
                            <i class="fa-solid fa-bolt"></i> Register Now
                        <?php endif; ?>
                    </button>
                </form>
            <?php else: ?>
                <a href="login.php" class="ev-register-btn">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In to Register
                </a>
            <?php endif; ?>
        </div>

    </div>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>