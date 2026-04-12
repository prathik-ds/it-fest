<?php 
include 'includes/header.php'; 

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'coordinator') {
    header('Location: index.php');
    exit;
}

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

<div class="page-header">
    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 10px;">
        <div class="glass" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--accent-blue);">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div>
            <h1 class="page-title">My Assigned Events</h1>
            <p class="page-subtitle">Manage check-ins, status updates, and results for your events.</p>
        </div>
    </div>
</div>

<?php if($msg): ?>
    <div class="glass" style="padding: 15px 25px; border-color: var(--accent-blue); color: #fff; margin-bottom: 30px; display: flex; align-items: center; gap: 15px;">
        <i class="fas fa-check-circle" style="color: #10b981;"></i>
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<?php if(!$active_event_id): ?>
    <!-- Grid View of All Assigned Events -->
    <div class="event-grid">
        <?php foreach($assignedEvents as $ev): ?>
            <?php
            // Get participant count for each event
            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = ?");
            $stmtCount->execute([$ev['id']]);
            $pCount = $stmtCount->fetchColumn();
            ?>
            <div class="glass event-card">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="badge badge-published">Published</span>
                    <div class="event-time">
                        <i class="far fa-clock"></i>
                        <?= date('h:i A', strtotime($ev['time'])) ?>
                    </div>
                </div>

                <h3 class="event-name"><?= htmlspecialchars($ev['name']) ?></h3>
                
                <div class="event-venue">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= htmlspecialchars($ev['venue'] ?: 'No Venue Set') ?>
                </div>

                <div class="stats-grid">
                    <div class="stat-box">
                        <i class="fas fa-users" style="color: var(--text-secondary); margin-bottom: 5px;"></i>
                        <span class="stat-value"><?= $pCount ?></span>
                        <span class="stat-label">Participants</span>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-qrcode" style="color: var(--text-secondary); margin-bottom: 5px;"></i>
                        <span class="stat-value">Scanner</span>
                        <span class="stat-label">Attendance</span>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="coordinator.php?manage_event=<?= $ev['id'] ?>" class="btn-primary">
                        START EVENT
                    </a>
                    <a href="coordinator.php?manage_event=<?= $ev['id'] ?>&tab=results" class="btn-secondary" style="font-size: 0.8rem;">
                        <i class="fas fa-trophy" style="margin-right: 8px; color: var(--accent-purple);"></i>
                        Enter Final Results
                    </a>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if(empty($assignedEvents)): ?>
            <div class="glass" style="grid-column: 1/-1; padding: 100px; text-align: center;">
                <i class="fas fa-calendar-times" style="font-size: 4rem; color: var(--text-secondary); margin-bottom: 20px;"></i>
                <h3 style="color: var(--text-secondary);">No events assigned to you yet.</h3>
                <p style="color: var(--text-secondary); opacity: 0.6; margin-top: 10px;">Check back later or contact the administrator.</p>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- Management View for Specific Event -->
    <div style="margin-bottom: 30px;">
        <a href="coordinator.php" class="btn-secondary" style="margin-bottom: 20px;">
            <i class="fas fa-arrow-left" style="margin-right: 10px;"></i> Back to all events
        </a>

        <div class="glass" style="padding: 30px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="font-size: 2rem; font-weight: 800;"><?= htmlspecialchars($active_event['name']) ?></h2>
                <div style="display: flex; gap: 20px; margin-top: 10px; color: var(--text-secondary);">
                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($active_event['venue']) ?></span>
                    <span><i class="fas fa-users"></i> <?= count($participants) ?> Participants</span>
                </div>
            </div>
            <div style="display: flex; gap: 12px;">
                <button onclick="startScanner()" class="btn-primary">
                    <i class="fas fa-qrcode" style="margin-right: 10px;"></i> OPEN SCANNER
                </button>
            </div>
        </div>
    </div>

    <!-- QR Scanner Modal -->
    <div id="scanner-container" class="glass" style="display: none; padding: 30px; margin-bottom: 30px; text-align: center; border-color: var(--accent-blue);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 1.2rem; font-weight: 700;">Attendance Scanner</h3>
            <button onclick="stopScanner()" class="btn-secondary" style="padding: 8px 15px;">CLOSE</button>
        </div>
        <div id="reader" style="width: 100%; max-width: 500px; margin: 0 auto; border-radius: 16px; overflow: hidden; border: 2px solid var(--accent-blue);"></div>
        <p id="scanner-msg" style="margin-top: 20px; color: var(--text-secondary);">Position the QR code within the frame.</p>
    </div>

    <div class="glass" style="overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: rgba(255,255,255,0.02); border-bottom: 1px solid var(--glass-border);">
                    <th style="padding: 20px; text-align: left; color: var(--text-secondary); font-size: 0.8rem; text-transform: uppercase;">Participant</th>
                    <th style="padding: 20px; text-align: center; color: var(--text-secondary); font-size: 0.8rem; text-transform: uppercase;">Attendance</th>
                    <th style="padding: 20px; text-align: center; color: var(--text-secondary); font-size: 0.8rem; text-transform: uppercase;">Score</th>
                    <th style="padding: 20px; text-align: center; color: var(--text-secondary); font-size: 0.8rem; text-transform: uppercase;">Status</th>
                    <th style="padding: 20px; text-align: right; color: var(--text-secondary); font-size: 0.8rem; text-transform: uppercase;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($participants as $p): ?>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <form action="coordinator_actions.php" method="POST">
                            <input type="hidden" name="reg_id" value="<?= $p['reg_id'] ?>">
                            <input type="hidden" name="event_id" value="<?= $active_event_id ?>">
                            <td style="padding: 20px;">
                                <div style="font-weight: 700; font-size: 1.1rem;"><?= htmlspecialchars($p['name']) ?></div>
                                <div style="font-size: 0.85rem; color: var(--text-secondary);"><?= htmlspecialchars($p['college']) ?></div>
                            </td>
                            <td style="padding: 20px; text-align: center;">
                                <select name="attendance" class="glass" style="padding: 8px; border-radius: 8px; width: 120px;">
                                    <option value="absent" <?= $p['attendance'] == 'absent' ? 'selected' : '' ?>>Absent</option>
                                    <option value="present" <?= $p['attendance'] == 'present' ? 'selected' : '' ?>>Present</option>
                                </select>
                            </td>
                            <td style="padding: 20px; text-align: center;">
                                <input type="number" name="score" value="<?= $p['score'] ?>" class="glass" style="width: 80px; padding: 8px; text-align: center; border-radius: 8px;">
                            </td>
                            <td style="padding: 20px; text-align: center;">
                                <select name="status" class="glass" style="padding: 8px; border-radius: 8px; width: 140px;">
                                    <option value="registered" <?= $p['status'] == 'registered' ? 'selected' : '' ?>>Registered</option>
                                    <option value="participated" <?= $p['status'] == 'participated' ? 'selected' : '' ?>>Participated</option>
                                    <option value="winner" <?= $p['status'] == 'winner' ? 'selected' : '' ?>>Winner</option>
                                    <option value="runner" <?= $p['status'] == 'runner' ? 'selected' : '' ?>>Runner Up</option>
                                </select>
                            </td>
                            <td style="padding: 20px; text-align: right;">
                                <button type="submit" class="btn-primary" style="padding: 8px 16px; font-size: 0.8rem;">UPDATE</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
                <?php if(empty($participants)): ?>
                    <tr><td colspan="5" style="padding: 60px; text-align: center; color: var(--text-secondary); opacity: 0.5;">No participants yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let html5QrcodeScanner = null;

    function startScanner() {
        document.getElementById('scanner-container').style.display = 'block';
        document.getElementById('scanner-msg').innerText = "Starting Camera...";
        
        html5QrcodeScanner = new Html5Qrcode("reader");
        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
            document.getElementById('scanner-msg').innerHTML = "<b>SCANNED:</b> " + decodedText + ". Please wait...";
            markAttendance(decodedText);
            stopScanner();
        };
        const config = { fps: 10, qrbox: 250 };

        html5QrcodeScanner.start({ facingMode: "environment" }, config, qrCodeSuccessCallback)
            .then(() => {
                document.getElementById('scanner-msg').innerText = "Scanner Ready. Scan Student Entry Pass.";
            })
            .catch(err => {
                document.getElementById('scanner-msg').innerText = "Error starting scanner: " + err;
            });
    }

    function stopScanner() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                document.getElementById('scanner-container').style.display = 'none';
                html5QrcodeScanner.clear();
            }).catch(error => {
                console.warn("Failed to stop scanner", error);
                document.getElementById('scanner-container').style.display = 'none';
            });
        } else {
            document.getElementById('scanner-container').style.display = 'none';
        }
    }

    function markAttendance(userId) {
        const eventId = "<?= $active_event_id ?>";
        fetch('coordinator_actions_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `user_id=${userId}&event_id=${eventId}&action=qr_attendance`
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert("Attendance Marked for: " + data.student_name);
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Network error occurred.");
        });
    }
