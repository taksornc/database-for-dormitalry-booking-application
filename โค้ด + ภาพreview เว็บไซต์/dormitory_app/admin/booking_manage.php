<?php
session_start();
require_once('../db.php');
if (!isset($_SESSION['adminID'])) {
    header('Location: ../login_admin.php');
    exit();
}

function updateRoomStatus($pdo, $roomId, $status) {
    $stmt = $pdo->prepare("UPDATE Room SET RoomStatus = ? WHERE RoomID = ?");
    $stmt->execute([$status, $roomId]);
}

if (isset($_GET['id'])) {
    try {
        $pdo->beginTransaction();

        $bookingId = $_GET['id'];

        $stmt = $pdo->prepare("DELETE FROM Payment WHERE BookingID = ?");
        $stmt->execute([$bookingId]);

        $stmt = $pdo->prepare("UPDATE lease_agreement SET BookingID = NULL WHERE BookingID = ?");
        $stmt->execute([$bookingId]);

        $stmt = $pdo->prepare("SELECT RoomID FROM Booking WHERE BookingID = ?");
        $stmt->execute([$bookingId]);
        $room = $stmt->fetch();

        $stmt = $pdo->prepare("DELETE FROM Booking WHERE BookingID = ?");
        $stmt->execute([$bookingId]);

        if ($room) {
            $stmt = $pdo->prepare("UPDATE Room SET RoomStatus = 'available' WHERE RoomID = ?");
            $stmt->execute([$room['RoomID']]);
        }

        $pdo->commit();
        echo "<script>alert('Booking deleted successfully!'); window.location.href='booking_manage.php';</script>";
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Error deleting booking: " . htmlspecialchars($e->getMessage()) . "'); window.location.href='booking_manage.php';</script>";
    }
}

$stmt = $pdo->prepare("
    SELECT b.BookingID, b.RoomID, b.TenantID, b.BookingDate, 
           b.CheckInDate, b.CheckOutDate, b.DepositPaid, r.MonthlyRent as AdvancePaid,
           (COALESCE(b.DepositPaid, 0) + COALESCE(r.MonthlyRent, 0)) as TotalAmount,
           l.LeaseID,
           CASE 
               WHEN COALESCE(SUM(p.AmountPaid), 0) >= (COALESCE(b.DepositPaid, 0) + COALESCE(r.MonthlyRent, 0)) THEN 'Paid'
               WHEN COALESCE(SUM(p.AmountPaid), 0) > 0 THEN 'Partially Paid'
               ELSE 'Unpaid'
           END as PaymentStatus
    FROM Booking b
    LEFT JOIN Payment p ON b.BookingID = p.BookingID
    LEFT JOIN Room r ON b.RoomID = r.RoomID
    LEFT JOIN lease_agreement l ON l.BookingID = b.BookingID
    GROUP BY b.BookingID
    ORDER BY b.BookingID DESC");
$stmt->execute();
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="tenant_manage.php">Tenants</a>
        <a href="room_manage.php">Rooms</a>
        <a href="booking_manage.php">Bookings</a>
        <a href="lease_manage.php">Lease Agreements</a>
        <a href="payment_manage.php">Payments</a>
        <a href="utility_manage.php">Utility Usage</a>
        <a href="maintenance_manage.php">Maintenance</a>
        <a href="staff_manage.php">Staff</a>
        <a href="contact_manage.php">Messages</a>
        <a href="logout.php">Logout</a>

    </div>

    <!-- Booking Management Content -->
    <div class="content">
        <h1>Manage Bookings</h1>
        <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; margin-top: 20px;">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Room ID</th>
                    <th>Tenant ID</th>
                    <th>Booking Date</th>
                    <th>Check-In Date</th>
                    <th>Check-Out Date</th>
                    <th>Deposit Paid</th>
                    <th>Advance Paid</th>
                    <th>Total Amount</th>
                    <th>Payment Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?php echo htmlspecialchars($booking['BookingID']); ?></td>
                    <td><?php echo htmlspecialchars($booking['RoomID']); ?></td>
                    <td><?php echo htmlspecialchars($booking['TenantID']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($booking['BookingDate'])); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($booking['CheckInDate'])); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($booking['CheckOutDate'])); ?></td>
                    <td>฿<?php echo number_format($booking['DepositPaid'], 2); ?></td>
                    <td>฿<?php echo number_format($booking['AdvancePaid'], 2); ?></td>
                    <td>฿<?php echo number_format($booking['TotalAmount'], 2); ?></td>
                    <td>
                        <span class="status-badge <?php echo strtolower($booking['PaymentStatus']); ?>">
                            <?php echo $booking['PaymentStatus']; ?>
                        </span>
                        <!-- ลบปุ่มยืนยันการชำระเงินออก -->
                    </td>
                    <td> <!-- Added Actions buttons -->
                        <a href="edit_booking.php?id=<?php echo $booking['BookingID']; ?>" class="action-btn">Edit</a>
                        <a href="booking_manage.php?id=<?php echo $booking['BookingID']; ?>" 
                           class="action-btn"
                           onclick="return confirm('Are you sure you want to delete this booking?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <!-- Add this CSS to your existing styles -->
        <style>
            .status-badge {
                padding: 5px 10px;
                border-radius: 15px;
                font-size: 0.9em;
                font-weight: bold;
            }
            
            .paid {
                background-color: #28a745;
                color: white;
            }
            
            .partially-paid {
                background-color: #ffc107;
                color: black;
            }
            
            .unpaid {
                background-color: #dc3545;
                color: white;
            }
        </style>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>© ChokunDormitory 2025</p>
    </div>
</body>
</html>
