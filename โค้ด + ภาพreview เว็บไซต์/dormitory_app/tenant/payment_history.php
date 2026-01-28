<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['userID'])) {
    header('Location: ../login_user.php');
    exit();
}

$userID = $_SESSION['userID'];

$stmt = $pdo->prepare("
    SELECT p.PaymentID, p.AmountPaid, p.PaymentDate, p.PaymentMethod, p.Receipt, p.PaymentType,
           b.RoomID, b.CheckInDate, b.CheckOutDate,
           t.TenantID
    FROM Payment p
    LEFT JOIN Booking b ON p.BookingID = b.BookingID
    LEFT JOIN Tenant t ON b.TenantID = t.TenantID
    WHERE t.UserID = ?
    ORDER BY p.PaymentDate DESC");

try {
    $stmt->execute([$userID]);
    $payments = $stmt->fetchAll();
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .payment-history-container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .payment-table th, .payment-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .payment-table th {
            background-color: #007bff;
            color: white;
        }
        .payment-table tr:hover {
            background-color: #f1f1f1;
        }
        .receipt-link {
            color: #007bff;
            text-decoration: none;
        }
        .receipt-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="payment-history-container">
        <h1>Payment History</h1>
        <table class="payment-table">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Amount Paid</th>
                    <th>Payment Date</th>
                    <th>Payment Method</th>
                    <th>Payment Type</th>
                    <th>Receipt</th>
                    <th>Room ID</th>
                    <th>Check-In Date</th>
                    <th>Check-Out Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?php echo htmlspecialchars($payment['PaymentID']); ?></td>
                    <td>฿<?php echo number_format($payment['AmountPaid'], 2); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($payment['PaymentDate'])); ?></td>
                    <td><?php echo htmlspecialchars($payment['PaymentMethod']); ?></td>
                    <td><?php echo htmlspecialchars($payment['PaymentType']); ?></td>
                    <td>
                        <?php if ($payment['Receipt']): ?>
                            <a href="../uploads/receipts/<?php echo $payment['Receipt']; ?>" target="_blank" class="receipt-link">View Receipt</a>
                        <?php else: ?>
                            No Receipt
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($payment['RoomID']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($payment['CheckInDate'])); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($payment['CheckOutDate'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>