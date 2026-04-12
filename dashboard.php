<?php 
include 'includes/header.php'; 

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userinfo = $_SESSION['user'];
$user_id = $userinfo['user_id'];

// Fetch user's registered events with scores
$stmt = $pdo->prepare("SELECT e.*, r.status as reg_status, r.score FROM events e JOIN registrations r ON e.id = r.event_id WHERE r.user_id = ? ORDER BY e.date");
$stmt->execute([$user_id]);
$myEvents = $stmt->fetchAll();

// Fetch public announcements
$stmt = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC");
$announcements = $stmt->fetchAll();
?>

<div class="page-header">
    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 10px;">
        <div class="glass" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--accent-blue);">
            <i class="fas fa-th-large"></i>
        </div>
        <div>
            <h1 class="page-title">Student Control Center</h1>
            <p class="page-subtitle">Welcome back, <?= htmlspecialchars($userinfo['name']) ?>. Track your performance and events here.</p>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 350px 1fr; gap: 30px;">
    <!-- Profile & Stats -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        <div class="glass" style="padding: 30px; text-align: center;">
            <div style="width: 100px; height: 100px; background: rgba(99, 102, 241, 0.1); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; border: 2px solid var(--accent-blue);">
                <i class="fas fa-user-graduate" style="color: var(--accent-blue);"></i>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 5px;"><?= htmlspecialchars($userinfo['name']) ?></h2>
            <p style="color: var(--text-secondary); font-size: 0.9rem; letter-spacing: 1px;"><?= $user_id ?></p>
            
            <div style="margin-top: 30px; padding-top: 25px; border-top: 1px solid var(--glass-border); text-align: left; display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-envelope" style="color: var(--text-secondary); width: 20px;"></i>
                    <span style="font-size: 0.9rem;"><?= htmlspecialchars($userinfo['email']) ?></span>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-university" style="color: var(--text-secondary); width: 20px;"></i>
                    <span style="font-size: 0.9rem;"><?= htmlspecialchars($userinfo['college']) ?></span>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-book-reader" style="color: var(--text-secondary); width: 20px;"></i>
                    <span style="font-size: 0.9rem;"><?= htmlspecialchars($userinfo['course']) ?></span>
                </div>
            </div>
        </div>

        <!-- Digital Entry Pass -->
        <div class="glass" style="padding: 30px; text-align: center; border-color: var(--accent-purple);">
            <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 20px; text-transform: uppercase; color: var(--accent-purple);">Digital Entry Pass</h3>
            <div id="qrcode" style="background: #fff; padding: 15px; display: inline-block; border-radius: 12px; margin-bottom: 15px; box-shadow: 0 0 20px rgba(0,0,0,0.5);"></div>
            <p style="font-size: 0.8rem; color: var(--text-secondary); line-height: 1.5;">Show this QR code at the event venue for instant attendance check-in.</p>
        </div>
    </div>

    <!-- Main Content Area -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        <!-- Announcement Board -->
        <div class="glass" style="padding: 30px; border-color: var(--accent-blue);">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 25px;">
                <i class="fas fa-bullhorn" style="color: var(--accent-blue);"></i>
                <h3 style="font-size: 1.1rem; font-weight: 700; text-transform: uppercase;">Latest Announcements</h3>
            </div>
            <div style="max-height: 250px; overflow-y: auto; padding-right: 15px;">
                <?php if(empty($announcements)): ?>
                    <p style="color: var(--text-secondary); text-align: center; padding: 20px;">No updates yet.</p>
                <?php endif; ?>
                <?php foreach($announcements as $notice): ?>
                    <div class="glass" style="padding: 20px; margin-bottom: 15px;">
                        <span style="font-size: 0.75rem; color: var(--accent-blue); font-weight: 600; text-transform: uppercase;"><?= date('D, d M Y', strtotime($notice['created_at'])) ?></span>
                        <h4 style="font-size: 1.1rem; margin: 8px 0;"><?= htmlspecialchars($notice['title']) ?></h4>
                        <p style="font-size: 0.95rem; color: var(--text-secondary); line-height: 1.5;"><?= htmlspecialchars($notice['content']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Registered Events -->
        <div class="glass" style="padding: 30px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 30px;">
                <i class="fas fa-list-check" style="color: var(--accent-blue);"></i>
                <h3 style="font-size: 1.1rem; font-weight: 700; text-transform: uppercase;">My Registrations</h3>
            </div>
            
            <?php if(empty($myEvents)): ?>
                <div style="text-align: center; padding: 60px 0;">
                    <i class="fas fa-calendar-xmark" style="font-size: 3rem; color: var(--text-secondary); opacity: 0.3; margin-bottom: 20px;"></i>
                    <p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 30px;">You haven't registered for any events yet.</p>
                    <a href="events.php" class="btn-primary">EXPLORE COMPETITIONS</a>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--glass-border);">
                                <th style="padding: 15px; color: var(--text-secondary); font-size: 0.8rem; text-transform: uppercase;">Event</th>
                                <th style="padding: 15px; color: var(--text-secondary); font-size: 0.8rem; text-transform: uppercase;">Date/Time</th>
                                <th style="padding: 15px; text-align: center; color: var(--text-secondary); font-size: 0.8rem; text-transform: uppercase;">Status</th>
                                <th style="padding: 15px; text-align: center; color: var(--text-secondary); font-size: 0.8rem; text-transform: uppercase;">Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($myEvents as $ev): ?>
                                <tr style="border-bottom: 1px solid var(--glass-border); transition: 0.2s;">
                                    <td style="padding: 20px;">
                                        <div style="font-weight: 700; font-size: 1.1rem;"><?= htmlspecialchars($ev['name']) ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-secondary);"><?= $ev['category'] ?></div>
                                    </td>
                                    <td style="padding: 20px; font-size: 0.9rem; color: var(--text-secondary);">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <i class="far fa-calendar"></i> <?= date('d M', strtotime($ev['date'])) ?>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                                            <i class="far fa-clock"></i> <?= date('H:i', strtotime($ev['time'])) ?>
                                        </div>
                                    </td>
                                    <td style="padding: 20px; text-align: center;">
                                        <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);">
                                            <?= strtoupper($ev['reg_status']) ?>
                                        </span>
                                    </td>
                                    <td style="padding: 20px; text-align: center; font-weight: 800; color: var(--accent-blue); font-size: 1.2rem;">
                                        <?= $ev['score'] > 0 ? $ev['score'] : '--' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    new QRCode(document.getElementById("qrcode"), {
        text: "<?= $user_id ?>",
        width: 150,
        height: 150,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });
</script>

<?php include 'includes/footer.php'; ?>
