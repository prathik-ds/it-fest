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
$stmt = $pdo->prepare("
    SELECT e.*, r.status as reg_status, r.score, r.team_id,
           t.name as team_name, t.invite_code as team_code, t.leader_user_id as team_leader
    FROM events e 
    JOIN registrations r ON e.id = r.event_id 
    LEFT JOIN teams t ON r.team_id = t.id
    WHERE r.user_id = ? ORDER BY e.date
");
$stmt->execute([$user_id]);
$myEvents = $stmt->fetchAll();

// Fetch public announcements
$stmt = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5");
$announcements = $stmt->fetchAll();
?>

<div style="padding: 40px;">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="header-content">
            <h1>
                <span style="font-size: 1.5rem;">👋</span>
                Welcome, <?= htmlspecialchars($userinfo['name']) ?>
            </h1>
            <p>Track your registrations, scores, and access your digital entry passes.</p>
        </div>
        <div class="header-actions">
            <span class="status-tag" style="background: rgba(124, 58, 237, 0.08); color: var(--accent-2); padding: 8px 18px; font-size: 0.78rem; border: 1px solid rgba(124, 58, 237, 0.2); border-radius: 10px;">
                <i class="fa-solid fa-id-badge" style="margin-right: 4px;"></i>
                <?= $user_id ?>
            </span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px; margin-top: 30px;">
        <!-- Registrations Table -->
        <div class="glass-panel" style="padding: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h2 style="font-family: 'Space Grotesk', sans-serif; font-size: 1.4rem; font-weight: 700;">My Registrations</h2>
                <a href="events.php" class="btn-coord" style="text-decoration: none;">
                    <i class="fa-solid fa-compass"></i> Browse Events
                </a>
            </div>

            <?php if(empty($myEvents)): ?>
                <div style="text-align: center; padding: 60px 0;">
                    <div style="width: 72px; height: 72px; background: rgba(0, 212, 255, 0.06); border-radius: 20px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                        <i class="fa-solid fa-calendar-plus" style="font-size: 1.8rem; color: var(--accent-1);"></i>
                    </div>
                    <p style="color: var(--text-secondary); font-size: 1.05rem; margin-bottom: 8px;">No events registered yet</p>
                    <p style="color: var(--text-dim); font-size: 0.82rem; margin-bottom: 24px;">Explore competitions and sign up to compete!</p>
                    <a href="events.php" class="btn-neon" style="padding: 12px 32px; text-decoration: none; font-size: 0.82rem;">
                        <i class="fa-solid fa-rocket"></i> Explore Events
                    </a>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <th style="padding: 16px; text-align: left; color: var(--text-dim); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 1.5px; font-family: 'Space Grotesk', sans-serif;">Event</th>
                                <th style="padding: 16px; text-align: left; color: var(--text-dim); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 1.5px; font-family: 'Space Grotesk', sans-serif;">Schedule</th>
                                <th style="padding: 16px; text-align: left; color: var(--text-dim); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 1.5px; font-family: 'Space Grotesk', sans-serif;">Team</th>
                                <th style="padding: 16px; text-align: center; color: var(--text-dim); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 1.5px; font-family: 'Space Grotesk', sans-serif;">Status</th>
                                <th style="padding: 16px; text-align: right; color: var(--text-dim); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 1.5px; font-family: 'Space Grotesk', sans-serif;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($myEvents as $ev): ?>
                                <tr style="border-bottom: 1px solid var(--border); transition: background 0.2s;" onmouseover="this.style.background='rgba(0, 212, 255, 0.02)'" onmouseout="this.style.background='transparent'">
                                    <td style="padding: 16px;" data-label="Event">
                                        <div style="font-weight: 700; color: var(--text-primary); font-size: 0.95rem;"><?= htmlspecialchars($ev['name']) ?></div>
                                        <div style="font-size: 0.72rem; color: var(--text-dim); display: flex; align-items: center; gap: 6px; margin-top: 3px;">
                                            <span style="width: 6px; height: 6px; border-radius: 50%; background: <?= $ev['category'] == 'IT' ? 'var(--accent-1)' : 'var(--accent-2)' ?>;"></span>
                                            <?= $ev['category'] ?>
                                        </div>
                                    </td>
                                    <td style="padding: 16px;" data-label="Schedule">
                                        <div style="font-size: 0.9rem; color: var(--text-primary);"><?= date('d M, Y', strtotime($ev['date'])) ?></div>
                                        <div style="font-size: 0.72rem; color: var(--text-dim);"><?= date('h:i A', strtotime($ev['time'])) ?></div>
                                    </td>
                                    <!-- Team info -->
                                    <td style="padding: 16px;" data-label="Team">
                                        <?php if (!empty($ev['team_id'])): ?>
                                            <div style="font-size:0.8rem; font-weight:700; color:#c4b5fd;"><?= htmlspecialchars($ev['team_name']) ?></div>
                                            <div style="font-size:0.65rem; color:#5b6a8a; font-family:'JetBrains Mono',monospace; letter-spacing:2px; margin-top:2px;">
                                                <?= htmlspecialchars($ev['team_code']) ?>
                                                <?php if($ev['team_leader'] === $user_id): ?>
                                                    &nbsp;<span style="color:#fbbf24; font-size:0.6rem;">LEADER</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color:#5b6a8a; font-size:0.75rem;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 16px; text-align: center;" data-label="Status">
                                        <?php
                                        $statusColor = 'var(--success)';
                                        $statusBg = 'rgba(16, 185, 129, 0.08)';
                                        $statusBorder = 'rgba(16, 185, 129, 0.2)';
                                        if ($ev['reg_status'] == 'winner') {
                                            $statusColor = 'var(--accent-5)';
                                            $statusBg = 'rgba(251, 191, 36, 0.08)';
                                            $statusBorder = 'rgba(251, 191, 36, 0.2)';
                                        } elseif ($ev['reg_status'] == 'attended') {
                                            $statusColor = 'var(--accent-1)';
                                            $statusBg = 'rgba(0, 212, 255, 0.08)';
                                            $statusBorder = 'rgba(0, 212, 255, 0.2)';
                                        }
                                        ?>
                                        <span style="padding: 5px 12px; border-radius: 8px; font-size: 0.62rem; font-weight: 800; background: <?= $statusBg ?>; color: <?= $statusColor ?>; text-transform: uppercase; letter-spacing: 1px; border: 1px solid <?= $statusBorder ?>;">
                                            <?= strtoupper($ev['reg_status']) ?>
                                        </span>
                                    </td>
                                    <td style="padding: 16px; text-align: right; display: flex; gap: 8px; justify-content: flex-end;" data-label="Actions">
                                        <button onclick="showTicket('<?= $ev['id'] ?>', '<?= htmlspecialchars(addslashes($ev['name'])) ?>')" class="btn-coord" style="padding: 6px 14px; font-size: 0.68rem; background: rgba(0, 212, 255, 0.06); color: var(--accent-1); border: 1px solid rgba(0, 212, 255, 0.2);">
                                            <i class="fa-solid fa-qrcode"></i> Ticket
                                        </button>
                                        
                                        <?php if($ev['reg_status'] !== 'registered'): ?>
                                            <a href="certificate.php?event_id=<?= $ev['id'] ?>" target="_blank" class="btn-coord" style="padding: 6px 14px; font-size: 0.68rem; background: rgba(251, 191, 36, 0.06); color: var(--accent-5); border: 1px solid rgba(251, 191, 36, 0.2); text-decoration: none;">
                                                <i class="fa-solid fa-award"></i> Cert
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
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- QR Entry Info -->
            <div class="glass-panel" style="padding: 28px; text-align: center; border-color: rgba(0, 212, 255, 0.12); position: relative; overflow: hidden;">
                <div style="position: absolute; top: -30px; right: -30px; width: 100px; height: 100px; background: radial-gradient(circle, rgba(0, 212, 255, 0.08), transparent 70%); border-radius: 50%;"></div>
                <div style="width: 56px; height: 56px; background: rgba(0, 212, 255, 0.06); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                    <i class="fa-solid fa-ticket" style="font-size: 1.5rem; color: var(--accent-1);"></i>
                </div>
                <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 1.05rem; margin-bottom: 12px; color: var(--text-primary); font-weight: 700;">Event Entry System</h3>
                <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.7;">Present the QR ticket for each specific event to the coordinator for attendance verification.</p>
            </div>

            <!-- Profile Card -->
            <div class="glass-panel" style="padding: 28px;">
                <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 1.05rem; margin-bottom: 22px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-user-circle" style="color: var(--accent-2);"></i>
                    Profile
                </h3>
                <div style="display: flex; flex-direction: column; gap: 18px;">
                    <div style="padding: 12px 14px; background: rgba(0, 0, 0, 0.15); border-radius: 12px; border: 1px solid var(--border);">
                        <div style="font-size: 0.65rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 4px;">College</div>
                        <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($userinfo['college']) ?></div>
                    </div>
                    <div style="padding: 12px 14px; background: rgba(0, 0, 0, 0.15); border-radius: 12px; border: 1px solid var(--border);">
                        <div style="font-size: 0.65rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 4px;">Course</div>
                        <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($userinfo['course']) ?></div>
                    </div>
                    <div style="padding: 12px 14px; background: rgba(0, 0, 0, 0.15); border-radius: 12px; border: 1px solid var(--border);">
                        <div style="font-size: 0.65rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 4px;">Email</div>
                        <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-primary); word-break: break-all;"><?= htmlspecialchars($userinfo['email']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Modal -->
