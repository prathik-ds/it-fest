<?php 
include 'includes/header.php'; 

// Fetch Top Performers across all events where score > 0
$stmt = $pdo->query("SELECT r.user_id, u.name as user_name, u.college, e.name as event_name, r.score, r.status FROM registrations r JOIN users u ON r.user_id = u.user_id JOIN events e ON r.event_id = e.id WHERE r.status IN ('winner', 'runner') ORDER BY e.name ASC, CASE WHEN LOWER(r.status) = 'winner' THEN 1 WHEN LOWER(r.status) = 'runner' THEN 2 ELSE 3 END ASC LIMIT 50");
$topScores = $stmt->fetchAll();
?>

<!-- Page Header -->
<div style="margin-bottom: 50px; text-align: center; padding-top: 20px;">
    <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(124, 58, 237, 0.06); border: 1px solid rgba(124, 58, 237, 0.15); padding: 6px 16px; border-radius: 50px; margin-bottom: 16px;">
        <i class="fa-solid fa-crown" style="font-size: 0.7rem; color: var(--accent-5);"></i>
        <span style="font-size: 0.7rem; font-weight: 700; color: var(--accent-2); text-transform: uppercase; letter-spacing: 2px;">Rankings</span>
    </div>
    <h1 style="font-family: 'Space Grotesk', sans-serif; font-size: 3rem; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 10px;">
        <span style="background: var(--grad-warm); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">Hall of Fame</span>
    </h1>
    <p style="color: var(--text-secondary); max-width: 400px; margin: 0 auto; font-size: 0.9rem;">Celebrating the top performers of FusionVerse 2026</p>
</div>

<style>
    .hof-table td {
        padding: 18px 24px;
        vertical-align: middle;
    }
    .hof-table th {
        padding: 18px 24px;
    }
    @media (max-width: 768px) {
        /* Container adjustments */
        .glass-hof {
            padding: 0 12px !important;
            background: transparent !important; 
            border: none !important;
            box-shadow: none !important;
        }
        
        /* Hide table headers natively on mobile */
        .hof-table thead {
            display: none !important;
        }

        /* Transform table row into a 3-column Grid card */
        .hof-table tr {
            display: grid !important;
            grid-template-columns: auto 1fr auto !important;
            grid-template-areas: 
                "medal participant badge"
                "medal event event";
            align-items: center;
            gap: 2px 14px !important;
            padding: 16px 14px !important;
            margin-bottom: 12px !important;
            background: rgba(15, 22, 41, 0.7) !important;
            border-radius: 16px !important;
            border: 1px solid rgba(124, 58, 237, 0.2) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2) !important;
            position: relative;
            overflow: hidden;
        }

        /* Add a subtle glow behind the cards */
        .hof-table tr::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 4px; height: 100%;
            background: var(--grad-primary);
            border-radius: 4px 0 0 4px;
        }

        /* Clean up standard table-card td overrides */
        .hof-table td {
            display: block !important;
            padding: 0 !important;
            min-height: auto !important;
            text-align: left !important;
            border: none !important;
        }
        .hof-table td:before {
            display: none !important; /* Hide data labels */
        }

        /* Position elements to Grid areas */
        .hof-table td[data-label="Standing"] {
            grid-area: medal;
            display: flex !important;
            align-items: center;
            justify-content: center;
        }
        .hof-table td[data-label="Standing"] span {
            font-size: 2.2rem !important; /* Make Medals Huge */
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.4));
        }

        .hof-table td[data-label="Participant"] {
            grid-area: participant;
        }
        .hof-table .participant-cell {
            gap: 12px !important;
        }
        .hof-table .participant-cell > div:last-child > div {
            font-size: 1rem !important; /* Name text size */
        }

        .hof-table td[data-label="Event"] {
            grid-area: event;
            /* Indent text to match name text (avatar 34px + gap 12px) */
            padding-left: 46px !important; 
        }
        .hof-table td[data-label="Event"] > div > div {
            font-size: 0.78rem !important;
            color: var(--text-muted) !important;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .hof-table td[data-label="Status"] {
            grid-area: badge;
            justify-self: end !important;
            align-self: start !important;
        }
    }
    @media (max-width: 480px) {
        .hof-table tr {
            padding: 14px 12px !important;
            gap: 2px 10px !important;
        }
        .hof-table td[data-label="Event"] {
            padding-left: 44px !important; 
        }
        .hof-table td[data-label="Standing"] span {
            font-size: 1.8rem !important;
        }
    }
