<?php 
include 'includes/header.php'; 

// Fetch Top Performers across all events where score > 0
$stmt = $pdo->query("SELECT r.user_id, u.name as user_name, u.college, e.name as event_name, r.score, r.status FROM registrations r JOIN users u ON r.user_id = u.user_id JOIN events e ON r.event_id = e.id WHERE r.score > 0 OR r.status != 'registered' ORDER BY r.score DESC, r.status DESC LIMIT 20");
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

<!-- Leaderboard Card -->
<div class="glass" style="max-width: 1000px; margin: 0 auto; padding: 0; overflow: hidden; border-color: rgba(124, 58, 237, 0.12);">
    <?php if(empty($topScores)): ?>
        <div style="text-align: center; padding: 80px 20px;">
            <div style="width: 72px; height: 72px; background: rgba(124, 58, 237, 0.08); border-radius: 20px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                <i class="fa-solid fa-hourglass-half" style="font-size: 1.8rem; color: var(--accent-2);"></i>
            </div>
            <p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 8px;">Leaderboard is warming up...</p>
            <p style="color: var(--text-dim); font-size: 0.82rem;">Check back once the competitions begin!</p>
        </div>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border);">
                    <th style="padding: 18px 24px; font-family: 'Space Grotesk', sans-serif; font-size: 0.72rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700;">Rank</th>
                    <th style="padding: 18px 24px; font-family: 'Space Grotesk', sans-serif; font-size: 0.72rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700;">Participant</th>
                    <th style="padding: 18px 24px; font-family: 'Space Grotesk', sans-serif; font-size: 0.72rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700;">Event</th>
                    <th style="padding: 18px 24px; font-family: 'Space Grotesk', sans-serif; font-size: 0.72rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; text-align: right;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $rank = 1; foreach($topScores as $row): ?>
                    <tr style="border-bottom: 1px solid var(--border); transition: background 0.2s; <?= $rank <= 3 ? 'background: rgba(124, 58, 237, 0.03);' : '' ?>" onmouseover="this.style.background='rgba(0, 212, 255, 0.03)'" onmouseout="this.style.background='<?= $rank <= 3 ? 'rgba(124, 58, 237, 0.03)' : 'transparent' ?>'">
                        <td style="padding: 20px 24px;" data-label="Rank">
                            <?php if ($rank == 1): ?>
                                <span style="font-size: 1.3rem;">🥇</span>
                            <?php elseif ($rank == 2): ?>
                                <span style="font-size: 1.3rem;">🥈</span>
                            <?php elseif ($rank == 3): ?>
                                <span style="font-size: 1.3rem;">🥉</span>
                            <?php else: ?>
                                <span style="font-family: 'JetBrains Mono', monospace; font-size: 0.9rem; font-weight: 700; color: var(--text-muted);">#<?= $rank ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 20px 24px;" data-label="Participant">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 38px; height: 38px; border-radius: 12px; background: <?= $rank <= 3 ? 'var(--grad-primary)' : 'rgba(100, 130, 200, 0.1)' ?>; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; color: white; flex-shrink: 0;">
                                    <?= strtoupper(substr($row['user_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: var(--text-primary); font-size: 0.95rem;"><?= htmlspecialchars($row['user_name']) ?></div>
                                    <div style="font-size: 0.72rem; color: var(--text-dim);"><?= htmlspecialchars($row['college']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 20px 24px;" data-label="Event">
                            <div>
                                <div style="font-size: 0.88rem; color: var(--text-secondary);"><?= htmlspecialchars($row['event_name']) ?></div>
                            </div>
                        </td>
                        <td style="padding: 20px 24px; text-align: right;" data-label="Status">
                            <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.65rem; padding: 5px 12px; border-radius: 6px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; background: <?= $row['status'] == 'winner' ? 'rgba(251, 191, 36, 0.1)' : 'rgba(124, 58, 237, 0.1)' ?>; color: <?= $row['status'] == 'winner' ? 'var(--accent-5)' : 'var(--accent-2)' ?>; border: 1px solid <?= $row['status'] == 'winner' ? 'rgba(251, 191, 36, 0.2)' : 'rgba(124, 58, 237, 0.2)' ?>;">
                                <i class="<?= $row['status'] == 'winner' ? 'fa-solid fa-crown' : 'fa-solid fa-star' ?>"></i>
                                <?= $row['status'] ?>
                            </span>
                        </td>
                    </tr>
                <?php $rank++; endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
