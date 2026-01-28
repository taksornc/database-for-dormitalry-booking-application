<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['userID'])) {
    header('Location: ../login_user.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT u.*, r.RoomType, r.Floor, r.RoomID
    FROM utilityusage u
    JOIN Room r ON u.RoomID = r.RoomID
    JOIN Booking b ON r.RoomID = b.RoomID
    JOIN Tenant t ON b.TenantID = t.TenantID
    WHERE t.UserID = ? 
    AND b.CheckOutDate >= CURRENT_DATE
    ORDER BY u.BillDate DESC
");
$stmt->execute([$_SESSION['userID']]);
$utilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utility Bills</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">

</head>
<body>
    <nav class="navbar">
        <div class="nav-links">
            <div>
                <a href="room_booking.php">Rooms</a>
                <a href="booking_history.php">Booking History</a>
                <a href="lease_agreement.php">Lease Agreement </a>
                <a href="utility_bills.php" class="active">Utility Bills</a>
                <a href="monthly_payment.php">Monthly Payment</a>
                <a href="maintenance.php">Maintenance Request</a>
            </div>
            <div>
                <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="utility-container">
        <h1><i class="fas fa-file-invoice"></i> Utility Bills</h1>

        <?php if (empty($utilities)): ?>
            <div class="no-utilities">
                <i class="fas fa-info-circle fa-2x"></i>
                <h2>No Utility Bills</h2>
                <p>You don't have any utility bills yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($utilities as $bill): ?>
                <div class="utility-card">
                    <div class="utility-header">
                        <h2>Room <?php echo htmlspecialchars($bill['RoomID']); ?> - <?php echo htmlspecialchars($bill['RoomType']); ?> - Floor <?php echo htmlspecialchars($bill['Floor']); ?></h2>
                        <span><?php echo date('F Y', strtotime($bill['BillDate'])); ?></span>
                    </div>
                    <div class="utility-details">
                        <div class="detail-item">
                            <i class="fas fa-tint"></i>
                            <strong>Water Usage:</strong><br>
                            <?php echo number_format($bill['WaterUsage'], 2); ?> units<br>
                            <strong>Water Bill:</strong><br>
                            ฿<?php echo number_format($bill['WaterBill'], 2); ?>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-bolt"></i>
                            <strong>Electricity Usage:</strong><br>
                            <?php echo number_format($bill['ElectricityUsage'], 2); ?> units<br>
                            <strong>Electricity Bill:</strong><br>
                            ฿<?php echo number_format($bill['ElectricityBill'], 2); ?>
                        </div>
                        <div class="detail-item total-cost">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <strong>Total Utility Cost:</strong><br>
                            ฿<?php echo number_format($bill['TotalUtilityCost'], 2); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    </div>

    <div class="footer">
    <p>&copy; 2025 ChokunDormitory. All rights reserved.</p>
</div>
</body>
</html>