</style>

<!-- Leaderboard Card -->
<div class="glass glass-hof" style="max-width: 1000px; margin: 0 auto; padding: 0; overflow: hidden; border-color: rgba(124, 58, 237, 0.12);">
    <?php if(empty($topScores)): ?>
        <div style="text-align: center; padding: 80px 20px;">
            <div style="width: 72px; height: 72px; background: rgba(124, 58, 237, 0.08); border-radius: 20px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                <i class="fa-solid fa-hourglass-half" style="font-size: 1.8rem; color: var(--accent-2);"></i>
            </div>
            <p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 8px;">Leaderboard is warming up...</p>
            <p style="color: var(--text-dim); font-size: 0.82rem;">Check back once the competitions begin!</p>
        </div>
    <?php else: ?>
        <table class="hof-table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border);">
                    <th style="font-family: 'Space Grotesk', sans-serif; font-size: 0.72rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700;">Standing</th>
                    <th style="font-family: 'Space Grotesk', sans-serif; font-size: 0.72rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700;">Participant</th>
                    <th style="font-family: 'Space Grotesk', sans-serif; font-size: 0.72rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700;">Event</th>
                    <th style="font-family: 'Space Grotesk', sans-serif; font-size: 0.72rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; text-align: right;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($topScores as $row): ?>
                    <tr style="border-bottom: 1px solid var(--border); transition: background 0.2s;" onmouseover="this.style.background='rgba(0, 212, 255, 0.03)'" onmouseout="this.style.background='transparent'">
                        <td data-label="Standing">
                            <?php if (strtolower($row['status']) == 'winner'): ?>
                                <span style="font-size: 1.2rem;" title="1st Place">🥇</span>
                            <?php elseif (strtolower($row['status']) == 'runner'): ?>
                                <span style="font-size: 1.2rem;" title="2nd Place">🥈</span>
                            <?php else: ?>
                                <span style="font-size: 1.2rem;" title="Participant">🏅</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Participant">
                            <div class="participant-cell" style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 34px; height: 34px; border-radius: 10px; background: <?= strtolower($row['status']) == 'winner' ? 'var(--grad-primary)' : 'rgba(100, 130, 200, 0.1)' ?>; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: white; flex-shrink: 0;">
                                    <?= strtoupper(substr($row['user_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: var(--text-primary); font-size: 0.9rem;"><?= htmlspecialchars($row['user_name']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td data-label="Event">
                            <div>
                                <div style="font-size: 0.85rem; color: var(--text-secondary);"><?= htmlspecialchars($row['event_name']) ?></div>
                            </div>
                        </td>
                        <td style="text-align: right;" data-label="Status">
                            <span class="status-badge" style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.65rem; padding: 5px 12px; border-radius: 6px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; background: <?= strtolower($row['status']) == 'winner' ? 'rgba(251, 191, 36, 0.1)' : 'rgba(124, 58, 237, 0.1)' ?>; color: <?= strtolower($row['status']) == 'winner' ? 'var(--accent-5)' : 'var(--accent-2)' ?>; border: 1px solid <?= strtolower($row['status']) == 'winner' ? 'rgba(251, 191, 36, 0.2)' : 'rgba(124, 58, 237, 0.2)' ?>;">
                                <i class="<?= strtolower($row['status']) == 'winner' ? 'fa-solid fa-crown' : 'fa-solid fa-star' ?>"></i>
                                <?= strtoupper($row['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
