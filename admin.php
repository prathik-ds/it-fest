<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}
include 'includes/header.php';

$msg = '';
$error = '';

// Handle POST actions for each function
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. User Management Functions
    if ($action === 'create_user') {
        $u_id = $_POST['user_id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $college = $_POST['college'] ?? 'N/A';
        $role = $_POST['role'];
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (user_id, name, email, phone, college, course, password, role) VALUES (?, ?, ?, ?, ?, 'General', ?, ?)");
            $stmt->execute([$u_id, $name, $email, $phone, $college, $pass, $role]);
            $msg = "USER CREATED SUCCESSFULLY.";
        } catch (Exception $e) {
            $error = "FAILED TO CREATE USER: ID/EMAIL ALREADY EXISTS.";
        }
    } elseif ($action === 'update_user_role') {
        $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?")->execute([$_POST['role'], $_POST['user_id']]);
        $msg = "USER ROLE UPDATED.";
    } elseif ($action === 'delete_user') {
        $pdo->prepare("DELETE FROM users WHERE user_id = ?")->execute([$_POST['user_id']]);
        $msg = "USER REMOVED FROM SYSTEM.";
    }

    // 2. Event Management Functions
    elseif ($action === 'create_event') {
        $name = $_POST['name'];
        $category = $_POST['category'];
        $description = $_POST['description'] ?? '';
        $rules = $_POST['rules'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $venue = $_POST['venue'];
        $max_p = $_POST['max_participants'];
        $coord_id = $_POST['coordinator_id'] ?: null;

        $image_path = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], 'assets/img/events/' . $filename)) {
                $image_path = 'assets/img/events/' . $filename;
            }
        }

        // Fetch coord details if assigned
        $c_name = '';
        $c_phone = '';
        if ($coord_id) {
            $cst = $pdo->prepare("SELECT name, phone FROM users WHERE user_id = ?");
            $cst->execute([$coord_id]);
            $c_data = $cst->fetch();
            $c_name = $c_data['name'];
            $c_phone = $c_data['phone'];
        }

        $stmt = $pdo->prepare("INSERT INTO events (name, category, description, rules, date, time, venue, coordinator_name, coordinator_phone, coordinator_id, max_participants, image, is_team_event, min_team_size, max_team_size) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $is_team = isset($_POST['is_team_event']) ? 1 : 0;
        $min_ts  = (int)($_POST['min_team_size'] ?? 2);
        $max_ts  = (int)($_POST['max_team_size'] ?? 4);
        $stmt->execute([$name, $category, $description, $rules, $date, $time, $venue, $c_name, $c_phone, $coord_id, $max_p, $image_path, $is_team, $min_ts, $max_ts]);
        $msg = "EVENT TRACK DEPLOYED.";
    } elseif ($action === 'update_event') {
        $id = $_POST['event_id'];
        $description = $_POST['description'] ?? '';
        $coord_id = $_POST['coordinator_id'] ?: null;
        $c_name = '';
        $c_phone = '';
        if ($coord_id) {
            $cst = $pdo->prepare("SELECT name, phone FROM users WHERE user_id = ?");
            $cst->execute([$coord_id]);
            $c_data = $cst->fetch();
            $c_name = $c_data['name'];
            $c_phone = $c_data['phone'];
        }

        $image_path = $_POST['existing_logo'] ?? null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], 'assets/img/events/' . $filename)) {
                $image_path = 'assets/img/events/' . $filename;
            }
        }

        $stmt = $pdo->prepare("UPDATE events SET name=?, category=?, description=?, rules=?, date=?, time=?, venue=?, coordinator_name=?, coordinator_phone=?, coordinator_id=?, max_participants=?, image=?, is_team_event=?, min_team_size=?, max_team_size=? WHERE id=?");
        $is_team = isset($_POST['is_team_event']) ? 1 : 0;
        $min_ts  = (int)($_POST['min_team_size'] ?? 2);
        $max_ts  = (int)($_POST['max_team_size'] ?? 4);
        $stmt->execute([$_POST['name'], $_POST['category'], $description, $_POST['rules'], $_POST['date'], $_POST['time'], $_POST['venue'], $c_name, $c_phone, $coord_id, $_POST['max_participants'], $image_path, $is_team, $min_ts, $max_ts, $id]);
        $msg = "EVENT TRACK UPDATED.";
    }

    // 3. Registration Management Functions
    elseif ($action === 'update_reg_status') {
        $pdo->prepare("UPDATE registrations SET status = ? WHERE id = ?")->execute([$_POST['status'], $_POST['reg_id']]);
        $msg = "PARTICIPANT STATUS UPDATED.";
    }
}

