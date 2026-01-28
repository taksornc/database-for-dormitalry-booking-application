<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['userID'])) {
    header('Location: ../login_user.php');
    exit();
}

$stmt = $pdo->prepare("SELECT TenantID FROM tenant WHERE UserID = ?");
$stmt->execute([$_SESSION['userID']]);
$tenant = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT 
        b.BookingID,
        r.RoomType,
        r.MonthlyRent,
        u.UtilityID,
        u.WaterUsage,
        u.ElectricityUsage,
        u.WaterBill,
        u.ElectricityBill,
        u.TotalUtilityCost,
        u.BillDate,
        CASE 
            WHEN p.PaymentStatus IS NULL THEN 'Pending'
            ELSE p.PaymentStatus 
        END as PaymentStatus
    FROM booking b
    JOIN room r ON b.RoomID = r.RoomID
    LEFT JOIN utilityusage u ON r.RoomID = u.RoomID
    LEFT JOIN payment p ON (p.BookingID = b.BookingID AND p.UtilityID = u.UtilityID)
    WHERE b.TenantID = ? 
    AND b.CheckOutDate >= CURRENT_DATE
    ORDER BY u.BillDate DESC
");
$stmt->execute([$tenant['TenantID']]);
$monthlyCharges = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Payments</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .payment-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }
        .payment-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .payment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .payment-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .amount-box {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
        }
        .status-pending {
            color: #ffc107;
        }
        .status-paid {
            color: #28a745;
        }
        .btn-pay {
            background: #007bff;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-pay:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-links">
            <div>
                <a href="room_booking.php">Rooms</a>
                <a href="booking_history.php">Booking History</a>
                <a href="lease_agreement.php">Lease Agreement</a>
                <a href="utility_bills.php">Utility Bills</a>
                <a href="monthly_payment.php" class="active">Monthly Payment</a>
                <a href="maintenance.php">Maintenance Request</a>
            </div>
            <div>
                <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="payment-container">
        <h1><i class="fas fa-file-invoice-dollar"></i> Monthly Payments</h1>

        <?php foreach ($monthlyCharges as $charge): ?>
            <div class="payment-card">
                <div class="payment-header">
                    <h3>Room <?php echo htmlspecialchars($charge['RoomType']); ?></h3>
                    <div>
                        <?php if ($charge['BillDate']): ?>
                            <span style="margin-right: 15px; color: #666;">
                                <i class="fas fa-calendar"></i> Bill Date: <?php echo date('d/m/Y', strtotime($charge['BillDate'])); ?>
                            </span>
                        <?php endif; ?>
                        <span class="status-<?php echo strtolower($charge['PaymentStatus']); ?>">
                            <i class="fas fa-circle"></i> <?php echo $charge['PaymentStatus']; ?>
                        </span>
                    </div>
                </div>

                <div class="payment-details">
                    <div class="amount-box">
                        <strong>Monthly Rent:</strong>
                        <div>฿<?php echo number_format($charge['MonthlyRent'], 2); ?></div>
                    </div>

                    <?php if ($charge['UtilityID']): ?>
                    <div class="amount-box">
                        <strong>Water Usage:</strong>
                        <div><?php echo number_format($charge['WaterUsage'], 2); ?> Units</div>
                        <strong>Water Bill:</strong>
                        <div>฿<?php echo number_format($charge['WaterBill'], 2); ?></div>
                    </div>
                    <div class="amount-box">
                        <strong>Electricity Usage:</strong>
                        <div><?php echo number_format($charge['ElectricityUsage'], 2); ?> Units</div>
                        <strong>Electricity Bill:</strong>
                        <div>฿<?php echo number_format($charge['ElectricityBill'], 2); ?></div>
                    </div>
                    <?php endif; ?>

                    <div class="amount-box">
                        <strong>Total Amount:</strong>
                        <div>฿<?php echo number_format($charge['MonthlyRent'] + ($charge['TotalUtilityCost'] ?? 0), 2); ?></div>
                    </div>
                </div>

                <?php if ($charge['PaymentStatus'] !== 'Paid'): ?>
                    <div style="margin-top: 15px;">
                        <a href="payment_process.php?booking_id=<?php echo $charge['BookingID']; ?>&utility_id=<?php echo $charge['UtilityID']; ?>" 
                           class="btn-pay">
                            <i class="fas fa-credit-card"></i> Pay Now
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>