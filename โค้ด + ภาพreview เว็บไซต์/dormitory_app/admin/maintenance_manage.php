<?php
session_start();
require_once('../db.php');
if (!isset($_SESSION['adminID'])) {
    header('Location: ../login_admin.php');
    exit();
}
$stmt = $pdo->prepare("
    SELECT 
        mr.RequestID,
        mr.TenantID,
        mr.RoomID,
        mr.RequestDate,
        mr.IssueDescription,
        mr.Status,
        mr.StaffID
    FROM MaintenanceRequest mr
    ORDER BY mr.RequestID DESC
");
$stmt->execute();
$requests = $stmt->fetchAll();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Requests Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="tenant_manage.php">Tenants</a>
            <a href="room_manage.php">Rooms</a>
            <a href="booking_manage.php">Bookings</a>
            <a href="lease_manage.php">Lease Agreements</a>
            <a href="payment_manage.php">Payments</a>
            <a href="utility_manage.php">Utility Usage</a>
            <a href="maintenance_manage.php" class="active">Maintenance</a>
            <a href="staff_manage.php">Staff</a>
            <a href="contact_manage.php">Messages</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="content">
        <h1>Maintenance Requests Management</h1>

        <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; margin-top: 20px;">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Tenant ID</th>
                    <th>Room ID</th>
                    <th>Request Date</th>
                    <th>Issue Description</th>
                    <th>Status</th>
                    <th>Staff ID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                <tr>
                    <td><?php echo htmlspecialchars($request['RequestID']); ?></td>
                    <td><?php echo htmlspecialchars($request['TenantID']); ?></td>
                    <td><?php echo htmlspecialchars($request['RoomID']); ?></td>
                    <td><?php echo htmlspecialchars($request['RequestDate']); ?></td>
                    <td><?php echo htmlspecialchars($request['IssueDescription']); ?></td>
                    <td><?php echo htmlspecialchars($request['Status']); ?></td>
                    <td><?php echo htmlspecialchars($request['StaffID']); ?></td>
                    <td>
                        <a href="edit_maintenance.php?id=<?php echo $request['RequestID']; ?>" class="action-btn">Edit</a>
                        <a href="delete_maintenance.php?id=<?php echo $request['RequestID']; ?>" 
                           onclick="return confirm('Are you sure you want to delete this request?')" 
                           class="action-btn">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>&copy; 2025 ChokunDormitory. All rights reserved.</p>
    </div>
</body>
</html>