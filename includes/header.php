<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

function isActive($page) {
    return basename($_SERVER['PHP_SELF']) == $page ? 'active' : '';
}

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$current_page = basename($_SERVER['PHP_SELF']);
$is_dashboard_page = in_array($current_page, ['dashboard.php', 'coordinator.php', 'admin.php']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexusFest | SYNERGY</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="icon" href="favicon.ico">
</head>
<body>

    <?php if ($is_dashboard_page): ?>
        <!-- MODERN DASHBOARD LAYOUT -->
        <div class="bg-gradient-dash"></div>
        <div class="app-wrapper">
            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="sidebar-brand">
                    <div class="brand-icon-dash">C</div>
                    <div class="brand-name" style="font-family: 'Outfit';">
                        <h2 style="font-size: 1.1rem; font-weight: 700; letter-spacing: -0.5px;">NexusFest</h2>
                        <p style="font-size: 0.7rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px;">EVENT COORDINATOR</p>
                    </div>
                </div>

                <ul class="sidebar-menu">
                    <!-- MAIN SECTION -->
                    <div class="menu-label-dash">MAIN</div>
                    <li class="menu-item">
                        <a href="index.php" class="menu-link-dash">
                            <i class="fa-solid fa-house"></i>
                            <span>Home Page</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="dashboard.php" class="menu-link-dash <?= isActive('dashboard.php') ?>">
                            <i class="fa-solid fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="events.php" class="menu-link-dash <?= isActive('events.php') ?>">
                            <i class="fa-solid fa-trophy"></i>
                            <span>Explore Events</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="leaderboard.php" class="menu-link-dash <?= isActive('leaderboard.php') ?>">
                            <i class="fa-solid fa-ranking-star"></i>
                            <span>Leaderboard</span>
                        </a>
                    </li>

                    <?php if ($user): ?>
                        <?php if ($user['role'] === 'coordinator'): ?>
                            <!-- COORDINATOR PANEL -->
                            <div class="menu-label-dash">COORDINATOR PANEL</div>
                            <li class="menu-item">
                                <a href="coordinator.php" class="menu-link-dash <?= isActive('coordinator.php') ?>">
                                    <i class="fa-solid fa-calendar-check"></i>
                                    <span>Assigned Events</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a href="coordinator.php#scanner" class="menu-link-dash">
                                    <i class="fa-solid fa-qrcode"></i>
                                    <span>Scan Entry</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if ($user['role'] === 'admin'): ?>
                            <!-- ADMINISTRATION PANEL -->
                            <div class="menu-label-dash">ADMIN PANEL</div>
                            <li class="menu-item">
                                <a href="admin.php" class="menu-link-dash <?= isActive('admin.php') ?>">
                                    <i class="fa-solid fa-gauge-high"></i>
                                    <span>Dashboard / Analytics</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a href="admin.php?tab=events" class="menu-link-dash">
                                    <i class="fa-solid fa-database"></i>
                                    <span>Manage Tracks</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a href="admin.php?tab=users" class="menu-link-dash">
                                    <i class="fa-solid fa-users-gear"></i>
                                    <span>User Management</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- ACCOUNT SECTION -->
                    <div class="menu-label-dash">ACCOUNT</div>
                    <li class="menu-item">
                        <a href="dashboard.php" class="menu-link-dash">
                            <i class="fa-solid fa-user-circle"></i>
                            <span>My Profile</span>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="logout.php" class="menu-link-dash" style="color: var(--danger)">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </aside>

            <!-- Main Content Section -->
            <main class="main-content-dash">
                <header class="top-header-dash">
                    <div class="header-actions" style="display: flex; align-items: center; gap: 20px;">
                        <a href="coordinator.php" class="btn-coord" style="background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2); color: var(--primary); padding: 8px 16px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; text-decoration: none;">COORD ACCESS</a>
                        <div class="notification-bell" style="font-size: 1.2rem; color: var(--text-muted); cursor: pointer;">
                            <i class="fa-regular fa-bell"></i>
                        </div>
                    </div>
                </header>
                <div class="content-body" style="flex: 1;">
    <?php else: ?>
        <!-- LEGACY NEON LAYOUT -->
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
    <?php endif; ?>


