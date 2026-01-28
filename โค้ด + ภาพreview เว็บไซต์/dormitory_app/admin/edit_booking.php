<?php
session_start();
require_once('../db.php');
if (!isset($_SESSION['adminID'])) {
    header('Location: ../login_admin.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: booking_manage.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT b.*, r.RoomType, r.MonthlyRent, r.RoomStatus,
           t.FirstName, t.LastName, 
           l.LeaseID, l.StartDate as LeaseStartDate, l.EndDate as LeaseEndDate,
           COALESCE(SUM(p.AmountPaid), 0) as AmountPaid,
           CASE 
               WHEN COALESCE(SUM(p.AmountPaid), 0) >= (COALESCE(b.DepositPaid, 0) + COALESCE(b.AdvancePaid, 0)) THEN 'Paid'
               WHEN COALESCE(SUM(p.AmountPaid), 0) > 0 THEN 'Partially Paid'
               ELSE 'Pending'
           END as PaymentStatus
    FROM Booking b
    LEFT JOIN Room r ON b.RoomID = r.RoomID
    LEFT JOIN Tenant t ON b.TenantID = t.TenantID
    LEFT JOIN lease_agreement l ON b.BookingID = l.BookingID
    LEFT JOIN Payment p ON b.BookingID = p.BookingID
    WHERE b.BookingID = ?
    GROUP BY b.BookingID");
$stmt->execute([$_GET['id']]);
$booking = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            UPDATE Booking 
            SET CheckInDate = ?, 
                CheckOutDate = ?, 
                TotalAmount = ?,
                DepositPaid = ?,
                AdvancePaid = ?
            WHERE BookingID = ?
        ");
        $stmt->execute([
            $_POST['check_in_date'],
            $_POST['check_out_date'],
            $_POST['total_amount'],
            $_POST['deposit_paid'],      // Added missing parameter
            $_POST['advance_paid'],      // Added missing parameter
            $_GET['id']
        ]);

        $roomStatus = $_POST['payment_status'] === 'Paid' ? 'unavailable' : 'available';
        $stmt = $pdo->prepare("UPDATE Room SET RoomStatus = ? WHERE RoomID = ?");
        $stmt->execute([$roomStatus, $booking['RoomID']]);

        if ($_POST['payment_status'] === 'Paid') {
            $stmt = $pdo->prepare("
                INSERT INTO Payment (BookingID, AmountPaid, PaymentDate)
                VALUES (?, ?, CURRENT_DATE)
                ON DUPLICATE KEY UPDATE
                AmountPaid = VALUES(AmountPaid)
            ");
            $stmt->execute([$_GET['id'], $_POST['total_amount']]);
        }

        $pdo->commit();
        header('Location: booking_manage.php');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error updating booking: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .edit-form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .booking-summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="tenant_manage.php">Tenants</a>
        <a href="room_manage.php">Rooms</a>
        <a href="booking_manage.php">Bookings</a>
        <a href="payment_manage.php">Payments</a>
        <a href="utility_manage.php">Utility Usage</a>
        <a href="maintenance_manage.php">Maintenance</a>
        <a href="staff_manage.php">Staff</a>
        <a href="contact_manage.php">Messages</a>
    </div>

    <div class="content">
        <div class="edit-form">
            <h2>Edit Booking</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

        
                <div class="booking-summary">
                    <h3>Booking Details</h3>
                    <p><strong>Booking ID:</strong> <?php echo $booking['BookingID']; ?></p>
                    <p><strong>Tenant:</strong> <?php echo $booking['FirstName'] . ' ' . $booking['LastName']; ?></p>
                    <p><strong>Room Type:</strong> <?php echo $booking['RoomType']; ?></p>
                    <p><strong>Monthly Rent:</strong> ฿<?php echo number_format($booking['MonthlyRent'], 2); ?></p>
                    <p><strong>Lease ID:</strong> <?php echo $booking['LeaseID'] ? $booking['LeaseID'] : 'N/A'; ?></p>
                    <?php if ($booking['LeaseID']): ?>
                    <p><strong>Lease Period:</strong> <?php echo date('d/m/Y', strtotime($booking['LeaseStartDate'])) . ' - ' . date('d/m/Y', strtotime($booking['LeaseEndDate'])); ?></p>
                    <?php endif; ?>
                </div>

            <form method="POST">
                <div class="form-group">
                    <label for="check_in_date">Check-in Date:</label>
                    <input type="date" id="check_in_date" name="check_in_date" 
                           value="<?php echo $booking['CheckInDate']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="check_out_date">Check-out Date:</label>
                    <input type="date" id="check_out_date" name="check_out_date" 
                           value="<?php echo $booking['CheckOutDate']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="total_amount">Total Amount:</label>
                    <input type="number" id="total_amount" name="total_amount" step="0.01" 
                           value="<?php echo $booking['TotalAmount']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="deposit_paid">Deposit Paid:</label>
                    <input type="number" id="deposit_paid" name="deposit_paid" step="0.01" 
                           value="<?php echo $booking['DepositPaid']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="advance_paid">Advance Paid:</label>
                    <input type="number" id="advance_paid" name="advance_paid" step="0.01" 
                           value="<?php echo $booking['AdvancePaid']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="payment_status">Payment Status:</label>
                    <select id="payment_status" name="payment_status" required>
                        <option value="Pending" <?php echo $booking['PaymentStatus'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Partially Paid" <?php echo $booking['PaymentStatus'] == 'Partially Paid' ? 'selected' : ''; ?>>Partially Paid</option>
                        <option value="Paid" <?php echo $booking['PaymentStatus'] == 'Paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="Cancelled" <?php echo $booking['PaymentStatus'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Update Booking</button>
                <a href="booking_manage.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>