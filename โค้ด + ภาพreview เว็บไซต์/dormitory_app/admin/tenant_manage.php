<?php
session_start();
require_once('../db.php'); 
if (!isset($_SESSION['adminID'])) {
    header('Location: ../login_admin.php');
    exit();
}

// ลบข้อมูลของtenanttt
if (isset($_GET['delete'])) {
    try {
        $pdo->beginTransaction();
        $tenantID = $_GET['delete'];
        
        $stmt = $pdo->prepare("DELETE FROM MaintenanceRequest WHERE TenantID = ?");
        $stmt->execute([$tenantID]);

        $stmt = $pdo->prepare("DELETE FROM Payment WHERE TenantID = ?");
        $stmt->execute([$tenantID]);

        $stmt = $pdo->prepare("DELETE FROM UtilityUsage WHERE RoomID IN 
                             (SELECT RoomID FROM Booking WHERE TenantID = ?)");
        $stmt->execute([$tenantID]);

        $stmt = $pdo->prepare("DELETE FROM lease_agreement WHERE BookingID IN 
                             (SELECT BookingID FROM Booking WHERE TenantID = ?)");
        $stmt->execute([$tenantID]);

        $stmt = $pdo->prepare("DELETE FROM Booking WHERE TenantID = ?");
        $stmt->execute([$tenantID]);

        $stmt = $pdo->prepare("DELETE FROM Tenant WHERE TenantID = ?");
        $stmt->execute([$tenantID]);

        $pdo->commit();
        $_SESSION['success'] = 'Tenant deleted successfully';
        header('Location: tenant_manage.php');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Error deleting tenant: ' . $e->getMessage();
        header('Location: tenant_manage.php');
        exit();
    }
}


$stmt = $pdo->prepare("SELECT t.* FROM Tenant t ORDER BY t.TenantID DESC");
$stmt->execute();
$tenants = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Tenants</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navigation Bar (Top) -->
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

    <div class="content">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="header">
            <h1>Admin - Manage Tenants</h1>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Tenant ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>ID Card Number</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tenants as $tenant): ?>
                <tr>
                    <td><?php echo htmlspecialchars($tenant['TenantID']); ?></td>
                    <td><?php echo htmlspecialchars($tenant['FirstName']); ?></td>
                    <td><?php echo htmlspecialchars($tenant['LastName']); ?></td>
                    <td><?php echo htmlspecialchars($tenant['Email']); ?></td>
                    <td><?php echo htmlspecialchars($tenant['Phone']); ?></td>
                    <td><?php echo htmlspecialchars($tenant['IDCardNumber']); ?></td>
                    <td><?php echo htmlspecialchars($tenant['Address']); ?></td>
                    <td>
                        <a href="edit_tenant.php?id=<?php echo $tenant['TenantID']; ?>" class="action-btn">Edit</a>
                        <a href="tenant_manage.php?delete=<?php echo $tenant['TenantID']; ?>" class="action-btn" 
                           onclick="return confirm('Are you sure you want to delete this tenant? This will remove all related records.')">Delete</a>
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