// Data Fetching
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$events_count = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$total_regs = $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();

$participation_data = $pdo->query("SELECT name, current_participants as count FROM events ORDER BY current_participants DESC LIMIT 6")->fetchAll();
$all_events = $pdo->query("SELECT * FROM events ORDER BY name")->fetchAll();
$all_users = $pdo->query("SELECT * FROM users ORDER BY role DESC, name")->fetchAll();
$coordinators = $pdo->query("SELECT user_id, name FROM users WHERE role = 'coordinator'")->fetchAll();
$all_regs = $pdo->query("SELECT r.*, u.name as user_name, u.college, e.name as event_name FROM registrations r JOIN users u ON r.user_id = u.user_id JOIN events e ON r.event_id = e.id ORDER BY r.created_at DESC")->fetchAll();
?>

<div style="padding: 40px;">
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Command Center</h1>
            <p>Full-scale management of users, events, and festival registrations.</p>
        </div>
        <div class="header-actions">
            <span class="status-tag"
                style="background: rgba(168, 85, 247, 0.1); color: var(--secondary); padding: 8px 16px; font-size: 0.8rem; border-radius: 8px;">
                <i class="fa-solid fa-shield-halved"></i> MASTER ADMINISTRATION
            </span>
        </div>
    </div>

    <?php if ($msg): ?>
        <div
            style="margin-bottom: 20px; padding: 15px; background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); color: var(--success); border-radius: 12px; font-size: 0.9rem;">
            <i class="fa-solid fa-circle-check"></i> <?= $msg ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div
            style="margin-bottom: 20px; padding: 15px; background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); border-radius: 12px; font-size: 0.9rem;">
            <i class="fa-solid fa-circle-exclamation"></i> <?= $error ?>
        </div>
    <?php endif; ?>

    <!-- Navigation Tabs -->
    <div class="tab-container">
        <button onclick="showTab('dashboard')" class="tab-btn active" id="tab-dashboard"><i
                class="fa-solid fa-chart-pie" style="margin-right: 8px;"></i> DASHBOARD</button>
        <button onclick="showTab('users')" class="tab-btn" id="tab-users"><i class="fa-solid fa-users"
                style="margin-right: 8px;"></i> USER MANAGEMENT</button>
        <button onclick="showTab('events')" class="tab-btn" id="tab-events"><i class="fa-solid fa-calendar-alt"
                style="margin-right: 8px;"></i> EVENT MANAGEMENT</button>
        <button onclick="showTab('regs')" class="tab-btn" id="tab-regs"><i class="fa-solid fa-clipboard-check"
                style="margin-right: 8px;"></i> REGISTRATION DESK</button>
    </div>

    <!-- TAB: DASHBOARD -->
    <div id="content-dashboard" class="admin-tab-content">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 40px;">
            <div class="glass-panel-dash" style="padding: 30px; text-align: center;">
                <div style="font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase;">Total Participants
                </div>
                <div style="font-size: 2.2rem; font-weight: 800; color: var(--primary);"><?= $total_users ?></div>
            </div>
            <div class="glass-panel-dash" style="padding: 30px; text-align: center;">
                <div style="font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase;">Active Events</div>
                <div style="font-size: 2.2rem; font-weight: 800; color: var(--secondary);"><?= $events_count ?></div>
            </div>
            <div class="glass-panel-dash" style="padding: 30px; text-align: center;">
                <div style="font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase;">Total Bookings</div>
                <div style="font-size: 2.2rem; font-weight: 800; color: var(--success);"><?= $total_regs ?></div>
            </div>
        </div>
        <div class="glass-panel-dash" style="padding: 30px;">
            <h2 style="font-family: 'Outfit'; font-size: 1.2rem; margin-bottom: 30px;">Top Competition Entries</h2>
            <div style="height: 300px;"><canvas id="participationChart"></canvas></div>
        </div>
    </div>

    <!-- TAB: USER MANAGEMENT -->
    <div id="content-users" class="admin-tab-content" style="display: none;">
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">
            <div class="glass-panel-dash" style="padding: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h2 style="font-family: 'Outfit'; font-size: 1.4rem; margin: 0;">Account Directory</h2>
                    <a href="export_data.php?type=users" class="btn-coord" style="text-decoration: none;">
                        <i class="fa-solid fa-file-export"></i> EXPORT CSV
                    </a>
                </div>
                <div style="overflow-x: auto;">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>USER</th>
                                <th style="text-align: center;">ROLE</th>
                                <th style="text-align: right;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_users as $u): ?>
                                <tr>
                                    <td data-label="User">
                                        <div style="font-weight: 600;"><?= htmlspecialchars($u['name']) ?></div>
                                        <div style="font-size: 0.65rem; color: var(--text-dim);"><?= $u['email'] ?></div>
                                    </td>
                                    <td style="text-align: center;" data-label="Role">
                                        <form method="POST">
                                            <input type="hidden" name="action" value="update_user_role">
                                            <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                            <select name="role" onchange="this.form.submit()" class="modern-select"
                                                style="padding: 6px 12px; width: auto; display: inline-block;">
                                                <option value="student" <?= $u['role'] == 'student' || $u['role'] == 'user' ? 'selected' : '' ?>>Student</option>
                                                <option value="coordinator" <?= $u['role'] == 'coordinator' ? 'selected' : '' ?>>Coord</option>
                                                <option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>Admin
                                                </option>
                                            </select>
                                        </form>
                                    </td>
                                    <td style="text-align: right;" data-label="Actions">
                                        <form method="POST" onsubmit="return confirm('Delete user?');">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                            <button type="submit" class="btn-icon-danger" title="Delete User"><i
                                                    class="fa-solid fa-trash-can"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="glass-panel-dash" style="padding: 30px;">
                <h3 style="font-family: 'Outfit'; font-size: 1.1rem; color: var(--primary); margin-bottom: 20px;">
                    Onboard New User</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="create_user">
                    <div class="form-group" style="margin-bottom: 15px;"><label class="modern-label">User ID / Reg
                            No.</label><input type="text" name="user_id" class="modern-input" required
                            placeholder="e.g. STD1001"></div>
                    <div class="form-group" style="margin-bottom: 15px;"><label class="modern-label">Full
                            Name</label><input type="text" name="name" class="modern-input" required
                            placeholder="John Doe"></div>
                    <div class="form-group" style="margin-bottom: 15px;"><label class="modern-label">Email
                            Address</label><input type="email" name="email" class="modern-input" required
                            placeholder="john@example.com"></div>
                    <div class="form-group" style="margin-bottom: 15px;"><label class="modern-label">Phone
                            Number</label><input type="text" name="phone" class="modern-input" required
                            placeholder="1234567890"></div>
                    <div class="form-group" style="margin-bottom: 15px;"><label class="modern-label">Role</label><select
                            name="role" class="modern-select">
                            <option value="student">Student / User</option>
                            <option value="coordinator">Coordinator</option>
                            <option value="admin">Administrator</option>
                        </select></div>
                    <div class="form-group" style="margin-bottom: 25px;"><label class="modern-label">Default
                            Password</label><input type="password" name="password" class="modern-input" required
                            placeholder="********"></div>
                    <button type="submit" class="btn-start-dash">ADD ACCOUNT</button>
                </form>
            </div>
        </div>
    </div>

    <!-- TAB: EVENT MANAGEMENT -->
    <div id="content-events" class="admin-tab-content" style="display: none;">
        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 30px;">
            <div class="glass-panel-dash" style="padding: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h2 style="font-family: 'Outfit'; font-size: 1.4rem;  margin: 0;">Competition Tracks</h2>
                    <a href="export_data.php?type=events" class="btn-coord" style="text-decoration: none;">
                        <i class="fa-solid fa-file-export"></i> EXPORT CSV
                    </a>
                </div>
                <div style="overflow-x: auto;">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>EVENT</th>
                                <th style="text-align: center;">VENUE</th>
                                <th style="text-align: right;">COORDINATOR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_events as $ev): ?>
                                <tr style="cursor: pointer;" onclick='editEvent(<?= json_encode($ev) ?>)'>
                                    <td data-label="Event">
                                        <div style="font-weight: 600; color: var(--primary);">
                                            <?= htmlspecialchars($ev['name']) ?></div>
                                        <div style="font-size: 0.7rem; color: var(--text-dim); margin-top: 4px;"><i
                                                class="fa-solid fa-layer-group"></i> <?= $ev['category'] ?> Track</div>
                                    </td>
                                    <td style="text-align: center;" data-label="Venue"><i
                                            class="fa-solid fa-location-dot text-dim"></i> <?= $ev['venue'] ?: 'TBD' ?></td>
                                    <td style="text-align: right;" data-label="Coordinator">
                                        <div style="font-weight: 600;">
                                            <?= htmlspecialchars($ev['coordinator_name'] ?: 'System Assigned') ?></div>
                                        <div style="font-size: 0.65rem; color: var(--text-dim);">
                                            <?= $ev['coordinator_id'] ?: 'AUTO' ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="glass-panel-dash" style="padding: 30px;">
                <h3 id="ev-form-title"
                    style="font-family: 'Outfit'; font-size: 1.1rem; color: var(--primary); margin-bottom: 20px;">Deploy
                    New Event</h3>
                <form id="ev-form" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" id="ev-action" value="create_event">
                    <input type="hidden" name="event_id" id="ev-id">
                    <input type="hidden" name="existing_logo" id="ev-existing-logo">
                    
                    <!-- Section 1: Basic Information -->
                    <div style="margin-bottom: 20px;">
                        <div style="font-size: 0.65rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-info-circle"></i> Basic Track Info
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;"><label class="modern-label">Track Title</label><input type="text" name="name" id="ev-name" class="modern-input" required placeholder="e.g. Code Rush"></div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                            <div><label class="modern-label">Track Type</label><select name="category" id="ev-cat" class="modern-select"><option value="IT">IT Track</option><option value="Commerce">Commerce</option></select></div>
                            <div><label class="modern-label">Max Capacity</label><input type="number" name="max_participants" id="ev-max" value="50" class="modern-input"></div>
                        </div>
                    </div>

                    <!-- Section 2: Branding (The Requested Logo Section) -->
                    <div style="margin-bottom: 25px; padding: 20px; background: rgba(0, 212, 255, 0.03); border: 1px dashed rgba(0, 212, 255, 0.2); border-radius: 12px;">
                        <div style="font-size: 0.65rem; color: var(--accent-1); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; font-weight: 700;">
                            <i class="fa-solid fa-palette"></i> Event Branding Section
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label class="modern-label">Event Logo / Header Image</label>
                            <label class="mobile-file-upload">
                                <input type="file" name="logo" id="ev-logo" accept="image/*" onchange="previewLogo(this)">
                                <div class="mobile-file-upload-content">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <span>Tap to Browse Images</span>
                                    <span style="font-size: 0.65rem; color: var(--text-dim); font-weight: 400;">JPEG, PNG, WEBP</span>
                                </div>
                            </label>
                            <div id="logo-preview-container" style="margin-top: 15px; display: none; text-align: center;">
                                <div style="font-size: 0.6rem; color: var(--text-dim); margin-bottom: 8px;">LIVE PREVIEW</div>
                                <img id="logo-preview" src="#" alt="Preview" style="max-width: 100%; height: 120px; border-radius: 12px; object-fit: cover; border: 1px solid var(--border-bright); box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="modern-label">Short Tagline / Description</label>
                            <textarea name="description" id="ev-desc" class="modern-textarea" style="height: 60px;" placeholder="Brief overview for the event card..."></textarea>
                        </div>
                    </div>

                    <!-- Section 3: Logistics -->
                    <div style="margin-bottom: 20px;">
                        <div style="font-size: 0.65rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-clock"></i> Schedule & Logistics
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label class="modern-label">Assign Coordinator</label>
                            <select name="coordinator_id" id="ev-coord" class="modern-select">
                                <option value="">-- Let System Auto-Assign / None --</option>
                                <?php foreach ($coordinators as $c): ?>
                                    <option value="<?= $c['user_id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= $c['user_id'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                            <div><label class="modern-label">Date</label><input type="date" name="date" id="ev-date" class="modern-input"></div>
                            <div><label class="modern-label">Time</label><input type="time" name="time" id="ev-time" class="modern-input"></div>
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;"><label class="modern-label">Venue</label><input type="text" name="venue" id="ev-venue" class="modern-input" placeholder="e.g. Main Auditorium"></div>
                        <div class="form-group" style="margin-bottom: 25px;"><label class="modern-label">Full Rules (Modal)</label><textarea name="rules" id="ev-rules" class="modern-textarea" style="height: 100px; resize: vertical;" placeholder="Detailed rules..."></textarea></div>
                    </div>

                    <!-- Section 4: Team Event Settings -->
                    <div style="margin-bottom: 25px; padding: 18px; background: rgba(124,58,237,0.05); border: 1px solid rgba(124,58,237,0.2); border-radius: 12px;">
                        <div style="font-size: 0.65rem; color: #a855f7; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; font-weight: 700;">
                            <i class="fa-solid fa-users"></i> Team Event Settings
                        </div>
                        <label style="display:flex; justify-content: space-between; align-items:center; cursor:pointer; margin-bottom:18px; padding: 12px 16px; background: rgba(0,0,0,0.2); border-radius: 12px; border: 1px solid rgba(124,58,237,0.15);">
                            <span style="color:#c4b5fd; font-size:0.85rem; font-weight:700;">Enable Team Registration</span>
                            <input type="checkbox" name="is_team_event" id="ev-is-team" class="mobile-toggle" onchange="toggleEvTeamFields()">
                        </label>
                        <div id="ev-team-size-fields" style="display:none; grid-template-columns:1fr 1fr; gap:15px;">
                            <div>
                                <label class="modern-label">Min Team Size</label>
                                <input type="number" name="min_team_size" id="ev-min-ts" value="2" min="1" max="20" class="modern-input">
                            </div>
                            <div>
                                <label class="modern-label">Max Team Size</label>
                                <input type="number" name="max_team_size" id="ev-max-ts" value="4" min="1" max="20" class="modern-input">
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="ev-submit" class="btn-start-dash">DEPLOY EVENT TRACK</button>
                    <button type="button" onclick="resetEvForm()" class="btn-coord" style="width: 100%; border: none; margin-top: 10px; opacity: 0.7;">CLEAR FORM</button>
                </form>
            </div>
        </div>
    </div>

    <!-- TAB: REGISTRATION DESK -->
    <div id="content-regs" class="admin-tab-content" style="display: none;">
        <div class="glass-panel-dash" style="padding: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h2 style="font-family: 'Outfit'; font-size: 1.4rem;">Flow Management</h2>
                <div style="display: flex; gap: 10px;">
                    <a href="export_data.php?type=participation" class="btn-coord" style="text-decoration: none; padding: 6px 16px; font-size: 0.7rem; color: var(--secondary); border-color: var(--secondary);">
                        <i class="fa-solid fa-file-csv"></i> CSV EXPORT
                    </a>
                    <button onclick="window.print()" class="btn-coord"
                        style="padding: 6px 16px; font-size: 0.7rem; border: 1px solid var(--primary); color: var(--primary);"><i
                            class="fa-solid fa-file-pdf"></i> RECORD PRINT</button>
                </div>
            </div>
            <div style="overflow-x: auto;">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>PARTICIPANT</th>
                            <th>EVENT</th>
                            <th style="text-align: center;">APPROVAL FLOW</th>
                            <th style="text-align: right;">DATETIME</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_regs as $r): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600; font-size: 0.95rem;">
                                        <?= htmlspecialchars($r['user_name']) ?></div>
                                    <div style="font-size: 0.7rem; color: var(--text-dim); margin-top: 4px;"><i
                                            class="fa-solid fa-graduation-cap"></i> <?= htmlspecialchars($r['college']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 500; color: var(--secondary);">
                                        <?= htmlspecialchars($r['event_name']) ?></div>
                                </td>
                                <td style="text-align: center;">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_reg_status">
                                        <input type="hidden" name="reg_id" value="<?= $r['id'] ?>">
                                        <?php
                                        $statusClass = '';
                                        if ($r['status'] == 'registered')
                                            $statusClass = 'pending';
                                        elseif ($r['status'] == 'participated')
                                            $statusClass = 'approved';
                                        elseif ($r['status'] == 'winner' || $r['status'] == 'runner')
                                            $statusClass = 'winner';
                                        ?>
                                        <select name="status" onchange="this.form.submit()"
                                            class="status-select <?= $statusClass ?>">
                                            <option value="registered" <?= $r['status'] == 'registered' ? 'selected' : '' ?>>
                                                PENDING APPROVAL</option>
                                            <option value="participated" <?= $r['status'] == 'participated' ? 'selected' : '' ?>>APPROVED / PLAYED</option>
                                            <option value="winner" <?= $r['status'] == 'winner' ? 'selected' : '' ?>>🏆 WINNER
                                            </option>
                                            <option value="runner" <?= $r['status'] == 'runner' ? 'selected' : '' ?>>🥈
                                                RUNNER-UP</option>
                                        </select>
                                    </form>
                                </td>
                                <td style="text-align: right; font-size: 0.8rem; color: var(--text-muted);">
                                    <i class="fa-regular fa-clock"></i>
                                    <?= date('d M, h:i A', strtotime($r['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($all_regs)): ?>
                            <tr>
                                <td colspan="4" style="padding: 60px; text-align: center; color: var(--text-dim);">No active
                                    registrations.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Styles for Tabs & Inputs -->
<style>
    /* Tab Navigation */
    .tab-container {
        display: flex;
        gap: 15px;
        margin-bottom: 30px;
        border-bottom: 1px solid var(--border);
        padding-bottom: 5px;
        overflow-x: auto;
    }

    .tab-btn {
        background: none;
        border: none;
        color: var(--text-dim);
        padding: 12px 24px;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 12px 12px 0 0;
        position: relative;
        white-space: nowrap;
    }

    .tab-btn:hover {
        color: var(--text-main);
        background: rgba(255, 255, 255, 0.02);
    }

    .tab-btn.active {
        color: var(--primary);
        background: rgba(99, 102, 241, 0.05);
    }

    .tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 100%;
        height: 3px;
        background: var(--primary);
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(99, 102, 241, 0.5);
    }

    /* Modern Form Elements */
    .modern-input,
    .modern-select,
    .modern-textarea {
        width: 100%;
        box-sizing: border-box;
        padding: 12px 16px;
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid var(--border);
        color: var(--text-main);
        border-radius: 10px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.85rem;
        transition: all 0.3s ease;
    }

    .modern-input:focus,
    .modern-select:focus,
    .modern-textarea:focus {
        outline: none;
        border-color: var(--primary);
        background: rgba(99, 102, 241, 0.05);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }

    .modern-label {
        display: block;
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }

    /* Table Styling */
    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .modern-table th {
        padding: 16px;
        text-align: left;
        font-size: 0.7rem;
        color: var(--text-dim);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        position: sticky;
        top: 0;
        background: rgba(17, 24, 39, 0.95);
        backdrop-filter: blur(10px);
        z-index: 10;
    }

    .modern-table td {
        padding: 16px;
        border-bottom: 1px solid var(--border);
        font-size: 0.85rem;
        vertical-align: middle;
        transition: background 0.2s ease;
    }

    .modern-table tbody tr:hover td {
        background: rgba(255, 255, 255, 0.02);
    }

    /* Buttons */
    .btn-icon-danger {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border: 1px solid var(--danger);
        width: 32px;
        height: 32px;
        border-radius: 8px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .btn-icon-danger:hover {
        background: var(--danger);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    /* Select Status */
    .status-select {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        transition: all 0.3s ease;
        cursor: pointer;
        border: 1px solid var(--border);
        -webkit-appearance: none;
        appearance: none;
        background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23ffffff%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E');
        background-repeat: no-repeat;
        background-position: right 10px top 50%;
        background-size: 8px auto;
        padding-right: 28px;
    }

    .status-select.pending {
        background-color: rgba(99, 102, 241, 0.1);
        color: var(--primary);
        border-color: rgba(99, 102, 241, 0.3);
    }

    .status-select.approved {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border-color: rgba(16, 185, 129, 0.3);
    }

    .status-select.winner {
        background-color: rgba(245, 158, 11, 0.1);
        color: var(--warning);
        border-color: rgba(245, 158, 11, 0.3);
    }

    .status-select:focus {
        box-shadow: 0 0 0 2px var(--bg-dark), 0 0 0 4px var(--primary);
        outline: none;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    window.onload = function () {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        const editId = urlParams.get('edit');

        if (tab) showTab(tab);

        if (editId && tab === 'events') {
            // Find event data in the PHP-generated $all_events array
            const allEvents = <?= json_encode($all_events) ?>;
            const eventToEdit = allEvents.find(e => e.id == editId);
            if (eventToEdit) {
                editEvent(eventToEdit);
            }
        }
    }
    function showTab(t) {
        document.querySelectorAll('.admin-tab-content').forEach(c => {
            c.style.opacity = '0';
            c.style.display = 'none';
        });
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));

        const target = document.getElementById('content-' + t);
        if (target) {
            target.style.display = 'block';
            setTimeout(() => target.style.opacity = '1', 50);
        }

        const tabBtn = document.getElementById('tab-' + t);
        if (tabBtn) tabBtn.classList.add('active');

        // Update URL without reload
        const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?tab=' + t;
        window.history.pushState({ path: newUrl }, '', newUrl);
    }

    function editEvent(ev) {
        showTab('events');
        document.getElementById('ev-form-title').innerText = 'Update Competition Track';
        document.getElementById('ev-action').value = 'update_event';
        document.getElementById('ev-id').value = ev.id;
        document.getElementById('ev-name').value = ev.name;
        document.getElementById('ev-desc').value = ev.description || '';
        document.getElementById('ev-cat').value = ev.category;
        document.getElementById('ev-max').value = ev.max_participants;
        document.getElementById('ev-coord').value = ev.coordinator_id || '';
        document.getElementById('ev-date').value = ev.date;
        document.getElementById('ev-time').value = ev.time;
        document.getElementById('ev-venue').value = ev.venue;
        document.getElementById('ev-rules').value = ev.rules;
        document.getElementById('ev-existing-logo').value = ev.image || '';
        
        const preview = document.getElementById('logo-preview');
        if (ev.image) {
            preview.src = ev.image;
            document.getElementById('logo-preview-container').style.display = 'block';
        } else {
            document.getElementById('logo-preview-container').style.display = 'none';
        }

        document.getElementById('ev-submit').innerText = 'SAVE CHANGES';
        document.getElementById('ev-submit').style.background = 'linear-gradient(135deg, var(--secondary), var(--primary))';
        document.getElementById('ev-form').scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Populate team fields
        const isTeam = ev.is_team_event == 1;
        document.getElementById('ev-is-team').checked = isTeam;
        document.getElementById('ev-min-ts').value = ev.min_team_size || 2;
        document.getElementById('ev-max-ts').value = ev.max_team_size || 4;
        toggleEvTeamFields();
        
        // Highlight form momentarily
        const formPanel = document.getElementById('ev-form').closest('.glass-panel-dash');
        formPanel.style.boxShadow = '0 0 20px rgba(99, 102, 241, 0.4)';
        setTimeout(() => { formPanel.style.boxShadow = ''; }, 1500);
    }

    function previewLogo(input) {
        if (input.files && input.files[0]) {
            // Update the span text to show the file name
            const labelSpan = input.closest('.mobile-file-upload').querySelector('span');
            labelSpan.innerText = input.files[0].name;
            labelSpan.style.color = 'var(--accent-1)';

            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('logo-preview').src = e.target.result;
                document.getElementById('logo-preview-container').style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function resetEvForm() {
        document.getElementById('ev-form-title').innerText = 'Deploy New Event Track';
        document.getElementById('ev-action').value = 'create_event';
        document.getElementById('ev-form').reset();
        document.getElementById('ev-existing-logo').value = '';
        document.getElementById('logo-preview-container').style.display = 'none';
        document.getElementById('ev-submit').innerText = 'DEPLOY TRACK';
        document.getElementById('ev-submit').style.background = 'linear-gradient(135deg, var(--primary), var(--secondary))';
        document.getElementById('ev-is-team').checked = false;
        document.getElementById('ev-team-size-fields').style.display = 'none';
    }

    function toggleEvTeamFields() {
        const isTeam = document.getElementById('ev-is-team').checked;
        document.getElementById('ev-team-size-fields').style.display = isTeam ? 'grid' : 'none';
    }

    // Interactive Charts Optimization
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.font.family = 'Plus Jakarta Sans';

    // Check if chart element exists before initializing
    const chartCtx = document.getElementById('participationChart');
    if (chartCtx) {
        new Chart(chartCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($participation_data, 'name') ?: []) ?>,
                datasets: [{
                    label: 'Entries',
                    data: <?= json_encode(array_column($participation_data, 'count') ?: []) ?>,
                    backgroundColor: 'rgba(99, 102, 241, 0.5)',
                    borderColor: '#6366f1',
                    borderWidth: 2,
                    borderRadius: 8,
                    hoverBackgroundColor: 'rgba(168, 85, 247, 0.7)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { precision: 0 } },
                    x: { grid: { display: false } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        titleFont: { size: 14, family: 'Outfit' },
                        bodyFont: { size: 14, weight: 'bold' },
                        padding: 12,
                        cornerRadius: 8,
                        borderColor: 'rgba(255,255,255,0.1)',
                        borderWidth: 1
                    }
                },
                animation: { duration: 2000, easing: 'easeOutQuart' }
            }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>