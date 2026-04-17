<?php
include 'includes/header.php';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

$stmt = $pdo->query("SELECT * FROM events ORDER BY category, name");
$events = $stmt->fetchAll();

// Get user's team memberships for team events
$myTeams = [];
if ($user) {
    $uid = $user['user_id'];
    $stmt2 = $pdo->prepare("
        SELECT t.event_id, t.id as team_id, t.name as team_name, t.invite_code, t.leader_user_id
        FROM teams t
        JOIN team_members tm ON t.id = tm.team_id
        WHERE tm.user_id = ?
    ");
    $stmt2->execute([$uid]);
    foreach ($stmt2->fetchAll() as $row) {
        $myTeams[$row['event_id']] = $row;
    }
}
?>

<style>
/* Override container width for events page to use full width */
.container { max-width: 100% !important; padding: 0 40px !important; }

/* Events grid */
.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 32px;
    margin-top: 40px;
    margin-bottom: 80px;
}

/* Event card */
.ev-card {
    background: rgba(15, 22, 41, 0.6);
    border: 1px solid rgba(100, 130, 200, 0.15);
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
    position: relative;
}
.ev-card:hover {
    transform: translateY(-8px);
    border-color: rgba(0, 212, 255, 0.35);
    box-shadow: 0 20px 50px rgba(0,0,0,0.35), 0 0 30px rgba(0,212,255,0.08);
}
.ev-card-img {
    height: 200px;
    position: relative;
    overflow: hidden;
    background: rgba(8, 12, 26, 0.8);
    flex-shrink: 0;
}
.ev-card-img img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.ev-card:hover .ev-card-img img { transform: scale(1.08); }
.ev-card-img-placeholder {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, rgba(124,58,237,0.15), rgba(0,212,255,0.08));
}
.ev-card-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to bottom, transparent 40%, rgba(8,12,26,0.85) 100%);
    pointer-events: none;
}
.ev-card-badge {
    position: absolute; top: 14px; left: 14px; z-index: 2;
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(8,12,26,0.65);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.12);
    padding: 4px 12px; border-radius: 50px;
}
.ev-card-body {
    padding: 22px 24px 16px;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.ev-card-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.3rem; font-weight: 700;
    color: #f0f4ff; letter-spacing: -0.01em;
    margin-bottom: 8px;
}
.ev-card-desc {
    color: #94a3c7; font-size: 0.84rem;
    line-height: 1.65; margin-bottom: 18px;
    flex: 1;
}
.ev-card-meta {
    display: flex; flex-direction: column; gap: 6px;
    margin-bottom: 16px;
}
.ev-card-meta-item {
    display: flex; align-items: center; gap: 8px;
    font-size: 0.8rem; color: #94a3c7;
}
.ev-card-meta-item i { width: 16px; text-align: center; }
.ev-cap-label {
    display: flex; justify-content: space-between;
    font-size: 0.7rem; color: #5b6a8a; margin-bottom: 5px;
}
.ev-cap-bar {
    width: 100%; height: 4px;
    background: rgba(255,255,255,0.06);
    border-radius: 4px; overflow: hidden;
}
.ev-cap-fill {
    height: 100%; border-radius: 4px;
    transition: width 0.5s ease;
}
.ev-card-footer {
    padding: 0 24px 22px;
}
.ev-register-btn {
    width: 100%;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 13px;
    background: transparent;
    border: 2px solid #00d4ff;
    color: #00d4ff;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 0.82rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 1.5px;
    border-radius: 12px; cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}
.ev-register-btn:hover {
    background: rgba(0,212,255,0.1);
    box-shadow: 0 0 20px rgba(0,212,255,0.2);
    color: #fff;
}
.ev-register-btn:disabled {
    opacity: 0.4; cursor: not-allowed;
    border-color: rgba(255,255,255,0.15); color: #5b6a8a;
}

