<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['userID'])) {
    header('Location: ../login_user.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT 
        b.*,
        r.RoomID,
        r.RoomType,
        r.Floor,
        r.MonthlyRent,
        p.Receipt as PaymentReceipt,
        CASE 
            WHEN b.PaymentStatus = 'Cancelled' THEN 'Cancelled'
            WHEN b.CheckOutDate < CURRENT_DATE THEN 'Completed'
            WHEN b.PaymentStatus = 'Pending' THEN 'รอตรวจสอบการชำระเงิน'
            WHEN b.PaymentStatus = 'Complete' THEN 'ทำการจองเรียบร้อย'
            ELSE 'Active'
        END as BookingStatus
    FROM Booking b
    JOIN Room r ON b.RoomID = r.RoomID
    JOIN Tenant t ON b.TenantID = t.TenantID
    LEFT JOIN Payment p ON b.BookingID = p.BookingID AND p.UtilityID IS NULL
    WHERE t.UserID = ? AND b.PaymentStatus != 'Cancelled'
    ORDER BY b.BookingDate DESC
");
$stmt->execute([$_SESSION['userID']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-links">
            <div>
                <a href="room_booking.php">Rooms</a>
                <a href="booking_history.php" class="active">Booking History</a>
                <a href="lease_agreement.php">Lease Agreement</a>
                <a href="utility_bills.php">Utility Bills</a>
                <a href="monthly_payment.php">Monthly Payment</a>
                <a href="maintenance.php">Maintenance Request</a>
            </div>
            <div>
                <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="booking-container">
        <h1><i class="fas fa-history"></i> Booking History</h1>

        <?php if (empty($bookings)): ?>
            <div class="no-bookings">
                <i class="fas fa-info-circle fa-2x"></i>
                <h2>No Booking History</h2>
                <p>You haven't made any room bookings yet.</p>
                <a href="room_booking.php" class="book-button" style="display: inline-block; margin-top: 1rem;">
                    Book a Room Now
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <h2>Room <?php echo htmlspecialchars($booking['RoomType']); ?> - Floor <?php echo htmlspecialchars($booking['Floor']); ?> - Room <?php echo htmlspecialchars($booking['RoomID']); ?></h2>
                        <span class="booking-status status-<?php echo strtolower(str_replace(' ', '-', $booking['BookingStatus'])); ?>">
                            <?php echo htmlspecialchars($booking['BookingStatus']); ?>
                        </span>
                    </div>
                    <div class="booking-details">
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt"></i>
                            <strong>Booking Date:</strong><br>
                            <?php echo date('F j, Y', strtotime($booking['BookingDate'])); ?>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-check"></i>
                            <strong>Check-in Date:</strong><br>
                            <?php echo date('F j, Y', strtotime($booking['CheckInDate'])); ?>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-times"></i>
                            <strong>Check-out Date:</strong><br>
                            <?php echo date('F j, Y', strtotime($booking['CheckOutDate'])); ?>
                        </div>
                        
                        <div class="detail-item">
                            <i class="fas fa-money-bill-wave"></i>
                            <strong>Monthly Rent:</strong><br>
                            ฿<?php echo number_format($booking['MonthlyRent'], 2); ?>
                        </div>
                        
                        <!-- เพิ่มส่วนแสดงรายละเอียดการชำระเงิน -->
                        <div class="detail-item">
                            <i class="fas fa-hand-holding-usd"></i>
                            <strong>Deposit Paid:</strong><br>
                            ฿<?php echo number_format($booking['DepositPaid'], 2); ?>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-money-check-alt"></i>
                            <strong>Total Amount:</strong><br>
                            ฿<?php echo number_format($booking['TotalAmount'], 2); ?>
                        </div>
                    </div>
                    <div style="margin-top: 1rem; text-align: right; display: flex; justify-content: flex-end; gap: 10px;">
                        <?php if ($booking['PaymentStatus'] == 'Pending'): ?>
                            <form action="payment.php" method="GET" style="margin: 0;">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['BookingID']; ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-credit-card"></i> Proceed to Payment
                                </button>
                            </form>
                            <form action="cancel_booking.php" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['BookingID']; ?>">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-times"></i> Cancel Booking
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if (!empty($booking['PaymentReceipt'])): ?>
                            <button type="button" class="btn btn-info" onclick="window.open('/dormitory_app/uploads/receipts/<?php echo htmlspecialchars($booking['PaymentReceipt']); ?>', '_blank')">
                                <i class="fas fa-file-invoice"></i> ดูใบเสร็จ
                            </button>
                        <?php elseif ($booking['PaymentStatus'] == 'Paid'): ?>
                            <button type="button" class="btn btn-secondary" disabled>
                                <i class="fas fa-clock"></i> รอแอดมินเพิ่มใบเสร็จ
                            </button>
                        <?php endif; ?>
                    </div> <!-- จบ booking-card -->
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    </div>

<div class="footer">
    <p>&copy; 2025 ChokunDormitory. All rights reserved.</p>
</div>
</body>
</html>
    


    
    


    