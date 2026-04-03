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
    
    // User Management (Promote/Demote)
    if ($action === 'update_role') {
        $u_id = $_POST['user_id'];
        $new_role = $_POST['role'];
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        $stmt->execute([$new_role, $u_id]);
        $msg = "USER ROLE UPDATED SUCCESSFULLY.";
    }

    // Registrations update (Score/Status)
    elseif ($action === 'update_score') {
        $reg_id = $_POST['reg_id'];
        $score = $_POST['score'];
        $status = $_POST['status'];
        $stmt = $pdo->prepare("UPDATE registrations SET score = ?, status = ? WHERE id = ?");
        $stmt->execute([$score, $status, $reg_id]);
        $msg = "REGISTRATION UPDATED SUCCESSFULLY.";
    } 
    
    // Create Event (Free version)
    elseif ($action === 'create_event') {
        $name = $_POST['name']; $category = $_POST['category']; $description = $_POST['description'];
        $rules = $_POST['rules']; $date = $_POST['date']; $time = $_POST['time']; $venue = $_POST['venue'];
        $coord_name = $_POST['coordinator_name']; $coord_phone = $_POST['coordinator_phone'];
        $coord_id = $_POST['coordinator_id'] ?: null; $max_p = $_POST['max_participants'];

        $stmt = $pdo->prepare("INSERT INTO events (name, category, description, rules, date, time, venue, coordinator_name, coordinator_phone, coordinator_id, max_participants) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $description, $rules, $date, $time, $venue, $coord_name, $coord_phone, $coord_id, $max_p]);
        $msg = "EVENT CREATED SUCCESSFULLY.";
    }

    // Update Event (Free version)
    elseif ($action === 'update_event') {
        $id = $_POST['event_id']; $name = $_POST['name']; $category = $_POST['category']; $description = $_POST['description'];
        $rules = $_POST['rules']; $date = $_POST['date']; $time = $_POST['time']; $venue = $_POST['venue'];
        $coord_name = $_POST['coordinator_name']; $coord_phone = $_POST['coordinator_phone'];
        $coord_id = $_POST['coordinator_id'] ?: null; $max_p = $_POST['max_participants'];

        $stmt = $pdo->prepare("UPDATE events SET name=?, category=?, description=?, rules=?, date=?, time=?, venue=?, coordinator_name=?, coordinator_phone=?, coordinator_id=?, max_participants=? WHERE id=?");
        $stmt->execute([$name, $category, $description, $rules, $date, $time, $venue, $coord_name, $coord_phone, $coord_id, $max_p, $id]);
        $msg = "EVENT UPDATED SUCCESSFULLY.";
    }

    // Delete Event
    elseif ($action === 'delete_event') {
        $id = $_POST['event_id'];
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "EVENT DELETED SUCCESSFULLY.";
    }
}

// Analytics Queries
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$events_count = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$total_regs = $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();

// Participation distribution (For Chart.js)
$participation_data = $pdo->query("SELECT name, current_participants as count FROM events ORDER BY current_participants DESC")->fetchAll();
$category_split = $pdo->query("SELECT category, SUM(current_participants) as count FROM events GROUP BY category")->fetchAll();

// Fetch Events for list
$events = $pdo->query("SELECT * FROM events ORDER BY category, name")->fetchAll();
// Fetch Users
$all_users = $pdo->query("SELECT * FROM users ORDER BY role DESC, created_at DESC")->fetchAll();
$coordinators = $pdo->query("SELECT user_id, name FROM users WHERE role = 'coordinator'")->fetchAll();
// Fetch Latest Registrations
$registrations = $pdo->query("SELECT r.id, u.name as user_name, e.name as event_name, r.score, r.status, r.created_at FROM registrations r JOIN users u ON r.user_id = u.user_id JOIN events e ON r.event_id = e.id ORDER BY r.created_at DESC LIMIT 50")->fetchAll();
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