@media (max-width: 900px) {
    .events-grid { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
    .container { padding: 0 16px !important; }
}
@media (max-width: 600px) {
    .events-grid { grid-template-columns: 1fr; gap: 14px; }
    .container { padding: 0 12px !important; margin-top: 0 !important; }
    .ev-card-title { font-size: 1.1rem; }
    .ev-card-body { padding: 14px 16px 10px; }
    .ev-card-footer { padding: 0 16px 16px; }
}

/* ── Team Badge ── */
.ev-team-badge {
    position: absolute; top: 14px; right: 14px; z-index: 2;
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(124,58,237,0.55);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(124,58,237,0.5);
    padding: 4px 10px; border-radius: 50px;
    font-size: 0.6rem; font-weight: 800; color: white; text-transform: uppercase; letter-spacing: 1.5px;
}
.ev-register-btn.team-btn {
    border-color: #a855f7;
    color: #a855f7;
}
.ev-register-btn.team-btn:hover {
    background: rgba(168,85,247,0.1);
    box-shadow: 0 0 20px rgba(168,85,247,0.2);
    color: #fff;
}
.ev-register-btn.joined-btn {
    border-color: #10b981; color: #10b981;
    cursor: default; pointer-events: none; opacity: 0.85;
}

/* ── Team Modal ── */
#teamModal {
    display: none; position: fixed; inset: 0;
    background: rgba(4,6,14,0.85); z-index: 3000;
    align-items: center; justify-content: center;
    backdrop-filter: blur(10px);
}
.team-modal-box {
    background: rgba(15,22,41,0.97);
    border: 1px solid rgba(124,58,237,0.25);
    border-radius: 24px; padding: 36px;
    max-width: 420px; width: 92%;
    position: relative;
    animation: fadeInUp 0.3s ease;
}
.team-tab-bar {
    display: flex; gap: 10px; margin-bottom: 28px;
}
.team-tab {
    flex: 1; padding: 10px;
    border-radius: 10px; border: 1px solid rgba(255,255,255,0.08);
    background: transparent; color: #94a3c7;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 0.78rem; font-weight: 700; cursor: pointer;
    transition: all 0.25s;
    text-transform: uppercase; letter-spacing: 1px;
}
.team-tab.active {
    background: rgba(124,58,237,0.15);
    border-color: rgba(124,58,237,0.4);
    color: #a855f7;
}
.team-panel { display: none; }
.team-panel.active { display: block; }
.team-input {
    width: 100%; padding: 12px 16px;
    background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px; color: #f0f4ff;
    font-family: 'Space Grotesk', sans-serif; font-size: 0.9rem;
    box-sizing: border-box; margin-bottom: 14px;
    transition: border-color 0.25s;
}
.team-input:focus { outline: none; border-color: rgba(124,58,237,0.5); }
.team-input::placeholder { color: #5b6a8a; }
.team-submit-btn {
    width: 100%; padding: 13px;
    background: linear-gradient(135deg, rgba(124,58,237,0.8), rgba(168,85,247,0.8));
    border: none; border-radius: 12px;
    color: white; font-family: 'Space Grotesk', sans-serif;
    font-size: 0.82rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 1.5px;
    cursor: pointer; transition: opacity 0.25s;
}
.team-submit-btn:hover { opacity: 0.85; }
.team-submit-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.team-alert {
    padding: 12px 16px; border-radius: 10px;
    font-size: 0.82rem; margin-bottom: 16px; display: none;
}
.team-alert.success { background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.2); color: #10b981; }
.team-alert.error   { background: rgba(244,63,94,0.08);  border: 1px solid rgba(244,63,94,0.2);  color: #f43f5e; }
.team-code-box {
    font-family: 'JetBrains Mono', monospace;
    font-size: 1.5rem; font-weight: 800;
    letter-spacing: 6px; text-align: center;
    padding: 18px; background: rgba(124,58,237,0.08);
    border: 2px dashed rgba(124,58,237,0.35);
    border-radius: 14px; color: #a855f7; margin: 16px 0;
    cursor: pointer; transition: background 0.2s;
}
.team-code-box:hover { background: rgba(124,58,237,0.14); }
.team-member-chip {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px; padding: 6px 12px; font-size: 0.78rem;
    color: #94a3c7; margin: 4px;
}
.team-member-chip.leader { border-color: rgba(251,191,36,0.3); color: #fbbf24; }
.team-info-section { margin-top: 20px; }
.team-size-hint {
    font-size: 0.75rem; color: #5b6a8a;
    text-align: center; margin-top: 8px;
}
</style>

<!-- Page Header -->
<div style="margin-bottom: 10px; text-align: center; padding-top: 10px;">
    <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(0,212,255,0.06); border: 1px solid rgba(0,212,255,0.15); padding: 6px 16px; border-radius: 50px; margin-bottom: 16px;">
        <i class="fa-solid fa-trophy" style="font-size: 0.7rem; color: var(--accent-1);"></i>
        <span style="font-size: 0.7rem; font-weight: 700; color: var(--accent-1); text-transform: uppercase; letter-spacing: 2px;">Live Events</span>
    </div>
    <h1 style="font-family: 'Space Grotesk', sans-serif; font-size: 3rem; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 10px;">
        <span style="background: var(--grad-primary); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">Competitions</span>
    </h1>
    <p style="color: var(--text-secondary); max-width: 400px; margin: 0 auto; font-size: 0.9rem;">Choose your challenge and register to compete at FusionVerse 2026</p>
</div>

<?php if ($success): ?>
<div style="padding: 14px 20px; border-radius: 14px; border: 1px solid rgba(16,185,129,0.2); background: rgba(16,185,129,0.06); color: var(--text-primary); margin-bottom: 20px; font-size: 0.88rem; display: flex; align-items: center; gap: 10px;">
    <i class="fa-solid fa-circle-check" style="color: var(--success);"></i>
    Registration Successful! View your events in the <a href="dashboard.php" style="color: var(--accent-2); font-weight: 700; text-decoration: none;">Dashboard →</a>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div style="padding: 14px 20px; border-radius: 14px; border: 1px solid rgba(244,63,94,0.2); background: rgba(244,63,94,0.06); color: var(--danger); margin-bottom: 20px; font-size: 0.88rem; display: flex; align-items: center; gap: 10px;">
    <i class="fa-solid fa-circle-exclamation"></i>
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<!-- Events Grid -->
<div class="events-grid">
    <?php foreach ($events as $index => $event): ?>
    <?php
        $fill = $event['max_participants'] > 0 ? ($event['current_participants'] / $event['max_participants']) * 100 : 0;
        $is_full = $event['current_participants'] >= $event['max_participants'];
        $cat_color = $event['category'] == 'IT' ? 'var(--accent-1)' : 'var(--accent-2)';
        $cap_color = $fill > 80 ? 'var(--danger)' : 'linear-gradient(90deg, var(--accent-2), var(--accent-1))';
        $is_team = !empty($event['is_team_event']);
        $my_team = $myTeams[$event['id']] ?? null;
    ?>
    <div class="ev-card" style="animation: fadeInUp <?= 0.2 + ($index * 0.07) ?>s ease forwards; opacity: 0;">
        
        <!-- Image Header -->
        <div class="ev-card-img">
            <?php if (!empty($event['image'])): ?>
                <img src="<?= htmlspecialchars($event['image']) ?>" alt="<?= htmlspecialchars($event['name']) ?>" loading="lazy">
            <?php else: ?>
                <div class="ev-card-img-placeholder">
                    <i class="fa-solid fa-microchip" style="font-size: 3.5rem; color: <?= $cat_color ?>; opacity: 0.3;"></i>
                </div>
            <?php endif; ?>
            <div class="ev-card-overlay"></div>
            <div class="ev-card-badge">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: <?= $cat_color ?>;"></span>
                <span style="font-size: 0.62rem; font-weight: 800; color: white; text-transform: uppercase; letter-spacing: 1.5px;"><?= htmlspecialchars($event['category']) ?></span>
            </div>
            <?php if ($is_team): ?>
            <div class="ev-team-badge">
                <i class="fa-solid fa-users" style="font-size: 0.55rem;"></i> TEAM
            </div>
            <?php endif; ?>
        </div>

        <!-- Card Body -->
        <div class="ev-card-body">
            <h3 class="ev-card-title"><?= htmlspecialchars($event['name']) ?></h3>
            <p class="ev-card-desc">
                <?= htmlspecialchars($event['description'] ?: 'Challenge yourself in the ' . $event['name'] . ' competition track at FusionVerse 2026.') ?>
            </p>

            <!-- Meta Info -->
            <div class="ev-card-meta">
                <div class="ev-card-meta-item">
                    <i class="fa-regular fa-calendar" style="color: var(--accent-1);"></i>
                    <?= date('d M Y', strtotime($event['date'])) ?> · <?= date('h:i A', strtotime($event['time'])) ?>
                </div>
                <div class="ev-card-meta-item">
                    <i class="fa-solid fa-location-dot" style="color: var(--accent-2);"></i>
                    <?= htmlspecialchars($event['venue']) ?>
                </div>
                <?php if ($is_team): ?>
                <div class="ev-card-meta-item">
                    <i class="fa-solid fa-users" style="color: #a855f7;"></i>
                    Team · <?= $event['min_team_size'] ?>–<?= $event['max_team_size'] ?> members
                </div>
                <?php endif; ?>
            </div>

            <!-- Capacity Bar -->
            <div style="margin-bottom: 4px;">
                <div class="ev-cap-label">
                    <span>Capacity</span>
                    <span style="color: var(--text-secondary); font-weight: 700;"><?= $event['current_participants'] ?> / <?= $event['max_participants'] ?></span>
                </div>
                <div class="ev-cap-bar">
                    <div class="ev-cap-fill" style="width: <?= $fill ?>%; background: <?= $cap_color ?>;"></div>
                </div>
            </div>
        </div>

        <!-- Action Footer -->
        <div class="ev-card-footer">
            <?php if ($user): ?>
                <?php if ($is_team): ?>
                    <?php if ($my_team): ?>
                        <!-- Already in a team -->
                        <button class="ev-register-btn joined-btn"
                            onclick="viewMyTeam(<?= $event['id'] ?>, '<?= htmlspecialchars(addslashes($event['name'])) ?>')" 
                            style="cursor:pointer; pointer-events:all;">
                            <i class="fa-solid fa-users"></i> My Team: <?= htmlspecialchars($my_team['team_name']) ?>
                        </button>
                    <?php elseif ($is_full): ?>
                        <button class="ev-register-btn" disabled>
                            <i class="fa-solid fa-ban"></i> Full House
                        </button>
                    <?php else: ?>
                        <button class="ev-register-btn team-btn"
                            onclick="openTeamModal(<?= $event['id'] ?>, '<?= htmlspecialchars(addslashes($event['name'])) ?>', <?= (int)$event['min_team_size'] ?>, <?= (int)$event['max_team_size'] ?>)">
                            <i class="fa-solid fa-users"></i> Team Register
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <form action="register_event.php" method="POST">
                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                        <button type="submit" class="ev-register-btn" <?= $is_full ? 'disabled' : '' ?>>
                            <?php if ($is_full): ?>
                                <i class="fa-solid fa-ban"></i> Full House
                            <?php else: ?>
                                <i class="fa-solid fa-bolt"></i> Register Now
                            <?php endif; ?>
                        </button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php" class="ev-register-btn">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In to Register
                </a>
            <?php endif; ?>
        </div>

    </div>
    <?php endforeach; ?>
</div>

<!-- ═══ TEAM MODAL ═══ -->
<div id="teamModal">
    <div class="team-modal-box">
        <!-- Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <div>
                <h3 id="tModal-eventName" style="font-family:'Space Grotesk',sans-serif; font-size:1.1rem; font-weight:700; color:#a855f7; margin:0;">Team Register</h3>
                <div id="tModal-teamMeta" style="font-size:0.72rem; color:#5b6a8a; margin-top:4px;"></div>
            </div>
            <button onclick="closeTeamModal()" style="background:rgba(244,63,94,0.08); border:1px solid rgba(244,63,94,0.15); color:#f43f5e; cursor:pointer; width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1rem;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div id="tModal-alert" class="team-alert"></div>

        <!-- Tab view: create / join -->
        <div id="tModal-tabs">
            <div class="team-tab-bar">
                <button class="team-tab active" id="tab-create" onclick="switchTab('create')"><i class="fa-solid fa-wand-magic-sparkles"></i> Create Team</button>
                <button class="team-tab" id="tab-join" onclick="switchTab('join')"><i class="fa-solid fa-link"></i> Join Team</button>
            </div>

            <!-- Create Panel -->
            <div class="team-panel active" id="panel-create">
                <input class="team-input" id="tInput-teamName" type="text" placeholder="Team name (e.g. Dark Matter)" maxlength="50">
                <div id="tModal-sizeHint" class="team-size-hint"></div>
                <button class="team-submit-btn" id="btn-createTeam" onclick="doCreateTeam()">
                    <i class="fa-solid fa-rocket"></i> Create & Get Invite Code
                </button>
            </div>

            <!-- Join Panel -->
            <div class="team-panel" id="panel-join">
                <input class="team-input" id="tInput-inviteCode" type="text" placeholder="Enter 6-character invite code" maxlength="10" style="text-transform:uppercase; letter-spacing:4px; font-family:'JetBrains Mono',monospace; text-align:center; font-size:1.2rem;">
                <button class="team-submit-btn" id="btn-joinTeam" onclick="doJoinTeam()">
                    <i class="fa-solid fa-right-to-bracket"></i> Join Team
                </button>
            </div>
        </div>

        <!-- Created team info (after creation) -->
        <div id="tModal-created" style="display:none;">
            <div style="text-align:center; padding:10px 0 4px;">
                <i class="fa-solid fa-party-horn" style="font-size:2.5rem; color:#a855f7; margin-bottom:12px;"></i>
                <p style="color:#94a3c7; font-size:0.85rem; margin-bottom:4px;">Team created! Share this code with teammates:</p>
                <div class="team-code-box" id="tModal-code" onclick="copyCode()" title="Click to copy"></div>
                <p style="font-size:0.7rem; color:#5b6a8a; margin-top:6px;"><i class="fa-solid fa-copy"></i> Click code to copy · Share with your teammates</p>
            </div>
            <div class="team-info-section" id="tModal-memberList"></div>
        </div>

        <!-- View existing team -->
        <div id="tModal-myTeam" style="display:none;">
            <div style="text-align:center; margin-bottom:16px;">
                <i class="fa-solid fa-shield-halved" style="font-size:2rem; color:#a855f7;"></i>
                <h4 id="tMyTeam-name" style="font-family:'Space Grotesk',sans-serif; margin:12px 0 4px; color:#f0f4ff;"></h4>
            </div>
            <div style="font-size:0.7rem; color:#5b6a8a; text-align:center; margin-bottom:6px;">INVITE CODE</div>
            <div class="team-code-box" id="tMyTeam-code" onclick="copyMyCode()" title="Click to copy"></div>
            <p style="font-size:0.7rem; color:#5b6a8a; text-align:center; margin-top:4px;"><i class="fa-solid fa-copy"></i> Click to copy</p>
            <div class="team-info-section" id="tMyTeam-members"></div>
            <button class="team-submit-btn" id="btn-leaveTeam" onclick="doLeaveTeam()" style="margin-top:20px; background:rgba(244,63,94,0.15); border:1px solid rgba(244,63,94,0.3);">
                <i class="fa-solid fa-right-from-bracket"></i> <span id="leaveLabel">Leave Team</span>
            </button>
        </div>
    </div>
</div>

<script>
let _activeEventId = null;
let _activeTeamId  = null;
let _isLeader = false;
let _myCode = '';

function openTeamModal(eventId, eventName, minSize, maxSize) {
    _activeEventId = eventId;
    _activeTeamId  = null;
    document.getElementById('teamModal').style.display = 'flex';
    document.getElementById('tModal-eventName').textContent = eventName;
    document.getElementById('tModal-teamMeta').textContent = `Team size: ${minSize}–${maxSize} members`;
    document.getElementById('tModal-tabs').style.display = 'block';
    document.getElementById('tModal-created').style.display = 'none';
    document.getElementById('tModal-myTeam').style.display = 'none';
    document.getElementById('tInput-teamName').value = '';
    document.getElementById('tInput-inviteCode').value = '';
    document.getElementById('tModal-sizeHint').textContent = `Min ${minSize} · Max ${maxSize} members per team`;
    clearAlert();
    switchTab('create');
}

function viewMyTeam(eventId, eventName) {
    _activeEventId = eventId;
    document.getElementById('teamModal').style.display = 'flex';
    document.getElementById('tModal-eventName').textContent = eventName;
    document.getElementById('tModal-tabs').style.display = 'none';
    document.getElementById('tModal-created').style.display = 'none';
    document.getElementById('tModal-myTeam').style.display = 'none';
    clearAlert();
    loadMyTeam(eventId);
}

function closeTeamModal() {
    document.getElementById('teamModal').style.display = 'none';
}

function switchTab(tab) {
    document.getElementById('tab-create').classList.toggle('active', tab === 'create');
    document.getElementById('tab-join').classList.toggle('active', tab === 'join');
    document.getElementById('panel-create').classList.toggle('active', tab === 'create');
    document.getElementById('panel-join').classList.toggle('active', tab === 'join');
    clearAlert();
}

function showAlert(msg, type) {
    const el = document.getElementById('tModal-alert');
    el.textContent = msg;
    el.className = `team-alert ${type}`;
    el.style.display = 'block';
}
function clearAlert() { document.getElementById('tModal-alert').style.display = 'none'; }

async function doCreateTeam() {
    const name = document.getElementById('tInput-teamName').value.trim();
    if (!name) { showAlert('Enter a team name', 'error'); return; }

    const btn = document.getElementById('btn-createTeam');
    btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating...';
    clearAlert();

    const fd = new FormData();
    fd.append('action', 'create_team');
    fd.append('event_id', _activeEventId);
    fd.append('team_name', name);

    const res = await fetch('team_actions.php', { method: 'POST', body: fd }).then(r => r.json());

    btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-rocket"></i> Create & Get Invite Code';

    if (res.success) {
        _myCode = res.team_code;
        _activeTeamId = res.team_id;
        document.getElementById('tModal-code').textContent = res.team_code;
        document.getElementById('tModal-tabs').style.display = 'none';
        document.getElementById('tModal-created').style.display = 'block';
        document.getElementById('tModal-memberList').innerHTML = `<p style="color:#5b6a8a;font-size:0.8rem;text-align:center;margin-top:8px;">Share the code — members will join and appear here after page refresh.</p>`;
    } else {
        showAlert(res.message, 'error');
    }
}

async function doJoinTeam() {
    const code = document.getElementById('tInput-inviteCode').value.trim().toUpperCase();
    if (!code) { showAlert('Enter an invite code', 'error'); return; }

    const btn = document.getElementById('btn-joinTeam');
    btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Joining...';
    clearAlert();

    const fd = new FormData();
    fd.append('action', 'join_team');
    fd.append('invite_code', code);

    const res = await fetch('team_actions.php', { method: 'POST', body: fd }).then(r => r.json());

    btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> Join Team';

    if (res.success) {
        showAlert('✓ ' + res.message + ' — refreshing...', 'success');
        setTimeout(() => location.reload(), 1500);
    } else {
        showAlert(res.message, 'error');
    }
}

async function loadMyTeam(eventId) {
    const fd = new FormData();
    fd.append('action', 'get_team');
    fd.append('event_id', eventId);

    const res = await fetch('team_actions.php', { method: 'POST', body: fd }).then(r => r.json());

    if (res.success) {
        const t = res.team;
        _activeTeamId = t.id;
        _myCode = t.invite_code;
        _isLeader = (t.leader_user_id === '<?= $user ? $user['user_id'] : '' ?>');

        document.getElementById('tMyTeam-name').textContent = t.name;
        document.getElementById('tMyTeam-code').textContent = t.invite_code;
        document.getElementById('tModal-teamMeta').textContent = t.members.length + ' member(s) · ' + (t.members.length < 2 ? 'Waiting for teammates…' : 'Ready to compete!');

        let membersHtml = '<div style="font-size:0.7rem;color:#5b6a8a;margin-bottom:10px;text-transform:uppercase;letter-spacing:1.5px;">Members</div><div>';
        t.members.forEach(m => {
            const isL = m.user_id === t.leader_user_id;
            membersHtml += `<span class="team-member-chip ${isL ? 'leader' : ''}"><i class="fa-solid fa-${isL ? 'crown' : 'user'}"></i>${m.user_name}${isL ? ' (Leader)' : ''}</span>`;
        });
        membersHtml += '</div>';
        document.getElementById('tMyTeam-members').innerHTML = membersHtml;

        const leaveLabel = document.getElementById('leaveLabel');
        leaveLabel.textContent = _isLeader ? 'Dissolve Team' : 'Leave Team';
        if (_isLeader) {
            document.getElementById('btn-leaveTeam').style.background = 'rgba(244,63,94,0.2)';
        }

        document.getElementById('tModal-myTeam').style.display = 'block';
    } else {
        showAlert('Could not load team info', 'error');
    }
}

async function doLeaveTeam() {
    const confirm_msg = _isLeader
        ? 'This will DISSOLVE the team and remove ALL members. Continue?'
        : 'Leave this team? You can rejoin with the invite code.';
    if (!confirm(confirm_msg)) return;

    const btn = document.getElementById('btn-leaveTeam');
    btn.disabled = true;

    const fd = new FormData();
    fd.append('action', 'leave_team');
    fd.append('team_id', _activeTeamId);

    const res = await fetch('team_actions.php', { method: 'POST', body: fd }).then(r => r.json());
    btn.disabled = false;

    if (res.success) {
        showAlert('✓ ' + res.message + ' — refreshing...', 'success');
        setTimeout(() => location.reload(), 1500);
    } else {
        showAlert(res.message, 'error');
    }
}

function copyCode() {
    navigator.clipboard.writeText(_myCode).then(() => {
        const el = document.getElementById('tModal-code');
        const old = el.textContent;
        el.textContent = 'Copied!';
        setTimeout(() => el.textContent = old, 1200);
    });
}
function copyMyCode() {
    navigator.clipboard.writeText(_myCode).then(() => {
        const el = document.getElementById('tMyTeam-code');
        const old = el.textContent;
        el.textContent = 'Copied!';
        setTimeout(() => el.textContent = old, 1200);
    });
}

// Close modal on backdrop click
document.getElementById('teamModal').addEventListener('click', function(e) {
    if (e.target === this) closeTeamModal();
});
</script>

<?php include 'includes/footer.php'; ?>