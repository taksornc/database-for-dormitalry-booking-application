<?php
session_start();
require_once('../db.php');
if (!isset($_SESSION['adminID'])) {
    header('Location: ../login_admin.php');
    exit();
}

if (isset($_GET['id'])) {
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("SELECT BookingID FROM lease_agreement WHERE LeaseID = ?");
        $stmt->execute([$_GET['id']]);
        $lease = $stmt->fetch();
        
        if ($lease) {
            $stmt = $pdo->prepare("DELETE FROM lease_agreement WHERE LeaseID = ?");
            $stmt->execute([$_GET['id']]);
        } else {
            throw new Exception('Lease agreement not found.');
        }
        
        $pdo->commit();
        $_SESSION['success_message'] = 'Lease agreement deleted successfully!';
        header('Location: lease_manage.php');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>
                alert('Error deleting lease agreement. Please try again.');
                window.location.href='lease_manage.php';
              </script>";
    }
}

$stmt = $pdo->prepare("
    SELECT l.LeaseID, l.BookingID, b.TenantID, l.StartDate, l.EndDate, 
           l.RentAmount, l.DepositAmount, l.Terms
    FROM lease_agreement l
    LEFT JOIN Booking b ON l.BookingID = b.BookingID
    ORDER BY l.LeaseID DESC");
$stmt->execute();
$leases = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Lease Agreements</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="tenant_manage.php">Tenants</a>
        <a href="room_manage.php">Rooms</a>
        <a href="booking_manage.php">Bookings</a>
        <a href="lease_manage.php">Lease Agreements</a>
        <a href="payment_manage.php">Payments</a>
        <a href="utility_manage.php">Utility Usage</a>
        <a href="maintenance_manage.php">Maintenance</a>
        <a href="staff_manage.php">Staff</a>
        <a href="contact_manage.php">Messages</a>
        <a href="logout.php">Logout</a>
    </div>

    <style>
        .terms-cell {
            max-height: 100px;
            max-width: 200px;
            overflow-y: auto;
            white-space: pre-wrap;
            padding: 5px;
        }
    </style>

    <div class="content">
        <h1>Manage Lease Agreements</h1>
        <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; margin-top: 20px;">
            <thead>
                <tr>
                    <th>Lease ID</th>
                    <th>Booking ID</th>
                    <th>Tenant ID</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Rent Amount</th>
                    <th>Deposit Amount</th>
                    <th>Terms</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leases as $lease): ?>
                <tr>
                    <td><?php echo htmlspecialchars($lease['LeaseID']); ?></td>
                    <td><?php echo $lease['BookingID'] ? htmlspecialchars($lease['BookingID']) : 'N/A'; ?></td>
                    <td><?php echo $lease['TenantID'] ? htmlspecialchars($lease['TenantID']) : 'N/A'; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($lease['StartDate'])); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($lease['EndDate'])); ?></td>
                    <td>฿<?php echo number_format($lease['RentAmount'], 2); ?></td>
                    <td>฿<?php echo number_format($lease['DepositAmount'], 2); ?></td>
                    <td><div class="terms-cell"><?php echo nl2br(htmlspecialchars($lease['Terms'])); ?></div></td>
                    <td>
                        <a href="lease_manage.php?id=<?php echo $lease['LeaseID']; ?>" 
                           class="action-btn"
                           onclick="return confirm('Are you sure you want to delete this lease agreement?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>© ChokunDormitory 2025</p>
    </div>
</body>
</html>