<!-- Core Stats Row -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-bottom: 50px;">
    <div class="glass neon-border-blue" style="padding: 30px; text-align: center;">
        <h3 class="orbitron" style="font-size: 0.8rem; color: #777;">GLOBAL USERS</h3>
        <p class="orbitron neon-text-blue" style="font-size: 2.5rem;"><?= $total_users ?></p>
    </div>
    <div class="glass neon-border-blue" style="padding: 30px; text-align: center; border-color: var(--neon-purple);">
        <h3 class="orbitron" style="font-size: 0.8rem; color: #777;">EVENT TRACKS</h3>
        <p class="orbitron neon-text-purple" style="font-size: 2.5rem;"><?= $events_count ?></p>
    </div>
    <div class="glass neon-border-blue" style="padding: 30px; text-align: center; border-color: var(--neon-pink);">
        <h3 class="orbitron" style="font-size: 0.8rem; color: #777;">TOTAL REGISTRATIONS</h3>
        <p class="orbitron neon-text-pink" style="font-size: 2.5rem;"><?= $total_regs ?></p>
    </div>
</div>

<div style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 30px;">
    <a href="admin.php" class="btn-neon" style="font-size: 0.7rem;">Participation Dashboard</a>
    <a href="#manage-events" class="btn-neon" style="font-size: 0.7rem; border-color: var(--neon-purple); color: var(--neon-purple);">Manage Competition Database</a>
    <a href="#user-management" class="btn-neon" style="font-size: 0.7rem; border-color: var(--neon-blue); color: var(--neon-blue);">Portal User Management</a>
</div>

<!-- ANALYTICS DASHBOARD -->
<div class="glass neon-border-blue" style="padding: 30px; margin-bottom: 50px; border-color: var(--neon-blue);">
    <h2 class="orbitron neon-text-blue" style="font-size: 1.2rem; margin-bottom: 30px; letter-spacing: 2px;">Participation Distribution</h2>
    
    <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 40px;">
        <div class="glass" style="padding: 20px; background: rgba(0, 243, 255, 0.02);">
            <h4 class="orbitron" style="font-size: 0.8rem; color: #777; margin-bottom: 20px;">Top Competing Events (Registrations)</h4>
            <canvas id="participationChart" height="250"></canvas>
        </div>
        <div class="glass" style="padding: 20px; background: rgba(188, 19, 254, 0.02);">
            <h4 class="orbitron" style="font-size: 0.8rem; color: #777; margin-bottom: 20px;">Track Distribution (IT vs Commerce)</h4>
            <canvas id="categoryChart" height="250"></canvas>
        </div>
    </div>
</div>

