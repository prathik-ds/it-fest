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
$active_event_id = $_GET['manage_event'] ?? (count($assignedEvents) > 0 ? $assignedEvents[0]['id'] : null);
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

// CSV Export Logic (Hidden in simple implementation, triggered by link)
if (isset($_GET['export']) && $active_event) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="Participants_' . str_replace(' ', '_', $active_event['name']) . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['User ID', 'Name', 'Email', 'Phone', 'College', 'Attendance', 'Score', 'Status']);
    foreach ($participants as $p) {
        fputcsv($output, [$p['user_id'], $p['name'], $p['email'], $p['phone'], $p['college'], $p['attendance'], $p['score'], $p['status']]);
    }
    fclose($output);
    exit;
}
?>

<div style="margin-bottom: 50px;">
    <h1 class="orbitron neon-text-purple" style="font-size: 2.5rem; text-align: center;">COORDINATOR PORTAL</h1>
    <p style="color: #666; text-align: center; font-size: 0.9rem; letter-spacing: 3px;">SYNERGY EVENT MANAGEMENT INTERFACE</p>
</div>

<?php if($msg): ?>
    <div style="padding: 15px; border: 1px solid var(--neon-purple); background: rgba(188, 19, 254, 0.1); color: #fff; margin-bottom: 30px; border-radius: 8px;">
        ✅ <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<?php if(empty($assignedEvents)): ?>
    <div class="glass neon-border-blue" style="padding: 100px; text-align: center;">
        <h2 class="orbitron neon-text-pink">Access Denied / No Events Assigned</h2>
        <p style="color: #555; margin-top: 20px;">You haven't been assigned to any events yet. Contact the Admin to get events assigned to your profile.</p>
        <a href="index.php" class="btn-neon" style="margin-top: 30px;">RETURN HOME</a>
    </div>
<?php else: ?>

