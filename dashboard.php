<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
include 'includes/header.php'; 

$userinfo = $_SESSION['user'];
$user_id = $userinfo['user_id'];

// Fetch user's registered events with scores
$stmt = $pdo->prepare("SELECT e.*, r.status as reg_status, r.score FROM events e JOIN registrations r ON e.id = r.event_id WHERE r.user_id = ? ORDER BY e.date");
$stmt->execute([$user_id]);
$myEvents = $stmt->fetchAll();

// Fetch public announcements
$stmt = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5");
$announcements = $stmt->fetchAll();
?>

<div style="padding: 40px;">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Welcome back, <?= htmlspecialchars($userinfo['name']) ?>!</h1>
            <p>Track your registrations, scores, and get your digital entry pass.</p>
        </div>
        <div class="header-actions">
            <span class="status-tag" style="background: rgba(99, 102, 241, 0.1); color: var(--primary); padding: 8px 16px; font-size: 0.8rem;">
                STUDENT ID: <?= $user_id ?>
            </span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px; margin-top: 30px;">
        <!-- Registrations Table -->
        <div class="glass-panel" style="padding: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h2 style="font-family: 'Outfit'; font-size: 1.5rem;">My Registrations</h2>
                <a href="events.php" class="btn-coord" style="text-decoration: none;">BROWSE MORE EVENTS</a>
            </div>

            <?php if(empty($myEvents)): ?>
                <div style="text-align: center; padding: 60px 0;">
                    <i class="fa-solid fa-calendar-plus" style="font-size: 3rem; color: var(--text-dim); margin-bottom: 20px;"></i>
                    <p style="color: var(--text-muted); font-size: 1.1rem; margin-bottom: 20px;">You haven't registered for any events yet.</p>
                    <a href="events.php" class="btn-start" style="width: auto; padding: 12px 30px; text-decoration: none; display: inline-block;">EXPLORE COMPETITIONS</a>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <th style="padding: 16px; text-align: left; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Event</th>
                                <th style="padding: 16px; text-align: left; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Schedule</th>
                                <th style="padding: 16px; text-align: center; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Status</th>
                                <th style="padding: 16px; text-align: right; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Entry Pass</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($myEvents as $ev): ?>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 16px;">
                                        <div style="font-weight: 600;"><?= htmlspecialchars($ev['name']) ?></div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= $ev['category'] ?></div>
                                    </td>
                                    <td style="padding: 16px;">
                                        <div style="font-size: 0.9rem;"><?= date('d M, Y', strtotime($ev['date'])) ?></div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= date('h:i A', strtotime($ev['time'])) ?></div>
                                    </td>
                                    <td style="padding: 16px; text-align: center;">
                                        <span style="padding: 4px 10px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; background: rgba(16, 185, 129, 0.1); color: var(--success); text-transform: uppercase;">
                                            <?= strtoupper($ev['reg_status']) ?>
                                        </span>
                                    </td>
                                    <td style="padding: 16px; text-align: right; display: flex; gap: 8px; justify-content: flex-end;">
                                        <button onclick="showTicket('<?= $ev['id'] ?>', '<?= htmlspecialchars(addslashes($ev['name'])) ?>')" class="btn-coord" style="padding: 6px 12px; font-size: 0.7rem; background: rgba(168, 85, 247, 0.1); color: var(--secondary); border: 1px solid var(--secondary);">
                                            <i class="fa-solid fa-qrcode"></i> TICKET
                                        </button>
                                        
                                        <?php if($ev['reg_status'] !== 'registered'): ?>
                                            <a href="certificate.php?event_id=<?= $ev['id'] ?>" target="_blank" class="btn-coord" style="padding: 6px 12px; font-size: 0.7rem; background: rgba(212, 175, 55, 0.1); color: #D4AF37; border: 1px solid #D4AF37; text-decoration: none;">
                                                <i class="fa-solid fa-file-award"></i> CERTIFICATE
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar Info -->
        <div style="display: flex; flex-direction: column; gap: 30px;">
            <!-- Ticket Information -->
            <div class="glass-panel" style="padding: 30px; text-align: center; border-color: var(--primary);">
                <i class="fa-solid fa-ticket" style="font-size: 2.5rem; color: var(--primary); margin-bottom: 15px;"></i>
                <h3 style="font-size: 1.1rem; margin-bottom: 15px; color: white;">Event Entry System</h3>
                <p style="font-size: 0.85rem; color: var(--text-dim); line-height: 1.6;">Attendance is strict! You must present the specific QR Ticket for each event to the coordinator. Global passes are not accepted.</p>
            </div>

            <!-- Profile Info -->
            <div class="glass-panel" style="padding: 30px;">
                <h3 style="font-size: 1rem; margin-bottom: 20px;">Profile Details</h3>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div>
                        <div style="font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px;">College</div>
                        <div style="font-size: 0.9rem;"><?= htmlspecialchars($userinfo['college']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px;">Course</div>
                        <div style="font-size: 0.9rem;"><?= htmlspecialchars($userinfo['course']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px;">Email</div>
                        <div style="font-size: 0.9rem;"><?= htmlspecialchars($userinfo['email']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Modal -->
<div id="qrModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
    <div class="glass-panel" style="background: rgba(17, 24, 39, 0.95); padding: 40px; text-align: center; border-radius: 20px; border: 1px solid var(--primary); max-width: 350px; width: 90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 id="modal-event-name" style="font-family: 'Outfit'; font-size: 1.2rem; color: var(--primary); margin: 0;">Event Ticket</h3>
            <button onclick="closeModal()" style="background: none; border: none; color: var(--text-dim); cursor: pointer; font-size: 1.2rem;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <div id="qrcode" style="background: white; padding: 15px; display: inline-block; border-radius: 12px; margin-bottom: 20px; min-width: 200px; min-height: 200px;"></div>
        
        <div style="font-size: 0.8rem; color: var(--text-dim); line-height: 1.5;">
            Present this unique QR code to the event coordinator to mark your attendance.
        </div>
        <div style="margin-top: 15px; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">
            ID: <?= $user_id ?>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    let qrObj = null;

    function showTicket(eventId, eventName) {
        document.getElementById('modal-event-name').innerText = eventName;
        
        const qrContainer = document.getElementById("qrcode");
        qrContainer.innerHTML = ''; // Clear previous QR
        
        // Generate Event-Specific QR Format: EVENT_QR|USER_ID|EVENT_ID
        const qrData = "EVENT_QR|<?= $user_id ?>|" + eventId;
        
        qrObj = new QRCode(qrContainer, {
            text: qrData,
            width: 200,
            height: 200,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
        
        document.getElementById('qrModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('qrModal').style.display = 'none';
        if(qrObj) {
            qrObj.clear();
        }
    }
</script>

<?php include 'includes/footer.php'; ?>

