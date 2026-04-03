<?php 
include 'includes/header.php'; 

// Fetch Top Performers across all events where score > 0
$stmt = $pdo->query("SELECT r.user_id, u.name as user_name, u.college, e.name as event_name, r.score, r.status FROM registrations r JOIN users u ON r.user_id = u.user_id JOIN events e ON r.event_id = e.id WHERE r.score > 0 OR r.status != 'registered' ORDER BY r.score DESC, r.status DESC LIMIT 20");
$topScores = $stmt->fetchAll();
?>

<div style="margin-bottom: 50px; text-align: center;">
    <h1 class="orbitron neon-text-purple" style="font-size: 3rem;">HALL OF FAME</h1>
    <p style="color: #666; margin-top: 10px; letter-spacing: 2px;">CELEBRATING THE ELITE OF SYNERGY 2026</p>
</div>

<div class="glass neon-border-blue" style="max-width: 1000px; margin: 0 auto; padding: 40px; border-color: var(--neon-purple);">
    <?php if(empty($topScores)): ?>
        <div style="text-align: center; padding: 60px 0;">
            <p style="color: #555; font-size: 1.1rem;">The leaderboard is currently being calculated.</p>
            <p style="color: #444; font-size: 0.8rem; margin-top: 10px;">Check back once competitions begin!</p>
        </div>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid rgba(188, 19, 254, 0.2);">
                    <th style="padding: 15px; font-family: 'Orbitron'; font-size: 0.8rem; color: #777;">RANK</th>
                    <th style="padding: 15px; font-family: 'Orbitron'; font-size: 0.8rem; color: #777;">PARTICIPANT</th>
                    <th style="padding: 15px; font-family: 'Orbitron'; font-size: 0.8rem; color: #777;">EVENT</th>
                    <th style="padding: 15px; font-family: 'Orbitron'; font-size: 0.8rem; color: #777;">SCORE</th>
                </tr>
            </thead>
            <tbody>
                <?php $rank = 1; foreach($topScores as $row): ?>
                    <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.03); background: <?= $rank <= 3 ? 'rgba(188, 19, 254, 0.03)' : 'transparent' ?>;">
                        <td style="padding: 20px;">
                            <span class="orbitron <?= $rank <= 3 ? 'neon-text-pink' : 'neon-text-blue' ?>" style="font-size: 1.2rem; font-weight: 800;">
                                #<?= $rank ?>
                            </span>
                        </td>
                        <td style="padding: 20px;">
                            <span style="font-weight: 700; color: #fff; font-size: 1.1rem;"><?= htmlspecialchars($row['user_name']) ?></span><br>
                            <span style="font-size: 0.75rem; color: #666;"><?= htmlspecialchars($row['college']) ?></span>
                        </td>
                        <td style="padding: 20px;">
                            <span style="font-size: 0.85rem; color: #aaa;"><?= htmlspecialchars($row['event_name']) ?></span><br>
                            <span style="font-size: 0.7rem; color: <?= $row['status'] == 'winner' ? 'var(--neon-blue)' : 'var(--neon-purple)' ?>; text-transform: uppercase;">
                                <?= $row['status'] ?>
                            </span>
                        </td>
                        <td style="padding: 20px;">
                            <span class="orbitron neon-text-blue" style="font-size: 1.1rem; font-weight: 600;">
                                <?= $row['score'] ?>
                            </span>
                        </td>
                    </tr>
                <?php $rank++; endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