</script>

<?php include 'includes/footer.php'; ?>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let html5QrcodeScanner = null;

    function startScanner() {
        document.getElementById('scanner-container').style.display = 'block';
        document.getElementById('scanner-msg').innerText = "Starting Camera...";
        
        html5QrcodeScanner = new Html5Qrcode("reader");
        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
            document.getElementById('scanner-msg').innerHTML = "<b>SCANNED:</b> " + decodedText + ". Please wait...";
            markAttendance(decodedText);
            stopScanner();
        };
        const config = { fps: 10, qrbox: 250 };

        html5QrcodeScanner.start({ facingMode: "environment" }, config, qrCodeSuccessCallback)
            .then(() => {
                document.getElementById('scanner-msg').innerText = "Scanner Ready. Scan Student Entry Pass.";
            })
            .catch(err => {
                document.getElementById('scanner-msg').innerText = "Error starting scanner: " + err;
            });
    }

    function stopScanner() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                document.getElementById('scanner-container').style.display = 'none';
                html5QrcodeScanner.clear();
            }).catch(error => {
                console.warn("Failed to stop scanner", error);
                document.getElementById('scanner-container').style.display = 'none';
            });
        } else {
            document.getElementById('scanner-container').style.display = 'none';
        }
    }

    function markAttendance(userId) {
        const eventId = "<?= $active_event_id ?>";
        fetch('coordinator_actions_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `user_id=${userId}&event_id=${eventId}&action=qr_attendance`
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert("Attendance Marked for: " + data.student_name);
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Network error occurred.");
        });
    }
</script>

<?php include 'includes/footer.php'; ?>
