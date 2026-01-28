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

if (!isset($_GET['booking_id']) || !isset($_GET['utility_id'])) {
    header('Location: monthly_payment.php');
    exit();
}

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
        u.BillDate
    FROM booking b
    JOIN room r ON b.RoomID = r.RoomID
    LEFT JOIN utilityusage u ON r.RoomID = u.RoomID
    WHERE b.BookingID = ? AND (u.UtilityID = ? OR ? IS NULL)
    AND b.TenantID = ?
");
$stmt->execute([$_GET['booking_id'], $_GET['utility_id'], $_GET['utility_id'], $tenant['TenantID']]);
$payment = $stmt->fetch();

// Set timezone to Thailand
date_default_timezone_set('Asia/Bangkok');

$lateFee = 0;
$lastDayOfMonth = date('Y-m-t', strtotime($payment['BillDate']));
$today = date('Y-m-d');

if (strtotime($today) > strtotime($lastDayOfMonth)) {
    $daysLate = min(floor((strtotime($today) - strtotime($lastDayOfMonth)) / (60 * 60 * 24)), 40); 
    $lateFee = $daysLate * 50;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $totalAmount = $payment['MonthlyRent'] + ($payment['TotalUtilityCost'] ?? 0) + $lateFee;

        $stmt = $pdo->prepare("
            INSERT INTO payment (
                TenantID,
                BookingID,
                UtilityID,
                PaymentType,
                AmountPaid,
                PaymentDate,
                PaymentMethod,
                PaymentStatus,
                LateFee
            ) VALUES (?, ?, ?, 'Monthly', ?, CURRENT_DATE(), ?, 'Completed', ?)
        ");

        $stmt->execute([
            $tenant['TenantID'],
            $payment['BookingID'],
            $payment['UtilityID'],
            $totalAmount,
            $_POST['payment_method'],
            $lateFee
        ]);

        if ($payment['BookingID']) {
            $stmt = $pdo->prepare("UPDATE booking SET PaymentStatus = 'Completed' WHERE BookingID = ?");
            $stmt->execute([$payment['BookingID']]);
        }

        $paymentID = $pdo->lastInsertId();
        
        $pdo->commit();
        $_SESSION['payment_success'] = "Your payment has been processed successfully!";
        header('Location: payment_success.php');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error processing payment: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Payment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .payment-form {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .payment-summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="payment-form">
        <h2><i class="fas fa-credit-card"></i> Process Payment</h2>

        <div class="payment-summary">
            <h3>Payment Summary</h3>
            <p><strong>Room:</strong> <?php echo htmlspecialchars($payment['RoomType']); ?></p>
            <p><strong>Monthly Rent:</strong> ฿<?php echo number_format($payment['MonthlyRent'], 2); ?></p>
            <?php if ($payment['UtilityID']): ?>
                <p><strong>Water Usage:</strong> <?php echo number_format($payment['WaterUsage'], 2); ?> Units</p>
                <p><strong>Water Bill:</strong> ฿<?php echo number_format($payment['WaterBill'], 2); ?></p>
                <p><strong>Electricity Usage:</strong> <?php echo number_format($payment['ElectricityUsage'], 2); ?> Units</p>
                <p><strong>Electricity Bill:</strong> ฿<?php echo number_format($payment['ElectricityBill'], 2); ?></p>
            <?php endif; ?>
            <?php if ($lateFee > 0): ?>
                <p><strong>Late Fee:</strong> ฿<?php echo number_format($lateFee, 2); ?></p>
                <p><small class="text-danger">*Late payment charge: ฿50 per day after <?php echo date('F j, Y', strtotime($lastDayOfMonth)); ?></small></p>
            <?php endif; ?>
            <p><strong>Total Amount:</strong> ฿<?php echo number_format($payment['MonthlyRent'] + ($payment['TotalUtilityCost'] ?? 0) + $lateFee, 2); ?></p>
        </div>

        <form method="POST">
            <div class="form-group">
                <label>Payment Method:</label>
                <select name="payment_method" id="payment_method" required onchange="togglePaymentDetails()">
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="PromptPay">PromptPay</option>
                </select>
            </div>

            <div id="promptpay_details" style="display: none;" class="payment-details">
                <div class="qr-container" style="text-align: center; margin: 20px 0;">
                    <h4>Scan QR Code to Pay</h4>
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR1yVPMpUmn2dDPz8jzd2wDYTX3K-UN6lJ5Rw&s" alt="PromptPay QR Code" style="max-width: 200px;">
                    <p>PromptPay ID: 123-456-789</p>
                    <p>Account Name: Dormitory</p>
                    <p>Amount: ฿<?php echo number_format($payment['MonthlyRent'] + ($payment['TotalUtilityCost'] ?? 0), 2); ?></p>
                </div>
            </div>

            <button type="submit" class="btn-pay">
                <i class="fas fa-lock"></i> Confirm Payment
            </button>
        </form>
    </div>

    <script>
        function togglePaymentDetails() {
            const paymentMethod = document.getElementById('payment_method').value;
            const promptpayDetails = document.getElementById('promptpay_details');
            
            if (paymentMethod === 'PromptPay') {
                promptpayDetails.style.display = 'block';
            } else {
                promptpayDetails.style.display = 'none';
            }
        }
    </script>
</body>
</html>