<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user']['user_id'];
    $event_id = $_POST['event_id'] ?? '';

    if (empty($event_id)) {
        header('Location: dashboard.php?error=Missing+Event+ID');
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Check if registration exists
        $stmt = $pdo->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ?");
        $stmt->execute([$user_id, $event_id]);
        if (!$stmt->fetch()) {
            throw new Exception("Registration not found.");
        }

        // Delete registration
        $stmt = $pdo->prepare("DELETE FROM registrations WHERE user_id = ? AND event_id = ?");
        $stmt->execute([$user_id, $event_id]);

        // Decrement participant count
        $stmt = $pdo->prepare("UPDATE events SET current_participants = GREATEST(0, current_participants - 1) WHERE id = ?");
        $stmt->execute([$event_id]);

        // If it's a team event, also remove from team_members
        $stmt = $pdo->prepare("
            DELETE tm FROM team_members tm
            JOIN teams t ON tm.team_id = t.id
            WHERE tm.user_id = ? AND t.event_id = ?
        ");
        $stmt->execute([$user_id, $event_id]);

        $pdo->commit();
        header('Location: dashboard.php?msg=Successfully+unregistered+from+event');
    } catch (Exception $e) {
        $pdo->rollBack();
        header('Location: dashboard.php?error=' . urlencode($e->getMessage()));
    }
} else {
    header('Location: dashboard.php');
}
?>
