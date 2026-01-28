<?php
session_start();
if (!isset($_SESSION['payment_success'])) {
    header('Location: monthly_payment.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .success-container {
            max-width: 600px;
            margin: 100px auto;
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .success-icon {
            color: #28a745;
            font-size: 64px;
            margin-bottom: 20px;
        }
        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .btn-back:hover {
            background: #0056b3;
        }
        .payment-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <i class="fas fa-check-circle success-icon"></i>
        <h1>Payment Successful!</h1>
        <div class="payment-details">
            <p>Your payment has been processed successfully!</p>
            <p>Status: <span style="color: #28a745;">Processing</span></p>
            <p>Date: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
        <a href="monthly_payment.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Monthly Payments
        </a>
    </div>
</body>
</html>

<?php
// Clear the success message
unset($_SESSION['payment_success']);
?>