<div style="display: grid; grid-template-columns: 300px 1fr; gap: 30px;">
    <!-- Sidebar: Assigned Events -->
    <div class="glass neon-border-blue" style="padding: 25px; height: fit-content;">
        <h3 class="orbitron neon-text-blue" style="font-size: 0.8rem; margin-bottom: 20px;">MY ASSIGNED EVENTS</h3>
        <?php foreach($assignedEvents as $ev): ?>
            <a href="coordinator.php?manage_event=<?= $ev['id'] ?>" 
               style="display: block; padding: 15px; text-decoration: none; border-radius: 6px; margin-bottom: 10px; transition: 0.3s; border: 1px solid <?= $active_event_id == $ev['id'] ? 'var(--neon-purple)' : 'rgba(255,255,255,0.05)' ?>; background: <?= $active_event_id == $ev['id'] ? 'rgba(188, 19, 254, 0.1)' : 'transparent' ?>;">
                <p style="color: #fff; font-weight: 700; font-size: 0.9rem;"><?= htmlspecialchars($ev['name']) ?></p>
                <span style="font-size: 0.7rem; color: #555;"><?= $ev['category'] ?> | <?= date('H:i', strtotime($ev['time'])) ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Main Workspace -->
    <div class="glass neon-border-blue" style="padding: 35px; min-height: 600px; border-color: var(--neon-purple);">
        <?php if($active_event): ?>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; padding-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <div>
                    <h2 class="orbitron neon-text-purple" style="font-size: 1.8rem;"><?= htmlspecialchars($active_event['name']) ?></h2>
                    <p style="color: #666; margin-top: 5px;">
                        Venue: <span style="color: var(--neon-blue);"><?= htmlspecialchars($active_event['venue']) ?></span> | 
                        Participants: <span style="color: var(--neon-pink);"><?= count($participants) ?> / <?= $active_event['max_participants'] ?></span>
                    </p>
                </div>
                <div style="display: flex; gap: 15px;">
                    <button onclick="startScanner()" class="btn-neon" style="font-size: 0.7rem; border-color: var(--neon-blue); color: var(--neon-blue);">QR SCANNER</button>
                    <a href="coordinator.php?manage_event=<?= $active_event_id ?>&export=1" class="btn-neon" style="font-size: 0.7rem; border-color: #fff; color: #fff;">DOWNLOAD LIST (.CSV)</a>
                </div>
            </div>

            <!-- QR Scanner Modal -->
            <div id="scanner-container" class="glass neon-border-blue" style="display: none; padding: 20px; margin-bottom: 30px; text-align: center; border-color: var(--neon-blue);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 class="orbitron neon-text-blue" style="font-size: 0.9rem;">Attendance Scanner Active</h3>
                    <button onclick="stopScanner()" style="background: none; border: none; color: var(--neon-pink); cursor: pointer; font-size: 0.8rem;">[ CLOSE ]</button>
                </div>
                <div id="reader" style="width: 100%; max-width: 400px; margin: 0 auto; background: #000; border-radius: 8px; overflow: hidden;"></div>
                <div id="scanner-msg" style="margin-top: 15px; font-size: 0.8rem; color: #aaa;">Position the student's entry pass within the frame.</div>
            </div>

            <!-- Participant List -->
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid rgba(188, 19, 254, 0.2);">
                        <th style="padding: 15px; text-align: left; font-family: 'Orbitron'; font-size: 0.8rem; color: #777;">PARTICIPANT</th>
                        <th style="padding: 15px; text-align: center; font-family: 'Orbitron'; font-size: 0.8rem; color: #777;">ATTENDANCE</th>
                        <th style="padding: 15px; text-align: center; font-family: 'Orbitron'; font-size: 0.8rem; color: #777;">SCORE</th>
                        <th style="padding: 15px; text-align: center; font-family: 'Orbitron'; font-size: 0.8rem; color: #777;">STATUS</th>
                        <th style="padding: 15px; text-align: right; font-family: 'Orbitron'; font-size: 0.8rem; color: #777;">SAVE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($participants as $p): ?>
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                            <form action="coordinator_actions.php" method="POST">
                                <input type="hidden" name="reg_id" value="<?= $p['reg_id'] ?>">
                                <input type="hidden" name="event_id" value="<?= $active_event_id ?>">
                                <td style="padding: 15px;">
                                    <span style="color: #fff; font-weight: 700;"><?= htmlspecialchars($p['name']) ?></span><br>
                                    <span style="font-size: 0.7rem; color: var(--neon-blue);"><?= $p['user_id'] ?> | <?= htmlspecialchars($p['college']) ?></span><br>
                                    <span style="font-size: 0.65rem; color: #555;"><?= htmlspecialchars($p['phone']) ?></span>
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    <select name="attendance" style="padding: 5px; font-size: 0.8rem; background: #000; color: <?= $p['attendance'] == 'present' ? 'var(--neon-blue)' : '#777' ?>; border: 1px solid #333;">
                                        <option value="absent" <?= $p['attendance'] == 'absent' ? 'selected' : '' ?>>Absent</option>
                                        <option value="present" <?= $p['attendance'] == 'present' ? 'selected' : '' ?>>Present</option>
                                    </select>
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    <input type="number" name="score" value="<?= $p['score'] ?>" style="width: 60px; padding: 5px; background: rgba(0,0,0,0.3); border: 1px solid #444; color: #fff; text-align: center;">
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    <select name="status" style="padding: 5px; font-size: 0.8rem; background: #000; color: #fff; border: 1px solid #333;">
                                        <option value="registered" <?= $p['status'] == 'registered' ? 'selected' : '' ?>>Registered</option>
                                        <option value="participated" <?= $p['status'] == 'participated' ? 'selected' : '' ?>>Participated</option>
                                        <option value="winner" <?= $p['status'] == 'winner' ? 'selected' : '' ?>>Winner</option>
                                        <option value="runner" <?= $p['status'] == 'runner' ? 'selected' : '' ?>>Runner Up</option>
                                    </select>
                                </td>
                                <td style="padding: 15px; text-align: right;">
                                    <button type="submit" class="btn-neon" style="padding: 6px 12px; font-size: 0.65rem; border-radius: 4px;">UPDATE</button>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(empty($participants)): ?>
                        <tr><td colspan="5" style="padding: 40px; text-align: center; color: #555;">No participants have registered for this event yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Quick Bulk Actions or Overview Info -->
            <div style="margin-top: 50px; padding: 25px; background: rgba(0, 243, 255, 0.02); border: 1px solid rgba(0, 243, 255, 0.1); border-radius: 8px;">
                <h4 class="orbitron" style="font-size: 0.8rem; color: #aaa; margin-bottom: 15px;">Coordinator Dashboard Tips</h4>
                <ul style="color: #666; font-size: 0.8rem; line-height: 1.6;">
                    <li>Mark <b>Attendance</b> as soon as the participant arrives at the venue.</li>
                    <li>Download the <b>CSV list</b> for offline record keeping or manual scoring.</li>
                    <li>Update <b>Scores</b> in real-time to reflect live competition standings.</li>
                    <li>Selecting <b>Winner</b> or <b>Runner Up</b> will automatically push them to the public Leaderboard.</li>
                </ul>
            </div>

        <?php else: ?>
            <div style="text-align: center; padding: 100px;">
                <p style="color: #555; font-size: 1.1rem;">Select an event from the sidebar to manage participants and attendance.</p>
            </div>
        <?php endif; ?>
    </div>
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
