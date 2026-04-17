<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'coordinator' && $_SESSION['user']['role'] !== 'admin')) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete_registration') {
        $reg_id = $_POST['reg_id'];
        $event_id = $_POST['event_id'];
        
        try {
            if ($_SESSION['user']['role'] === 'admin') {
                $is_owner = true;
            } else {
                $c_id = $_SESSION['user']['user_id'];
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE id = ? AND coordinator_id = ?");
                $stmt->execute([$event_id, $c_id]);
                $is_owner = $stmt->fetchColumn() > 0;
            }

            if ($is_owner) {
                $pdo->beginTransaction();
                
                // Delete registration
                $stmt = $pdo->prepare("DELETE FROM registrations WHERE id = ?");
                $stmt->execute([$reg_id]);
                
                // Decrement count
                $stmt = $pdo->prepare("UPDATE events SET current_participants = GREATEST(0, current_participants - 1) WHERE id = ?");
                $stmt->execute([$event_id]);
                
                $pdo->commit();
                header('Location: coordinator.php?manage_event=' . $event_id . '&msg=Participant+Removed');
            } else {
                header('Location: coordinator.php?error=Unauthorized+Action');
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            header('Location: coordinator.php?manage_event=' . $event_id . '&error=' . urlencode($e->getMessage()));
        }
    } else {
        $reg_id = $_POST['reg_id'];
        $event_id = $_POST['event_id'];
        $attendance = $_POST['attendance'];
        $score = $_POST['score'];
        $status = $_POST['status'];

        try {
            // Double check ownership
            if ($_SESSION['user']['role'] === 'admin') {
                $is_owner = true;
            } else {
                $c_id = $_SESSION['user']['user_id'];
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE id = ? AND coordinator_id = ?");
                $stmt->execute([$event_id, $c_id]);
                $is_owner = $stmt->fetchColumn() > 0;
            }
            
            if ($is_owner) {
                $stmt = $pdo->prepare("UPDATE registrations SET attendance = ?, score = ?, status = ? WHERE id = ?");
                $stmt->execute([$attendance, $score, $status, $reg_id]);
                header('Location: coordinator.php?manage_event=' . $event_id . '&msg=Record+Updated+Successfully');
            } else {
                header('Location: coordinator.php?error=Unauthorized+Action');
            }
        } catch (PDOException $e) {
            header('Location: coordinator.php?manage_event=' . $event_id . '&error=' . urlencode($e->getMessage()));
        }
    }
} else {
    header('Location: coordinator.php');
}
?>
