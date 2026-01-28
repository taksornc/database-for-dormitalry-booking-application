<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['booking_id'])) {
    header('Location: room_booking.php');
    exit();
}

// Get payment details
$stmt = $pdo->prepare("SELECT p.*, b.TotalAmount, r.RoomType 
                       FROM Payment p 
                       JOIN Booking b ON p.BookingID = b.BookingID 
                       JOIN Room r ON b.RoomID = r.RoomID 
                       WHERE p.BookingID = ? 
                       ORDER BY p.PaymentDate DESC LIMIT 1");
$stmt->execute([$_SESSION['booking_id']]);
$payment = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success-icon {
            color: #28a745;
            font-size: 48px;
            margin-bottom: 20px;
        }
        .payment-details {
            background-color: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: left;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
        }
        .btn-primary {
            background-color: #007bff;
        }
        .btn-success {
            background-color: #28a745;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="success-icon">✓</div>
        <h1>Payment Successful!</h1>
        <p>Your payment has been processed successfully.</p>

        <div class="payment-details">
            <h3>Payment Details</h3>
            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($payment['RoomType']); ?></p>
            <p><strong>Amount Paid:</strong> ฿<?php echo number_format($payment['AmountPaid'], 2); ?></p>
            <p><strong>Payment Date:</strong> <?php echo date('d/m/Y', strtotime($payment['PaymentDate'])); ?></p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($payment['PaymentMethod']); ?></p>
            <p><strong>Payment Type:</strong> <?php echo htmlspecialchars($payment['PaymentType']); ?></p>
            <?php if ($payment['LateFee'] > 0): ?>
            <p><strong>Late Fee:</strong> ฿<?php echo number_format($payment['LateFee'], 2); ?></p>
            <?php endif; ?>
            <p><strong>Payment Status:</strong> <span style="color: <?php echo $payment['PaymentStatus'] == 'Completed' ? '#28a745' : '#dc3545'; ?>"><?php echo htmlspecialchars($payment['PaymentStatus']); ?></span></p>
        </div>

        <div>
            <a href="room_booking.php" class="btn btn-primary">Back to Rooms</a>
            <a href="payment_history.php" class="btn btn-success">View Payment History</a>
        </div>
    </div>
    </div>

    <div class="footer">
        <p>© ChokunDormitory 2025</p>
    </div>
</body>
</html>