<div id="qrModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(4, 6, 14, 0.85); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
    <div class="glass-panel" style="background: rgba(15, 22, 41, 0.97); padding: 40px; text-align: center; border-radius: 24px; border: 1px solid rgba(124, 58, 237, 0.2); max-width: 370px; width: 90%; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -40px; left: -40px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(124, 58, 237, 0.08), transparent 70%); border-radius: 50%;"></div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h3 id="modal-event-name" style="font-family: 'Space Grotesk', sans-serif; font-size: 1.15rem; color: var(--accent-2); margin: 0; font-weight: 700;">Event Ticket</h3>
            <button onclick="closeModal()" style="background: rgba(244, 63, 94, 0.08); border: 1px solid rgba(244, 63, 94, 0.15); color: var(--danger); cursor: pointer; font-size: 1rem; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <div id="qrcode" style="background: white; padding: 18px; display: inline-block; border-radius: 16px; margin-bottom: 20px; min-width: 200px; min-height: 200px; box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);"></div>
        
        <div style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.6;">
            Present this QR code to the event coordinator
        </div>
        <div style="margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 0.72rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 2px; padding: 8px 16px; background: rgba(0, 0, 0, 0.2); border-radius: 8px; display: inline-block;">
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
        qrContainer.innerHTML = '';
        
        const qrData = "EVENT_QR|<?= $user_id ?>|" + eventId;
        
        qrObj = new QRCode(qrContainer, {
            text: qrData,
            width: 200,
            height: 200,
            colorDark : "#0f1629",
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

