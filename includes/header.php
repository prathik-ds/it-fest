<?php
session_start();
require_once __DIR__ . '/../config/db.php';

function isActive($page) {
    return basename($_SERVER['PHP_SELF']) == $page ? 'active' : '';
}

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$current_page = basename($_SERVER['PHP_SELF']);

// Define pages that should use the Sidebar Panel layout
$panel_pages = ['coordinator.php', 'admin.php', 'dashboard.php'];
$is_panel = in_array($current_page, $panel_pages);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusFest | Experience Innovation</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="favicon.ico">
    <style>
        /* Inline critical CSS for stars */
        .stars { position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: -1; }
        .star { position: absolute; background: white; border-radius: 50%; opacity: 0.5; }
    </style>
</head>
<body class="<?= $is_panel ? 'has-sidebar' : '' ?>">
    <div class="space-bg">
        <div id="stars-container" class="stars"></div>
    </div>

    <?php if ($is_panel): ?>
        <!-- Panel Layout: Sidebar + Top Bar -->
        <aside class="sidebar">
            <a href="index.php" class="sidebar-logo">
                <div class="logo-icon">N</div>
                <div class="logo-text">NexusFest</div>
            </a>

            <ul class="sidebar-nav">
                <li>
                    <a href="index.php" class="sidebar-link <?= isActive('index.php') ?>">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                </li>
                <?php if ($user['role'] === 'coordinator'): ?>
                    <li>
                        <a href="coordinator.php" class="sidebar-link <?= isActive('coordinator.php') ?>">
                            <i class="fas fa-calendar-check"></i> Assigned Events
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($user['role'] === 'admin'): ?>
                    <li>
                        <a href="admin.php" class="sidebar-link <?= isActive('admin.php') ?>">
                            <i class="fas fa-user-shield"></i> Admin Panel
                        </a>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="dashboard.php" class="sidebar-link <?= isActive('dashboard.php') ?>">
                        <i class="fas fa-th-large"></i> Control Center
                    </a>
                </li>
                <li>
                    <a href="logout.php" class="sidebar-link" style="color: #ef4444; margin-top: auto;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </aside>

        <header class="top-bar">
            <div class="search-bar">
                <i class="fas fa-search" style="color: var(--text-secondary);"></i>
                <input type="text" placeholder="Search your events...">
            </div>

            <div class="user-profile">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: #2a2a3a; display: flex; align-items: center; justify-content: center; border: 1px solid var(--glass-border);">
                    <i class="fas fa-user"></i>
                </div>
                <i class="fas fa-bell" style="color: var(--text-secondary); margin-left: 15px;"></i>
            </div>
        </header>
    <?php else: ?>
        <!-- Public Layout: Top Navigation -->
        <nav class="public-nav">
            <a href="index.php" class="nav-logo">
                <div class="logo-icon" style="width: 32px; height: 32px; font-size: 0.9rem;">N</div>
                NexusFest
            </a>

            <ul class="nav-links">
                <li><a href="index.php" class="nav-link <?= isActive('index.php') ?>">Home</a></li>
                <li><a href="events.php" class="nav-link <?= isActive('events.php') ?>">Events</a></li>
                <li><a href="leaderboard.php" class="nav-link <?= isActive('leaderboard.php') ?>">Leaderboard</a></li>
                <?php if ($user): ?>
                    <li><a href="dashboard.php" class="btn-primary" style="padding: 8px 20px; font-size: 0.85rem;">Dashboard</a></li>
                    <li><a href="logout.php" class="nav-link" style="color: #ef4444;">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-link <?= isActive('login.php') ?>">Login</a></li>
                    <li><a href="register.php" class="btn-primary" style="padding: 8px 20px; font-size: 0.85rem;">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>

    <main class="main-content">
        <div class="container">
