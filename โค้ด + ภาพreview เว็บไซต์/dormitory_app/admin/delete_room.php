<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['adminID'])) {
    header('Location: ../login_admin.php');
    exit();
}

if (isset($_GET['id'])) {
    $roomID = $_GET['id'];
    
    try {
        $pdo->beginTransaction();

                    // Check if the room exists
        $stmt = $pdo->prepare("SELECT RoomID FROM Room WHERE RoomID = ?");
        $stmt->execute([$roomID]);
        if (!$stmt->fetch()) {
            throw new Exception('Room not found');
        }

        // Check if the room is currently booked
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Booking WHERE RoomID = ? AND PaymentStatus = 'Paid' AND CheckOutDate >= CURRENT_DATE");
        $stmt->execute([$roomID]);
        $bookingCount = $stmt->fetchColumn();
        
        if ($bookingCount > 0) {
            throw new Exception('Room is currently booked');
        }

        // Check for any pending payments
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Booking b JOIN Payment p ON b.BookingID = p.BookingID WHERE b.RoomID = ? AND PaymentStatus = 'Pending'");
        $stmt->execute([$roomID]);
        $pendingPayments = $stmt->fetchColumn();

        if ($pendingPayments > 0) {
            throw new Exception('Room has pending payments');
        }

        // Delete utility usage records first
        $stmt = $pdo->prepare("DELETE FROM UtilityUsage WHERE RoomID = ?");
        $stmt->execute([$roomID]);

        // Delete related booking records
        $stmt = $pdo->prepare("DELETE FROM Booking WHERE RoomID = ? AND PaymentStatus != 'Paid'");
        $stmt->execute([$roomID]);

        // Delete room from database
        $stmt = $pdo->prepare("DELETE FROM Room WHERE RoomID = ?");
        $stmt->execute([$roomID]);
        

        
        $pdo->commit();
        echo "<script>alert('Room deleted successfully!'); window.location.href='room_manage.php';</script>";

    } catch (Exception $e) {
        $pdo->rollBack();
        $errorMessage = 'Cannot delete room: ' . $e->getMessage();
        echo "<script>alert('" . addslashes($errorMessage) . "'); window.location.href='room_manage.php';</script>";
    }
} else {
    header('Location: room_manage.php');
    exit();
}
?>