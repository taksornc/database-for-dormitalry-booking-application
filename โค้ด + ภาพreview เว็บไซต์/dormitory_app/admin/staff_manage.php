<?php
session_start();
require_once('../db.php');
if (!isset($_SESSION['adminID'])) {
    header('Location: ../login_admin.php');
    exit();
}
if (isset($_GET['delete'])) {
    $deleteID = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM AdminStaff WHERE StaffID = ?");
    $stmt->execute([$deleteID]);
    echo "<script>alert('Staff member has been deleted successfully!'); window.location.href='staff_manage.php';</script>";
}

$stmt = $pdo->prepare("SELECT * FROM AdminStaff ORDER BY StaffID DESC");
$stmt->execute();
$staffMembers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .add-btn {
            background-color: #2ecc71;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
        }
        .action-btn {
            padding: 5px 10px;
            border-radius: 3px;
            color: white;
            text-decoration: none;
            margin: 0 5px;
        }
        .edit-btn { background-color: #f39c12; }
        .delete-btn { background-color: #e74c3c; }
        table { margin-top: 20px; }
    </style>
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

    <div class="content">
        <h1>Staff Management</h1>
        <a href="add_staff.php" class="add-btn">Add New Staff</a>

        <table>
            <thead>
                <tr>
                    <th>Staff ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staffMembers as $staff): ?>
                <tr>
                    <td><?php echo htmlspecialchars($staff['StaffID']); ?></td>
                    <td><?php echo htmlspecialchars($staff['FirstName']); ?></td>
                    <td><?php echo htmlspecialchars($staff['LastName']); ?></td>
                    <td><?php echo htmlspecialchars($staff['Phone']); ?></td>
                    <td><?php echo htmlspecialchars($staff['Role']); ?></td>
                    <td>
                        <a href="edit_staff.php?id=<?php echo $staff['StaffID']; ?>" 
                           class="action-btn edit-btn">Edit</a>
                        <a href="staff_manage.php?delete=<?php echo $staff['StaffID']; ?>" 
                           class="action-btn delete-btn"
                           onclick="return confirm('Are you sure you want to delete this staff member?')">Delete</a>
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