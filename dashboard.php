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

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
    <!-- Profile & Stats -->
    <div>
        <div class="glass neon-border-blue" style="padding: 30px; text-align: center; margin-bottom: 30px;">
            <div style="width: 80px; height: 80px; background: rgba(0, 243, 255, 0.1); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-size: 2rem; border: 1px solid var(--neon-blue);">
                👤
            </div>
            <h2 class="orbitron neon-text-blue" style="font-size: 1.2rem; margin-bottom: 5px;"><?= htmlspecialchars($userinfo['name']) ?></h2>
            <p style="color: #666; font-size: 0.8rem; letter-spacing: 2px;"><?= $user_id ?></p>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.05); text-align: left;">
                <p style="margin-bottom: 10px; font-size: 0.85rem; color: #888;">EMAIL: <span style="color: #fff;"><?= htmlspecialchars($userinfo['email']) ?></span></p>
                <p style="margin-bottom: 10px; font-size: 0.85rem; color: #888;">COLLEGE: <span style="color: #fff;"><?= htmlspecialchars($userinfo['college']) ?></span></p>
                <p style="margin-bottom: 10px; font-size: 0.85rem; color: #888;">COURSE: <span style="color: #fff;"><?= htmlspecialchars($userinfo['course']) ?></span></p>
            </div>
        </div>

        <!-- Announcement Board -->
        <div class="glass neon-border-blue" style="padding: 30px; border-color: var(--neon-purple);">
            <h3 class="orbitron neon-text-purple" style="font-size: 1rem; margin-bottom: 20px;">Notice Board</h3>
            <div style="max-height: 400px; overflow-y: auto;" class="scrollbar-hide">
                <?php if(empty($announcements)): ?>
                    <p style="color: #555; text-align: center;">No updates yet.</p>
                <?php endif; ?>
                <?php foreach($announcements as $notice): ?>
                    <div style="padding-bottom: 15px; margin-bottom: 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                        <p style="font-size: 0.7rem; color: var(--neon-blue); margin-bottom: 5px;"><?= date('D, d M', strtotime($notice['created_at'])) ?></p>
                        <h4 style="font-size: 0.9rem; margin-bottom: 5px; color: #fff;"><?= htmlspecialchars($notice['title']) ?></h4>
                        <p style="font-size: 0.8rem; color: #777; line-height: 1.4;"><?= htmlspecialchars($notice['content']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Registered Events Table -->
    <div class="glass neon-border-blue" style="padding: 30px;">
        <h2 class="orbitron neon-text-blue" style="font-size: 1.5rem; margin-bottom: 30px;">My Registrations</h2>
        
        <?php if(empty($myEvents)): ?>
            <div style="text-align: center; padding: 60px 0;">
                <p style="color: #555; font-size: 1.1rem; margin-bottom: 20px;">You haven't registered for any events yet.</p>
                <a href="events.php" class="btn-neon">EXPLORE COMPETITIONS</a>
            </div>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid rgba(0, 243, 255, 0.2);">
                        <th style="padding: 15px; font-family: 'Orbitron'; font-size: 0.8rem; color: #777;">EVENT</th>
                        <th style="padding: 15px; font-family: 'Orbitron'; font-size: 0.8rem; color: #777;">DATE/TIME</th>
                        <th style="padding: 15px; font-family: 'Orbitron'; font-size: 0.8rem; color: #777;">STATUS</th>
                        <th style="padding: 15px; font-family: 'Orbitron'; font-size: 0.8rem; color: #777;">SCORE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($myEvents as $ev): ?>
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.03);">
                            <td style="padding: 15px;">
                                <span style="font-weight: 700; color: #fff;"><?= htmlspecialchars($ev['name']) ?></span><br>
                                <span style="font-size: 0.7rem; color: #555;"><?= $ev['category'] ?></span>
                            </td>
                            <td style="padding: 15px; font-size: 0.85rem; color: #999;">
                                <?= date('d M', strtotime($ev['date'])) ?><br><?= date('H:i', strtotime($ev['time'])) ?>
                            </td>
                            <td style="padding: 15px;">
                                <span style="padding: 4px 10px; border-radius: 4px; font-size: 0.7rem; font-weight: 900; background: rgba(50, 200, 50, 0.1); color: #0f0;">
                                    <?= strtoupper($ev['reg_status']) ?>
                                </span>
                            </td>
                            <td style="padding: 15px; font-weight: 600; color: var(--neon-pink);">
                                <?= $ev['score'] > 0 ? $ev['score'] : '--' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="glass neon-border-blue" style="padding: 20px; text-align: center; margin-top: 40px; border-color: var(--neon-pink);">
            <h3 class="orbitron neon-text-pink" style="font-size: 0.9rem; margin-bottom: 20px;">Digital Entry Pass</h3>
            <div id="qrcode" style="background: #fff; padding: 15px; display: inline-block; border-radius: 8px; margin-bottom: 15px;"></div>
            <p style="font-size: 0.7rem; color: #777; line-height: 1.5;">Show this QR code at the event venue for instant attendance check-in.</p>
        </div>

        <div style="margin-top: 40px; padding: 20px; background: rgba(0, 243, 255, 0.03); border: 1px dashed rgba(0, 243, 255, 0.2); border-radius: 8px;">
            <h4 class="orbitron" style="font-size: 0.8rem; margin-bottom: 10px; color: #777;">💡 Quick Note</h4>
            <p style="font-size: 0.8rem; color: #666; line-height: 1.5;">Remember to bring your valid college ID and the unique code <span class="neon-text-blue"><?= $user_id ?></span> for check-in at the venue. Good luck!</p>
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
