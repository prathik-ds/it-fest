<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'coordinator') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reg_id = $_POST['reg_id'];
    $event_id = $_POST['event_id'];
    $attendance = $_POST['attendance'];
    $score = $_POST['score'];
    $status = $_POST['status'];

    try {
        // Double check ownership
        $c_id = $_SESSION['user']['user_id'];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE id = ? AND coordinator_id = ?");
        $stmt->execute([$event_id, $c_id]);
        
        if ($stmt->fetchColumn() > 0) {
            $stmt = $pdo->prepare("UPDATE registrations SET attendance = ?, score = ?, status = ? WHERE id = ?");
            $stmt->execute([$attendance, $score, $status, $reg_id]);
            header('Location: coordinator.php?manage_event=' . $event_id . '&msg=Record+Updated+Successfully');
        } else {
            header('Location: coordinator.php?error=Unauthorized+Action');
        }
    } catch (PDOException $e) {
        header('Location: coordinator.php?manage_event=' . $event_id . '&error=' . urlencode($e->getMessage()));
    }
} else {
    header('Location: coordinator.php');
}
?>