<!-- EVENT MANAGEMENT SECTION -->
<div id="manage-events" class="glass neon-border-blue" style="padding: 30px; margin-bottom: 80px; border-color: var(--neon-purple);">
    <h2 class="orbitron neon-text-purple" style="font-size: 1.5rem; margin-bottom: 40px; text-align: center;">Competition Track Database</h2>
    
    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 40px;">
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
                            <span style="font-size: 0.7rem; color: var(--neon-purple);"><?= $ev['category'] ?> Track</span>
                        </td>
                        <td style="padding: 15px;">
                            <span style="font-size: 0.85rem; color: #aaa;"><?= htmlspecialchars($ev['coordinator_name'] ?: 'System Assigned') ?></span><br>
                            <span style="font-size: 0.6rem; color: #666;"><?= htmlspecialchars($ev['coordinator_id'] ?: '-') ?></span>
                        </td>
                        <td style="padding: 15px; text-align: center;">
                            <div style="display: flex; gap: 10px; justify-content: center;">
                                <a href="admin.php?view_participants=<?= $ev['id'] ?>#manage-events" class="btn-neon" style="padding: 5px 10px; font-size: 0.6rem; border-color: var(--neon-blue); color: var(--neon-blue);">STUDENTS</a>
                                <button onclick='fillEditForm(<?= json_encode($ev) ?>)' class="btn-neon" style="padding: 5px 10px; font-size: 0.6rem; border-color: #fff; color: #fff;">EDIT</button>
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

        <div class="glass" style="padding: 30px; border: 1px solid rgba(188, 19, 254, 0.3);">
            <h3 id="form-title" class="orbitron neon-text-blue" style="font-size: 1rem; margin-bottom: 25px;">Track Modeler</h3>
            <form id="event-form" method="POST">
                <input type="hidden" name="action" id="form-action" value="create_event">
                <input type="hidden" name="event_id" id="form-event-id">
                
                <div class="form-group"><label>Event Name</label><input type="text" name="name" id="field-name" required></div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group"><label>Track Category</label><select name="category" id="field-category"><option value="IT">IT Track (BCA)</option><option value="Commerce">Commerce (BCom)</option></select></div>
                    <div class="form-group"><label>Max Capacity</label><input type="number" name="max_participants" id="field-max-p" value="50"></div>
                </div>
                <div class="form-group"><label>Portal Assignment (Coord.)</label>
                    <select name="coordinator_id" id="field-coord-id">
                        <option value="">-- No Assignment --</option>
                        <?php foreach($coordinators as $c): ?>
                            <option value="<?= $c['user_id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group"><label>Display Coord. Name</label><input type="text" name="coordinator_name" id="field-coord-n"></div>
                    <div class="form-group"><label>Display Coord. Phone</label><input type="text" name="coordinator_phone" id="field-coord-p"></div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group"><label>Operation Date</label><input type="date" name="date" id="field-date"></div>
                    <div class="form-group"><label>Operation Time</label><input type="time" name="time" id="field-time"></div>
                </div>
                <div class="form-group"><label>Strategic Venue</label><input type="text" name="venue" id="field-venue"></div>
                <div class="form-group"><label>Rules & Desc</label><textarea name="rules" id="field-rules" style="height: 100px;"></textarea></div>
                <input type="hidden" name="description" id="field-desc">
                
                <button type="submit" id="submit-btn" class="btn-neon" style="width: 100%;">DEPLOY EVENT TRACK</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const barLabels = <?= json_encode(array_column($participation_data, 'name')) ?>;
    const barValues = <?= json_encode(array_column($participation_data, 'count')) ?>;
    new Chart(document.getElementById('participationChart'), {
        type: 'bar',
        data: {
            labels: barLabels,
            datasets: [{
                label: 'Participants Registered',
                data: barValues,
                backgroundColor: 'rgba(0, 243, 255, 0.4)',
                borderColor: '#00f3ff',
                borderWidth: 2
            }]
        },
        options: {
            scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#aaa' } }, x: { ticks: { color: '#aaa' } } },
            plugins: { legend: { labels: { color: '#aaa', font: { family: 'Orbitron' } } } }
        }
    });

    const pieLabels = <?= json_encode(array_column($category_split, 'category')) ?>;
    const pieValues = <?= json_encode(array_column($category_split, 'count')) ?>;
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: pieLabels,
            datasets: [{
                data: pieValues,
                backgroundColor: ['#bc13fe', '#00f3ff'],
                borderColor: ['rgba(188, 19, 254, 0.5)', 'rgba(0, 243, 255, 0.5)'],
                borderWidth: 5
            }]
        },
        options: {
            plugins: { legend: { labels: { color: '#aaa', font: { family: 'Orbitron' } } } }
        }
    });

    function fillEditForm(event) {
        document.getElementById('form-title').innerText = 'Reconfigure Event: ' + event.name;
        document.getElementById('form-action').value = 'update_event';
        document.getElementById('form-event-id').value = event.id;
        document.getElementById('field-name').value = event.name;
        document.getElementById('field-category').value = event.category;
        document.getElementById('field-max-p').value = event.max_participants;
        document.getElementById('field-date').value = event.date;
        document.getElementById('field-time').value = event.time;
        document.getElementById('field-venue').value = event.venue;
        document.getElementById('field-coord-n').value = event.coordinator_name;
        document.getElementById('field-coord-p').value = event.coordinator_phone;
        document.getElementById('field-coord-id').value = event.coordinator_id || '';
        document.getElementById('submit-btn').innerText = 'UPDATE TRACK';
        document.getElementById('manage-events').scrollIntoView({ behavior: 'smooth' });
    }
</script>

<?php include 'includes/footer.php'; ?>
