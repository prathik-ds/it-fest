<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'coordinator' && $_SESSION['user']['role'] !== 'admin')) {
    header('Location: index.php');
    exit;
}
include 'includes/header.php'; 


$c_id = $_SESSION['user']['user_id'];
$msg = $_GET['msg'] ?? '';

// Fetch assigned events
$stmt = $pdo->prepare("SELECT * FROM events WHERE coordinator_id = ? ORDER BY date, time");
$stmt->execute([$c_id]);
$assignedEvents = $stmt->fetchAll();

// Handle active event selection
$active_event_id = $_GET['manage_event'] ?? null;
$active_event = null;
$participants = [];

if ($active_event_id) {
    // Verify ownership
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND coordinator_id = ?");
    $stmt->execute([$active_event_id, $c_id]);
    $active_event = $stmt->fetch();
    
    if ($active_event) {
        $stmt = $pdo->prepare("SELECT u.name, u.email, u.phone, u.college, r.user_id, r.id as reg_id, r.score, r.status, r.attendance FROM users u JOIN registrations r ON u.user_id = r.user_id WHERE r.event_id = ? ORDER BY u.name");
        $stmt->execute([$active_event_id]);
        $participants = $stmt->fetchAll();
    }
}
?>

<div style="padding: 30px 40px;">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>
                <span class="brand-icon" style="background: rgba(99, 102, 241, 0.2); border: 1px solid var(--primary); color: var(--primary); width: 36px; height: 36px; font-size: 0.9rem;">
                    <i class="fa-solid fa-calendar-days"></i>
                </span>
                My Assigned Events
            </h1>
            <p>Manage check-ins, status updates, and results for your events.</p>
        </div>
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Search your events..." id="eventSearch">
        </div>
    </div>
</div>

