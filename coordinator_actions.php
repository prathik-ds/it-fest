<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'coordinator' && $_SESSION['user']['role'] !== 'admin')) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Check CSRF could be added here if we had tokens, but we'll focus on the authorization bypass first.
    
    if ($action === 'delete_registration') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            header('Location: coordinator.php?error=Invalid+Security+Token');
            exit;
        }

        $reg_id = $_POST['reg_id'];
        $event_id = $_POST['event_id'];
        
        try {
            // Validate registration belongs to this event
            $stmt = $pdo->prepare("SELECT event_id, user_id FROM registrations WHERE id = ?");
            $stmt->execute([$reg_id]);
            $reg = $stmt->fetch();
            
            if (!$reg || $reg['event_id'] != $event_id) {
                header('Location: coordinator.php?error=Invalid+Registration');
                exit;
            }

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
                
                // If team event, remove from team_members if needed (this would be good to add based on unregister_event.php)
                $stmt = $pdo->prepare("
                    DELETE tm FROM team_members tm
                    JOIN teams t ON tm.team_id = t.id
                    WHERE tm.user_id = ? AND t.event_id = ?
                ");
                $stmt->execute([$reg['user_id'], $event_id]);

                // Delete registration
                $stmt = $pdo->prepare("DELETE FROM registrations WHERE id = ?");
                $stmt->execute([$reg_id]);
                
                // Decrement count
                $stmt = $pdo->prepare("UPDATE events SET current_participants = GREATEST(0, current_participants - 1) WHERE id = ?");
                $stmt->execute([$event_id]);
                
                $pdo->commit();
                header('Location: coordinator.php?manage_event=' . $event_id . '&msg=Participant+Removed');
                exit;
            } else {
                header('Location: coordinator.php?error=Unauthorized+Action');
                exit;
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            header('Location: coordinator.php?manage_event=' . $event_id . '&error=' . urlencode($e->getMessage()));
            exit;
        }
    } else {
        $reg_id = $_POST['reg_id'];
        $event_id = $_POST['event_id'];
        $attendance = $_POST['attendance'];
        $status = $_POST['status'];

        try {
            // Validate registration belongs to this event
            $stmt = $pdo->prepare("SELECT event_id, team_id FROM registrations WHERE id = ?");
            $stmt->execute([$reg_id]);
            $reg = $stmt->fetch();
            
            if (!$reg || $reg['event_id'] != $event_id) {
                header('Location: coordinator.php?error=Invalid+Registration');
                exit;
            }

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
                if (!empty($reg['team_id'])) {
                    $stmt = $pdo->prepare("UPDATE registrations SET attendance = ?, status = ? WHERE team_id = ? AND event_id = ?");
                    $stmt->execute([$attendance, $status, $reg['team_id'], $event_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE registrations SET attendance = ?, status = ? WHERE id = ?");
                    $stmt->execute([$attendance, $status, $reg_id]);
                }
                header('Location: coordinator.php?manage_event=' . $event_id . '&msg=Record+Updated+Successfully');
                exit;
            } else {
                header('Location: coordinator.php?error=Unauthorized+Action');
                exit;
            }
        } catch (PDOException $e) {
            header('Location: coordinator.php?manage_event=' . $event_id . '&error=' . urlencode($e->getMessage()));
            exit;
        }
    }
} else {
    header('Location: coordinator.php');
    exit;
}
?>
