<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['userID']) || !isset($_GET['bill_id'])) {
    header('Location: monthly_payment.php');
    exit();
}

$billID = $_GET['bill_id'];

$stmt = $pdo->prepare("
    SELECT mb.* 
    FROM MonthlyBill mb
    JOIN Tenant t ON mb.TenantID = t.TenantID
    WHERE mb.BillID = ? AND t.UserID = ? AND mb.PaymentStatus = 'Pending'
");
$stmt->execute([$billID, $_SESSION['userID']]);
$bill = $stmt->fetch();

if (!$bill) {
    header('Location: monthly_payment.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            UPDATE MonthlyBill 
            SET PaymentStatus = 'Paid', 
                PaymentDate = NOW() 
            WHERE BillID = ?
        ");
        $stmt->execute([$billID]);
        
        echo "<script>alert('Payment successful!'); window.location.href='monthly_payment.php';</script>";
        exit();
    } catch (Exception $e) {
        echo "<script>alert('Payment failed!'); window.location.href='monthly_payment.php';</script>";
        exit();
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
</head>
<body>
    <div class="content">
        <div class="payment-form">
            <h2>Payment Confirmation</h2>
            <div class="payment-details">
                <p>Total Amount: ฿<?php echo number_format($bill['TotalAmount'], 2); ?></p>
                <p>Bill Period: <?php echo date('F Y', strtotime($bill['BillMonth'])); ?></p>
            </div>
            <form method="POST">
                <button type="submit" class="pay-button">
                    <i class="fas fa-credit-card"></i> Confirm Payment
                </button>
            </form>
        </div>
    </div>
</body>
</html>