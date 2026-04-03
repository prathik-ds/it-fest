<?php
session_start();
require_once __DIR__ . '/../config/db.php';

function isActive($page) {
    return basename($_SERVER['PHP_SELF']) == $page ? 'active' : '';
}

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SYNERGY | Where Commerce Meets Code</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="icon" href="favicon.ico">
</head>
<body>
    <div class="cyber-grid"></div>
    <div class="glow-bg"></div>
    <div class="scanline"></div>

    <nav>
        <a href="index.php" class="logo orbitron neon-text-blue">SYNERGY</a>
        <ul class="nav-links">
            <li><a href="index.php" class="<?= isActive('index.php') ?>">Home</a></li>
            <li><a href="events.php" class="<?= isActive('events.php') ?>">Events</a></li>
            <li><a href="leaderboard.php" class="<?= isActive('leaderboard.php') ?>">Leaderboard</a></li>
            <?php if ($user): ?>
                <li><a href="dashboard.php" class="<?= isActive('dashboard.php') ?>">Dashboard</a></li>
                <?php if ($user['role'] === 'coordinator'): ?>
                    <li><a href="coordinator.php" class="neon-text-purple <?= isActive('coordinator.php') ?>">Coordinator</a></li>
                <?php endif; ?>
                <?php if ($user['role'] === 'admin'): ?>
                    <li><a href="admin.php" class="neon-text-pink <?= isActive('admin.php') ?>">Admin</a></li>
                <?php endif; ?>
                <li><a href="logout.php" style="color: var(--neon-pink)">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php" class="<?= isActive('login.php') ?>">Login</a></li>
                <li><a href="register.php" class="btn-neon" style="padding: 10px 20px; font-size: 0.8rem">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="container">
