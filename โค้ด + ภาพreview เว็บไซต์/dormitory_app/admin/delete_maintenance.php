<?php
session_start();
require_once('../db.php');
if (!isset($_SESSION['adminID'])) {
    header('Location: ../login_admin.php');
    exit();
}

if (isset($_GET['id'])) {
    $requestID = $_GET['id'];

    try {
        // Check if the maintenance request exists
        $checkStmt = $pdo->prepare("SELECT RequestID FROM MaintenanceRequest WHERE RequestID = ?");
        $checkStmt->execute([$requestID]);
        
        if ($checkStmt->rowCount() === 0) {
            $_SESSION['error'] = 'Maintenance request not found.';
            header('Location: maintenance_manage.php');
            exit();
        }

        // Delete the maintenance request
        $stmt = $pdo->prepare("DELETE FROM MaintenanceRequest WHERE RequestID = ?");
        $stmt->execute([$requestID]);

        $_SESSION['success'] = 'Maintenance request deleted successfully.';
        header('Location: maintenance_manage.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error deleting maintenance request. Please try again.';
        header('Location: maintenance_manage.php');
        exit();
    }
} else {
    $_SESSION['error'] = 'Invalid Request ID';
    header('Location: maintenance_manage.php');
    exit();
}
?>