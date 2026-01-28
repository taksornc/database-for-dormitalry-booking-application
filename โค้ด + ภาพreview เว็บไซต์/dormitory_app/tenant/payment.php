<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['tenant_id']) || !isset($_SESSION['booking_id'])) {
    header('Location: room_booking.php');
    exit();
}

$stmt = $pdo->prepare("SELECT b.*, r.RoomType, r.MonthlyRent 
                       FROM Booking b 
                       JOIN Room r ON b.RoomID = r.RoomID 
                       WHERE b.BookingID = ?");
$stmt->execute([$_SESSION['booking_id']]);
$booking = $stmt->fetch();

$depositPaid = 2000; // ค่ามัดจำคงที่ 2,000 บาท
$advancePaid = $booking['MonthlyRent']; // ค่าเช่าล่วงหน้าเท่ากับค่าเช่า 1 เดือน
$totalAmount = $depositPaid + $advancePaid; 
$booking['DepositPaid'] = $depositPaid;
$booking['AdvancePaid'] = $advancePaid;
$booking['TotalAmount'] = $totalAmount;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        // Generate receipt number
        $receipt = 'RCPT' . time() . '_' . rand(1000, 9999);
        
        // Calculate late fee if applicable
        $lateFee = 0;
        if (isset($booking['DueDate']) && strtotime($booking['DueDate']) < time()) {
            $lateFee = 100; // ค่าปรับชำระล่าช้า 100 บาท
        }

        // Insert payment record
        $stmt = $pdo->prepare("INSERT INTO Payment (TenantID, BookingID, UtilityID, PaymentType, AmountPaid, PaymentDate, PaymentMethod, LateFee, PaymentStatus, Receipt) 
                             VALUES (?, ?, NULL, 'Room Booking', ?, CURRENT_DATE(), ?, ?, 'Completed', ?)");
        $stmt->execute([
            $_SESSION['tenant_id'],
            $_SESSION['booking_id'],
            $_POST['amount_paid'],
            $_POST['payment_method'],
            $lateFee,
            $receipt
        ]);

        // Update booking status
        $stmt = $pdo->prepare("UPDATE Booking SET PaymentStatus = 'Paid' WHERE BookingID = ?");
        $stmt->execute([$_SESSION['booking_id']]);

        // อัพเดทสถานะห้องเป็น Unavailable
        $stmt = $pdo->prepare("
            UPDATE Room r 
            JOIN Booking b ON r.RoomID = b.RoomID 
            SET r.RoomStatus = 'Unavailable' 
            WHERE b.BookingID = ?
        ");
        $stmt->execute([$_SESSION['booking_id']]);

        $pdo->commit();
        header('Location: payment_confirmation.php');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Payment processing failed. Please try again.";
    }
}

// Get existing payments for this booking
$stmt = $pdo->prepare("SELECT SUM(AmountPaid) as TotalPaid FROM Payment WHERE BookingID = ?");
$stmt->execute([$_SESSION['booking_id']]);
$paymentSum = $stmt->fetch();
$remainingAmount = $booking['TotalAmount'] - ($paymentSum['TotalPaid'] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .payment-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .booking-summary, .payment-summary {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .bank-details {
            background-color: #e9ecef;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <h1>Payment Information</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="booking-summary">
            <h3>Booking Details</h3>
            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($booking['RoomType']); ?></p>
            <p><strong>Check-in Date:</strong> <?php echo date('d/m/Y', strtotime($booking['CheckInDate'])); ?></p>
            <p><strong>Check-out Date:</strong> <?php echo date('d/m/Y', strtotime($booking['CheckOutDate'])); ?></p>
            <p><strong>Deposit paid:</strong> ฿<?php echo number_format($booking['DepositPaid'], 2); ?></p>
            <p><strong>Advance paid:</strong> ฿<?php echo number_format($booking['AdvancePaid'], 2); ?></p>
            <p><strong>Total Amount:</strong> ฿<?php echo number_format($booking['TotalAmount'], 2); ?></p>
        </div>

        <div class="payment-summary">
            <h3>Payment Status</h3>
            <p><strong>Amount Paid:</strong> ฿<?php echo number_format($paymentSum['TotalPaid'] ?? 0, 2); ?></p>
            <p><strong>Remaining Amount:</strong> ฿<?php echo number_format($remainingAmount, 2); ?></p>
        </div>

        <div class="bank-details">
            <h3>Bank Transfer Details</h3>
            <p><strong>Bank:</strong> Krungthai</p>
            <p><strong>Account Name:</strong> Dormitory</p>
            <p><strong>Account Number:</strong> 123-456-789</p>
            <p><strong>Branch:</strong> Ladkabang</p>
        </div>

        <form method="POST" action="payment.php">
            <div class="form-group">
                <label for="amount_paid">Amount to Pay:</label>
                <input type="number" id="amount_paid" name="amount_paid" 
                       value="<?php echo $remainingAmount; ?>" 
                       max="<?php echo $remainingAmount; ?>"
                       readonly
                       required step="0.01">
            </div>

            <input type="hidden" name="payment_type" value="Room Booking">

            <div class="form-group">
                <label for="payment_method">Payment Method (วิธีการชำระเงิน):</label>
                <select name="payment_method" id="payment_method" required>
                    <option value="">-- เลือกวิธีการชำระเงิน --</option>
                    <option value="KBank">KBank Mobile Banking</option>
                    <option value="SCB">SCB Easy</option>
                    <option value="BBL">Bangkok Bank Mobile Banking</option>
                    <option value="KTB">Krunthai NEXT</option>
                    <option value="GSB">GSB Mobile Banking</option>
                    <option value="PromptPay">PromptPay QR</option>
                    <option value="Visa">Visa Credit Card</option>
                    <option value="MasterCard">MasterCard Credit Card</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" <?php echo $remainingAmount <= 0 ? 'disabled' : ''; ?>>
                Confirm Payment
            </button>
        </form>
    </div>
    </div>

    <div class="footer">
        <p>© ChokunDormitory 2025</p>
    </div>

    <script src="payment_methods.js"></script>
    <script>
        document.getElementById('payment_method').addEventListener('change', function() {
            const qrCodeInfo = document.querySelector('.qr-code-info');
            if (this.value === 'PromptPay' && !qrCodeInfo) {
                const div = document.createElement('div');
                div.className = 'qr-code-info bank-details';
                div.innerHTML = `
                    <h3>PromptPay QR Payment</h3>
                    <div style="text-align: center;">
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSy5rFAQb08SqGGcVsRKNOHXkkZIsiDSDuHsw&s" alt="PromptPay QR" style="max-width: 200px; margin: 10px auto;">
                        <p><strong>PromptPay ID:</strong> 1234567890</p>
                        <p><strong>Name:</strong> Chokun Dormitory</p>
                        <p><strong>Amount:</strong> ฿${document.getElementById('amount_paid').value}</p>
                    </div>`;
                this.parentNode.after(div);
            } else {
                document.querySelector('.qr-code-info')?.remove();
            }
        });
    </script>

</body>
</html>