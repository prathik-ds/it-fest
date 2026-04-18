<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/db.php';

if (!isset($_SESSION['user'])) {
    die("Access Denied. Please login first.");
}

$user_id = $_SESSION['user']['user_id'];
$event_id = $_GET['event_id'] ?? null;

if (!$event_id) {
    die("Invalid Request.");
}

// Fetch registration and event details to verify participation
$stmt = $pdo->prepare("
    SELECT r.*, e.name as event_name, e.category, u.name as user_name 
    FROM registrations r 
    JOIN events e ON r.event_id = e.id 
    JOIN users u ON r.user_id = u.user_id 
    WHERE r.user_id = ? AND r.event_id = ?
");
$stmt->execute([$user_id, $event_id]);
$data = $stmt->fetch();

if (!$data) {
    die("Registration not found.");
}

// Verify participation (either status is winner, runner, participated OR attendance is present)
// Based on current DB logic, let's allow it if status is not 'registered' (pending)
if ($data['status'] === 'registered') {
    die("Certificate will be available after participation is verified by the coordinator.");
}

$certificate_type = "PARTICIPATION";
if ($data['status'] === 'winner') $certificate_type = "WINNER";
if ($data['status'] === 'runner') $certificate_type = "RUNNER UP";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - <?= htmlspecialchars($data['event_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Great+Vibes&family=Montserrat:wght@400;700&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #D4AF37;
            --dark: #07090E;
        }
        body {
            margin: 0;
            padding: 0;
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: 'Montserrat', sans-serif;
        }
        .certificate-container {
            width: 1000px;
            height: 700px;
            background: white;
            position: relative;
            padding: 40px;
            box-sizing: border-box;
            border: 20px solid var(--dark);
            box-shadow: 0 0 50px rgba(0,0,0,0.2);
        }
        .outer-border {
            position: absolute;
            top: 10px; left: 10px; right: 10px; bottom: 10px;
            border: 2px solid var(--gold);
        }
        .inner-content {
            height: 100%;
            border: 1px solid var(--gold);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px;
        }
        .logo {
            font-family: 'Cinzel', serif;
            font-size: 2.5rem;
            color: var(--dark);
            margin-bottom: 20px;
            letter-spacing: 5px;
        }
        .certificate-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--gold);
            text-transform: uppercase;
            letter-spacing: 10px;
            margin-bottom: 40px;
        }
        .presented-to {
            font-size: 1rem;
            color: #666;
            margin-bottom: 10px;
        }
        .student-name {
            font-family: 'Great Vibes', cursive;
            font-size: 4rem;
            color: var(--dark);
            margin: 10px 0 30px 0;
        }
        .description {
            font-size: 1.1rem;
            color: #444;
            max-width: 700px;
            line-height: 1.8;
            margin-bottom: 40px;
        }
        .event-details {
            font-weight: 700;
            color: var(--dark);
        }
        .footer {
            width: 100%;
            display: flex;
            justify-content: space-around;
            margin-top: 50px;
        }
        .sig-block {
            border-top: 2px solid #ccc;
            padding-top: 10px;
            width: 200px;
        }
        .sig-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #888;
        }
        .badge {
            position: absolute;
            bottom: 60px;
            right: 60px;
            width: 120px;
            opacity: 0.1;
        }
        @media print {
            body { background: white; }
            .certificate-container { box-shadow: none; border-width: 15px; }
            .print-btn { display: none; }
        }
        @media (max-width: 768px) {
            body { height: auto; padding: 10px; align-items: flex-start; }
            .certificate-container {
                width: 100%;
                height: auto;
                border-width: 8px;
                padding: 15px;
            }
            .inner-content { padding: 20px 10px; }
            .logo { font-size: 1.5rem; letter-spacing: 2px; margin-bottom: 10px; }
            .certificate-title { font-size: 0.8rem; letter-spacing: 4px; margin-bottom: 20px; }
            .student-name { font-size: 2rem; margin: 5px 0 15px 0; }
            .description { font-size: 0.85rem; line-height: 1.6; margin-bottom: 20px; }
            .footer { flex-direction: column; gap: 20px; align-items: center; margin-top: 25px; }
            .sig-block { width: 160px; }
            .presented-to { font-size: 0.8rem; }
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: var(--dark);
            color: white;
            border: none;
            border-radius: 8px;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            cursor: pointer;
            z-index: 100;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
        }
        .print-btn:hover { background: #1a1f2e; }
    </style>
</head>
<body>

    <button onclick="window.print()" class="print-btn">
        <span>PRINT CERTIFICATE</span>
    </button>

    <div class="certificate-container">
        <div class="outer-border"></div>
        <div class="inner-content">
            <div class="logo">FusionVerse FEST</div>
            <div class="certificate-title">Certificate of <?= $certificate_type ?></div>
            
            <div class="presented-to">THIS IS PROUDLY PRESENTED TO</div>
            <div class="student-name"><?= htmlspecialchars($data['user_name']) ?></div>
            
            <div class="description">
                for their outstanding performance and successful participation in the 
                <span class="event-details"><?= htmlspecialchars($data['event_name']) ?></span> 
                event held during <span class="event-details">FusionVerse IT & Commerce Fest 2026</span>.
            </div>

            <div class="footer">
                <div class="sig-block">
                    <div style="font-family: 'Great Vibes', cursive; font-size: 1.5rem; margin-bottom: 5px;">H. Richardson</div>
                    <div class="sig-label">Event Coordinator</div>
                </div>
                <div class="sig-block">
                    <div style="font-family: 'Great Vibes', cursive; font-size: 1.5rem; margin-bottom: 5px;">S. Varma</div>
                    <div class="sig-label">Festival Director</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