<?php if($msg): ?>
    <div style="margin: 0 40px 20px; padding: 15px; background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); color: var(--success); border-radius: 12px; font-size: 0.9rem;">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<?php if (!$active_event_id): ?>
    <!-- Grid View of Events -->
    <div class="events-grid-dash">
        <?php foreach($assignedEvents as $ev): ?>
            <?php
                // Get participant count for this event
                $pst = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = ?");
                $pst->execute([$ev['id']]);
                $pCount = $pst->fetchColumn();
            ?>
            <div class="event-card-dash">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <span class="status-tag" style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 4px 10px; border-radius: 6px; font-size: 0.6rem; font-weight: 800; text-transform: uppercase;">
                        ASSIGNED
                    </span>
                    <span style="font-size: 0.75rem; color: var(--text-dim);">
                        <i class="fa-regular fa-clock" style="margin-right: 5px;"></i> <?= date('h:i A', strtotime($ev['time'])) ?>
                    </span>
                </div>
                
                <h3 style="font-family: 'Outfit'; font-size: 1.3rem; margin-bottom: 10px;"><?= htmlspecialchars($ev['name']) ?></h3>
                
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-location-dot" style="color: var(--primary);"></i>
                    <?= $ev['venue'] ?: 'Venue TBD' ?>
                </div>

                <div style="display: flex; gap: 12px; margin-bottom: 25px;">
                    <div class="stat-box-dash">
                        <span style="font-size: 1.1rem; font-weight: 800; color: var(--primary);"><?= $pCount ?></span>
                        <span style="font-size: 0.6rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px;">Registered</span>
                    </div>
                    <div class="stat-box-dash">
                        <span style="font-size: 1.1rem; font-weight: 800; color: var(--secondary);"><?= $ev['category'] ?></span>
                        <span style="font-size: 0.6rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px;">Track</span>
                    </div>
                </div>

                <a href="coordinator.php?manage_event=<?= $ev['id'] ?>" class="btn-start-dash" style="text-decoration: none; display: block; text-align: center; margin-bottom: 10px;">
                    MANAGE EVENT
                </a>
                <a href="coordinator.php?manage_event=<?= $ev['id'] ?>&view=results" style="text-decoration: none; display: block; text-align: center; color: var(--text-muted); font-size: 0.75rem; font-weight: 600; padding: 10px; border-radius: 10px; border: 1px solid var(--border); transition: 0.3s;" onmouseover="this.style.borderColor='var(--secondary)'; this.style.color='var(--text-main)'" onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text-muted)'">
                    <i class="fa-solid fa-trophy" style="margin-right: 6px;"></i> LEADERBOARD / RESULTS
                </a>
            </div>
        <?php endforeach; ?>

        <?php if(empty($assignedEvents)): ?>
            <div class="glass-panel-dash" style="grid-column: 1/-1; text-align: center; padding: 80px; border-style: dashed;">
                <i class="fa-solid fa-calendar-xmark" style="font-size: 3rem; color: var(--text-dim); opacity: 0.3; margin-bottom: 20px;"></i>
                <h3 style="color: var(--text-muted); font-family: 'Outfit';">No Assignments Yet</h3>
                <p style="color: var(--text-dim); font-size: 0.9rem;">Check back later or contact Admin for track duties.</p>
            </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <!-- Management View for Selection Event -->
    <div style="padding: 0 40px 40px;">
        <div class="glass-panel" style="padding: 30px; margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <a href="coordinator.php" style="color: var(--primary); text-decoration: none; font-size: 0.8rem; font-weight: 700; display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                        <i class="fa-solid fa-arrow-left"></i> BACK TO EVENTS
                    </a>
                    <h2 style="font-size: 2rem; font-family: 'Outfit', sans-serif;"><?= htmlspecialchars($active_event['name']) ?></h2>
                    <p style="color: var(--text-dim)">Manage participants and update scores for this event.</p>
                </div>
                <div style="display: flex; gap: 12px;">
                    <button onclick="startScanner()" class="btn-results" style="background: var(--bg-card); border-color: var(--primary); color: var(--primary);">
                        <i class="fa-solid fa-camera"></i> SCAN ATTENDANCE
                    </button>
                    <a href="export_data.php?type=participation&event_id=<?= $active_event_id ?>" class="btn-results">
                        <i class="fa-solid fa-file-csv"></i> EXPORT CSV
                    </a>
                </div>
            </div>

            <!-- QR Scanner Modal -->
            <div id="scanner-container" class="glass-panel" style="display: none; padding: 30px; margin-bottom: 30px; text-align: center; border-color: var(--primary);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="font-size: 1rem; color: var(--primary);">Scanner Active</h3>
                    <button onclick="stopScanner()" style="background: none; border: none; color: var(--danger); cursor: pointer;"><i class="fa-solid fa-xmark"></i> CLOSE</button>
                </div>
                <div id="reader" style="width: 100%; max-width: 500px; margin: 0 auto; border-radius: 16px; overflow: hidden;"></div>
                <p id="scanner-msg" style="margin-top: 20px; color: var(--text-muted);">Frame the QR code to mark attendance</p>
            </div>

            <!-- Participant Table -->
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <th style="padding: 16px; text-align: left; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Participant</th>
                            <th style="padding: 16px; text-align: center; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Attendance</th>
                            <th style="padding: 16px; text-align: center; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Score</th>
                            <th style="padding: 16px; text-align: center; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Status</th>
                            <th style="padding: 16px; text-align: right; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($participants as $p): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <form action="coordinator_actions.php" method="POST">
                                    <input type="hidden" name="reg_id" value="<?= $p['reg_id'] ?>">
                                    <input type="hidden" name="event_id" value="<?= $active_event_id ?>">
                                    <td style="padding: 16px;">
                                        <div style="font-weight: 600;"><?= htmlspecialchars($p['name']) ?></div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= $p['user_id'] ?> • <?= htmlspecialchars($p['college']) ?></div>
                                    </td>
                                    <td style="padding: 16px; text-align: center;">
                                        <select name="attendance" style="background: var(--bg-dark); border: 1px solid var(--border); color: white; padding: 6px 12px; border-radius: 8px;">
                                            <option value="absent" <?= $p['attendance'] == 'absent' ? 'selected' : '' ?>>Absent</option>
                                            <option value="present" <?= $p['attendance'] == 'present' ? 'selected' : '' ?>>Present</option>
                                        </select>
                                    </td>
                                    <td style="padding: 16px; text-align: center;">
                                        <input type="number" name="score" value="<?= $p['score'] ?>" style="width: 70px; background: var(--bg-dark); border: 1px solid var(--border); color: white; padding: 6px; border-radius: 8px; text-align: center;">
                                    </td>
                                    <td style="padding: 16px; text-align: center;">
                                        <select name="status" style="background: var(--bg-dark); border: 1px solid var(--border); color: white; padding: 6px 12px; border-radius: 8px;">
                                            <option value="registered" <?= $p['status'] == 'registered' ? 'selected' : '' ?>>Reg.</option>
                                            <option value="participated" <?= $p['status'] == 'participated' ? 'selected' : '' ?>>Played</option>
                                            <option value="winner" <?= $p['status'] == 'winner' ? 'selected' : '' ?>>Winner</option>
                                            <option value="runner" <?= $p['status'] == 'runner' ? 'selected' : '' ?>>Runner</option>
                                        </select>
                                    </td>
                                    <td style="padding: 16px; text-align: right;">
                                        <button type="submit" class="btn-coord" style="padding: 6px 16px; font-size: 0.7rem;">UPDATE</button>
                                    </td>
                                </form>
                            </tr>
                        <?php endforeach; ?>
                        <?php if(empty($participants)): ?>
                            <tr><td colspan="5" style="padding: 60px; text-align: center; color: var(--text-dim);">No participants found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let html5QrcodeScanner = null;

    function startScanner() {
        document.getElementById('scanner-container').style.display = 'block';
        document.getElementById('scanner-msg').innerText = "Initializing camera...";
        
        html5QrcodeScanner = new Html5Qrcode("reader");
        const activeEventId = "<?= $active_event_id ?>";

        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
            stopScanner(); // Stop scanning immediately after detecting

            // Check if it's the new EVENT_QR format
            if(decodedText.startsWith("EVENT_QR|")) {
                const parts = decodedText.split("|");
                if(parts.length === 3) {
                    const scannedUserId = parts[1];
                    const scannedEventId = parts[2];

                    if(scannedEventId !== activeEventId) {
                        document.getElementById('scanner-msg').innerHTML = "<b style='color: var(--danger);'>ERROR: EVENT MISMATCH!</b><br>This ticket is for a different event.";
                        alert("ERROR: This QR ticket is for a different event! It cannot be used here.");
                        return;
                    }

                    document.getElementById('scanner-msg').innerHTML = "<b style='color: var(--success);'>TICKET VALIDATED:</b> " + scannedUserId;
                    markAttendance(scannedUserId);
                } else {
                    document.getElementById('scanner-msg').innerHTML = "<b style='color: var(--danger);'>ERROR:</b> Invalid ticket format.";
                }
            } else {
                document.getElementById('scanner-msg').innerHTML = "<b style='color: var(--danger);'>ERROR: GLOBAL QR REJECTED</b><br>Please ask the participant to open their specific Event Ticket from their dashboard.";
                alert("Global QR Passes are no longer accepted. The participant must generate an event-specific ticket from their dashboard.");
            }
        };

        html5QrcodeScanner.start({ facingMode: "environment" }, { fps: 10, qrbox: 250 }, qrCodeSuccessCallback)
            .then(() => {
                document.getElementById('scanner-msg').innerText = "Scanner Ready. Scan Participant Event Ticket.";
            })
            .catch(err => {
                document.getElementById('scanner-msg').innerText = "Camera error: " + err;
            });
    }

    function stopScanner() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                document.getElementById('scanner-container').style.display = 'none';
            });
        }
    }

    function markAttendance(userId) {
        fetch('coordinator_actions_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `user_id=${userId}&event_id=<?= $active_event_id ?>&action=qr_attendance`
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                alert("Success: " + data.student_name + " marked present.");
                location.reload();
            } else alert("Error: " + data.message);
        });
    }

    document.getElementById('eventSearch')?.addEventListener('keyup', (e) => {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('.event-card-dash').forEach(card => {
            const name = card.querySelector('h3').innerText.toLowerCase();
            card.style.display = name.includes(term) ? 'block' : 'none';
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
