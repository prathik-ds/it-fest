<?php 
include 'includes/header.php'; 

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$msg = '';
$error = '';

// Logic for updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Manage Registrations (Score/Status)
    if ($action === 'update_score') {
        $reg_id = $_POST['reg_id'];
        $score = $_POST['score'];
        $status = $_POST['status'];
        $stmt = $pdo->prepare("UPDATE registrations SET score = ?, status = ? WHERE id = ?");
        $stmt->execute([$score, $status, $reg_id]);
        $msg = "REGISTRATION UPDATED SUCCESSFULLY.";
    } 
    
    // Announcements
    elseif ($action === 'add_announcement') {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content) VALUES (?, ?)");
        $stmt->execute([$title, $content]);
        $msg = "ANNOUNCEMENT POSTED SUCCESSFULLY.";
    }

    // CREATE EVENT
    elseif ($action === 'create_event') {
        $name = $_POST['name'];
        $category = $_POST['category'];
        $description = $_POST['description'];
        $rules = $_POST['rules'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $venue = $_POST['venue'];
        $coord_name = $_POST['coordinator_name'];
        $coord_phone = $_POST['coordinator_phone'];
        $max_p = $_POST['max_participants'];

        $stmt = $pdo->prepare("INSERT INTO events (name, category, description, rules, date, time, venue, coordinator_name, coordinator_phone, max_participants) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $description, $rules, $date, $time, $venue, $coord_name, $coord_phone, $max_p]);
        $msg = "EVENT CREATED SUCCESSFULLY.";
    }

    // UPDATE EVENT
    elseif ($action === 'update_event') {
        $id = $_POST['event_id'];
        $name = $_POST['name'];
        $category = $_POST['category'];
        $description = $_POST['description'];
        $rules = $_POST['rules'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $venue = $_POST['venue'];
        $coord_name = $_POST['coordinator_name'];
        $coord_phone = $_POST['coordinator_phone'];
        $max_p = $_POST['max_participants'];

        $stmt = $pdo->prepare("UPDATE events SET name=?, category=?, description=?, rules=?, date=?, time=?, venue=?, coordinator_name=?, coordinator_phone=?, max_participants=? WHERE id=?");
        $stmt->execute([$name, $category, $description, $rules, $date, $time, $venue, $coord_name, $coord_phone, $max_p, $id]);
        $msg = "EVENT UPDATED SUCCESSFULLY.";
    }

    // DELETE EVENT
    elseif ($action === 'delete_event') {
        $id = $_POST['event_id'];
        // Note: registrations have ON DELETE CASCADE so this will clean up automatically if foreign keys are set
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "EVENT DELETED SUCCESSFULLY.";
    }
}

// Fetch Stats
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_regs = $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
$events_count = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();

// Fetch Events
$events = $pdo->query("SELECT * FROM events ORDER BY category, name")->fetchAll();

// Fetch Registrations
$registrations = $pdo->query("SELECT r.id, u.name as user_name, e.name as event_name, r.score, r.status, r.created_at FROM registrations r JOIN users u ON r.user_id = u.user_id JOIN events e ON r.event_id = e.id ORDER BY r.created_at DESC")->fetchAll();

// Participant Details logic
$view_event_id = $_GET['view_participants'] ?? null;
$participants = [];
$view_event_name = '';
if ($view_event_id) {
    $stmt = $pdo->prepare("SELECT u.*, r.score, r.status as reg_status FROM users u JOIN registrations r ON u.user_id = r.user_id WHERE r.event_id = ?");
    $stmt->execute([$view_event_id]);
    $participants = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("SELECT name FROM events WHERE id = ?");
    $stmt->execute([$view_event_id]);
    $view_event_name = $stmt->fetchColumn();
}
?>

<div style="margin-bottom: 50px;">
    <h1 class="orbitron neon-text-pink" style="font-size: 2.5rem; text-align: center;">COMMAND CENTER</h1>
    <p style="color: #666; text-align: center; font-size: 0.9rem; letter-spacing: 3px;">SYNERGY ADMINISTRATOR PORTAL</p>
</div>

<?php if($msg): ?>
    <div style="padding: 15px; border: 1px solid var(--neon-blue); background: rgba(0, 243, 255, 0.1); color: #fff; margin-bottom: 30px; border-radius: 8px;">
        ✅ <?= $msg ?>
    </div>
<?php endif; ?>

<!-- Stats Row -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-bottom: 50px;">
    <div class="glass neon-border-blue" style="padding: 30px; text-align: center;">
        <h3 class="orbitron" style="font-size: 0.8rem; color: #777;">TOTAL USERS</h3>
        <p class="orbitron neon-text-blue" style="font-size: 2.5rem;"><?= $total_users ?></p>
    </div>
    <div class="glass neon-border-blue" style="padding: 30px; text-align: center; border-color: var(--neon-purple);">
        <h3 class="orbitron" style="font-size: 0.8rem; color: #777;">REGISTRATIONS</h3>
        <p class="orbitron neon-text-purple" style="font-size: 2.5rem;"><?= $total_regs ?></p>
    </div>
    <div class="glass neon-border-blue" style="padding: 30px; text-align: center; border-color: var(--neon-pink);">
        <h3 class="orbitron" style="font-size: 0.8rem; color: #777;">ACTIVE EVENTS</h3>
        <p class="orbitron neon-text-pink" style="font-size: 2.5rem;"><?= $events_count ?></p>
    </div>
</div>

<div style="display: flex; gap: 20px; margin-bottom: 30px;">
    <a href="admin.php" class="btn-neon" style="font-size: 0.7rem;">Overview</a>
    <a href="#manage-events" class="btn-neon" style="font-size: 0.7rem; border-color: var(--neon-purple); color: var(--neon-purple);">Manage Events</a>
    <a href="#announcements" class="btn-neon" style="font-size: 0.7rem; border-color: var(--neon-pink); color: var(--neon-pink);">Announcements</a>
</div>

<!-- Participant Details Modal-like Section -->
<?php if($view_event_id): ?>
<div class="glass neon-border-blue" style="padding: 30px; margin-bottom: 50px; border-color: var(--neon-purple);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 class="orbitron neon-text-purple" style="font-size: 1.5rem;">Participants: <?= htmlspecialchars($view_event_name) ?></h2>
        <a href="admin.php#manage-events" class="btn-neon" style="font-size: 0.7rem;">CLOSE</a>
    </div>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid rgba(188, 19, 254, 0.2);">
                <th style="padding: 15px; text-align: left; font-family: 'Orbitron'; font-size: 0.8rem; color: #777;">USER ID</th>
                <th style="padding: 15px; text-align: left; font-family: 'Orbitron'; font-size: 0.8rem; color: #777;">NAME</th>
                <th style="padding: 15px; text-align: left; font-family: 'Orbitron'; font-size: 0.8rem; color: #777;">COLLEGE</th>
                <th style="padding: 15px; text-align: center; font-family: 'Orbitron'; font-size: 0.8rem; color: #777;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($participants as $p): ?>
            <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                <td style="padding: 15px; color: var(--neon-blue);"><?= $p['user_id'] ?></td>
                <td style="padding: 15px; color: #fff;"><?= htmlspecialchars($p['name']) ?></td>
                <td style="padding: 15px; color: #aaa;"><?= htmlspecialchars($p['college']) ?></td>
                <td style="padding: 15px; text-align: center;">
                    <span style="font-size: 0.7rem; padding: 3px 8px; border: 1px solid currentColor; color: <?= $p['reg_status'] == 'winner' ? 'var(--neon-blue)' : '#777' ?>;">
                        <?= strtoupper($p['reg_status']) ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($participants)): ?>
                <tr><td colspan="4" style="padding: 30px; text-align: center; color: #555;">No participants yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<div id="overview" style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px; margin-bottom: 80px;">
    <!-- Registration Manager -->
    <div class="glass neon-border-blue" style="padding: 30px; overflow-x: auto;">
        <h2 class="orbitron neon-text-blue" style="font-size: 1.2rem; margin-bottom: 30px;">Registration Updates</h2>
        <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
            <thead>
                <tr style="border-bottom: 2px solid rgba(0, 243, 255, 0.1);">
                    <th style="padding: 15px; font-size: 0.8rem; color: #777; font-family: 'Orbitron'; text-align: left;">USER / EVENT</th>
                    <th style="padding: 15px; font-size: 0.8rem; color: #777; font-family: 'Orbitron';">SCORE</th>
                    <th style="padding: 15px; font-size: 0.8rem; color: #777; font-family: 'Orbitron';">STATUS</th>
                    <th style="padding: 15px; font-size: 0.8rem; color: #777; font-family: 'Orbitron';">ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($registrations as $reg): ?>
                    <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                        <td style="padding: 15px;">
                            <span style="color: #fff; font-weight: 700;"><?= htmlspecialchars($reg['user_name']) ?></span><br>
                            <span style="font-size: 0.7rem; color: var(--neon-blue); letter-spacing: 1px;"><?= htmlspecialchars($reg['event_name']) ?></span>
                        </td>
                        <form method="POST">
                            <input type="hidden" name="action" value="update_score">
                            <input type="hidden" name="reg_id" value="<?= $reg['id'] ?>">
                            <td style="padding: 15px;">
                                <input type="number" name="score" value="<?= $reg['score'] ?>" style="width: 70px; padding: 5px; background: rgba(0,0,0,0.3); border: 1px solid #444; border-radius: 4px;">
                            </td>
                            <td style="padding: 15px;">
                                <select name="status" style="padding: 5px; font-size: 0.8rem; background: rgba(0,0,0,0.3); color: #fff; border: 1px solid #444; border-radius: 4px;">
                                    <option value="registered" <?= $reg['status'] == 'registered' ? 'selected' : '' ?>>Registered</option>
                                    <option value="participated" <?= $reg['status'] == 'participated' ? 'selected' : '' ?>>Participated</option>
                                    <option value="winner" <?= $reg['status'] == 'winner' ? 'selected' : '' ?>>Winner</option>
                                    <option value="runner" <?= $reg['status'] == 'runner' ? 'selected' : '' ?>>Runner Up</option>
                                </select>
                            </td>
                            <td style="padding: 15px;">
                                <button type="submit" class="btn-neon" style="padding: 5px 15px; font-size: 0.7rem; border-width: 1px;">UPDATE</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Announcement Tool -->
    <div id="announcements" class="glass neon-border-blue" style="padding: 30px; height: fit-content;">
        <h2 class="orbitron neon-text-purple" style="font-size: 1.2rem; margin-bottom: 25px;">Post Notice</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add_announcement">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" placeholder="Event Update / Result Out" required>
            </div>
            <div class="form-group">
                <label>Content</label>
                <textarea name="content" placeholder="Type announcement details here..." style="height: 150px;" required></textarea>
            </div>
            <button type="submit" class="btn-neon" style="width: 100%; border-color: var(--neon-purple); color: var(--neon-purple);">POST NOTICE</button>
        </form>
    </div>
</div>

<!-- Event Management Section -->
<div id="manage-events" class="glass neon-border-blue" style="padding: 30px; margin-bottom: 80px; border-color: var(--neon-purple);">
    <h2 class="orbitron neon-text-purple" style="font-size: 1.5rem; margin-bottom: 40px; text-align: center;">Event Management</h2>
    
    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 40px;">
        <!-- Event List -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid rgba(188, 19, 254, 0.2);">
                        <th style="padding: 15px; text-align: left; font-family: 'Orbitron'; font-size: 0.8rem; color: #777;">EVENT</th>
                        <th style="padding: 15px; text-align: left; font-family: 'Orbitron'; font-size: 0.8rem; color: #777;">COORDINATOR</th>
                        <th style="padding: 15px; text-align: center; font-family: 'Orbitron'; font-size: 0.8rem; color: #777;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($events as $ev): ?>
                    <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                        <td style="padding: 15px;">
                            <span style="color: #fff; font-weight: 700;"><?= htmlspecialchars($ev['name']) ?></span><br>
                            <span style="font-size: 0.7rem; color: var(--neon-purple);"><?= $ev['category'] ?></span>
                        </td>
                        <td style="padding: 15px;">
                            <span style="font-size: 0.85rem; color: #aaa;"><?= htmlspecialchars($ev['coordinator_name'] ?: 'None') ?></span><br>
                            <span style="font-size: 0.7rem; color: #666;"><?= htmlspecialchars($ev['coordinator_phone'] ?: '-') ?></span>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <div style="display: flex; gap: 10px; justify-content: center;">
                                <a href="admin.php?view_participants=<?= $ev['id'] ?>#manage-events" class="btn-neon" style="padding: 5px 10px; font-size: 0.6rem; border-color: var(--neon-blue); color: var(--neon-blue);">STUDENTS</a>
                                <button onclick='fillEditForm(<?= json_encode($ev) ?>)' class="btn-neon" style="padding: 5px 10px; font-size: 0.6rem;">EDIT</button>
                                <form method="POST" onsubmit="return confirm('Delete this event?');">
                                    <input type="hidden" name="action" value="delete_event">
                                    <input type="hidden" name="event_id" value="<?= $ev['id'] ?>">
                                    <button type="submit" class="btn-neon" style="padding: 5px 10px; font-size: 0.6rem; border-color: var(--neon-pink); color: var(--neon-pink);">DEL</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Add/Edit Event Form -->
        <div class="glass" style="padding: 30px; border: 1px solid rgba(188, 19, 254, 0.3);">
            <h3 id="form-title" class="orbitron neon-text-blue" style="font-size: 1rem; margin-bottom: 25px;">Create New Event</h3>
            <form id="event-form" method="POST">
                <input type="hidden" name="action" id="form-action" value="create_event">
                <input type="hidden" name="event_id" id="form-event-id">
                
                <div class="form-group">
                    <label>Event Name</label>
                    <input type="text" name="name" id="field-name" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" id="field-category">
                            <option value="IT">IT (BCA)</option>
                            <option value="Commerce">Commerce (BCom)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Max Slots</label>
                        <input type="number" name="max_participants" id="field-max-p" value="50">
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="field-desc" style="height: 60px;"></textarea>
                </div>
                <div class="form-group">
                    <label>Rules</label>
                    <textarea name="rules" id="field-rules" style="height: 60px;"></textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="date" id="field-date">
                    </div>
                    <div class="form-group">
                        <label>Time</label>
                        <input type="time" name="time" id="field-time">
                    </div>
                </div>
                <div class="form-group">
                    <label>Venue</label>
                    <input type="text" name="venue" id="field-venue">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Coordinator Name</label>
                        <input type="text" name="coordinator_name" id="field-coord-n">
                    </div>
                    <div class="form-group">
                        <label>Coordinator Phone</label>
                        <input type="text" name="coordinator_phone" id="field-coord-p">
                    </div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" id="submit-btn" class="btn-neon" style="flex: 2;">SAVE EVENT</button>
                    <button type="button" onclick="resetForm()" class="btn-neon" style="flex: 1; border-color: #555; color: #555;">CANCEL</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function fillEditForm(event) {
    document.getElementById('form-title').innerText = 'Edit Event: ' + event.name;
    document.getElementById('form-action').value = 'update_event';
    document.getElementById('form-event-id').value = event.id;
    document.getElementById('field-name').value = event.name;
    document.getElementById('field-category').value = event.category;
    document.getElementById('field-max-p').value = event.max_participants;
    document.getElementById('field-desc').value = event.description;
    document.getElementById('field-rules').value = event.rules;
    document.getElementById('field-date').value = event.date;
    document.getElementById('field-time').value = event.time;
    document.getElementById('field-venue').value = event.venue;
    document.getElementById('field-coord-n').value = event.coordinator_name;
    document.getElementById('field-coord-p').value = event.coordinator_phone;
    document.getElementById('submit-btn').innerText = 'UPDATE EVENT';
    
    // Smooth scroll to form
    document.getElementById('event-form').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('form-title').innerText = 'Create New Event';
    document.getElementById('form-action').value = 'create_event';
    document.getElementById('form-event-id').value = '';
    document.getElementById('event-form').reset();
    document.getElementById('submit-btn').innerText = 'SAVE EVENT';
}
</script>

<?php include 'includes/footer.php'; ?>
