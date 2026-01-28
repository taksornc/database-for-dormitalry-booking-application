<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['adminID'])) {
    header('Location: ../login_admin.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: maintenance_manage.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT 
        mr.*,
        t.FirstName as TenantFirstName,
        t.LastName as TenantLastName,
        r.RoomType,
        r.Floor,
        s.FirstName as StaffFirstName,
        s.LastName as StaffLastName
    FROM MaintenanceRequest mr
    INNER JOIN Tenant t ON mr.TenantID = t.TenantID
    INNER JOIN Room r ON mr.RoomID = r.RoomID
    LEFT JOIN AdminStaff s ON mr.StaffID = s.StaffID
    WHERE mr.RequestID = ?
");
$stmt->execute([$_GET['id']]);
$request = $stmt->fetch();

$stmt = $pdo->prepare("SELECT StaffID, FirstName, LastName, Role FROM AdminStaff WHERE Role IN ('Cleaner', 'Maintenance', 'Technician')");
$stmt->execute();
$staff = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = $pdo->prepare("UPDATE MaintenanceRequest SET Status = ?, StaffID = ? WHERE RequestID = ?");
        $stmt->execute([
            $_POST['status'],
            $_POST['staff_id'],
            $_GET['id']
        ]);
        header('Location: maintenance_manage.php');
        exit();
    } catch (Exception $e) {
        $error = "Error updating maintenance request.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Maintenance Request</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="tenant_manage.php">Tenants</a>
            <a href="room_manage.php">Rooms</a>
            <a href="booking_manage.php">Bookings</a>
            <a href="payment_manage.php">Payments</a>
            <a href="utility_manage.php">Utility Usage</a>
            <a href="maintenance_manage.php" class="active">Maintenance</a>
            <a href="staff_manage.php">Staff</a>
            <a href="contact_manage.php">Messages</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="content">
        <h1>Edit Maintenance Request</h1>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="white-box">
            <div class="request-details">
                <h2>Request Information</h2>
                <p><strong>Request ID:</strong> <?php echo htmlspecialchars($request['RequestID']); ?></p>
                <p><strong>Tenant:</strong> <?php echo htmlspecialchars($request['TenantFirstName'] . ' ' . $request['TenantLastName']); ?></p>
                <p><strong>Room:</strong> <?php echo htmlspecialchars($request['RoomID']); ?> (Type: <?php echo htmlspecialchars($request['RoomType']); ?>, Floor: <?php echo htmlspecialchars($request['Floor']); ?>)</p>
                <p><strong>Request Date:</strong> <?php echo htmlspecialchars($request['RequestDate']); ?></p>
                <p><strong>Issue Description:</strong> <?php echo htmlspecialchars($request['IssueDescription']); ?></p>
            </div>

            <form method="POST" class="edit-form">
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select name="status" id="status" required>
                        <option value="Pending" <?php echo $request['Status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="In Progress" <?php echo $request['Status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Completed" <?php echo $request['Status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="Rejected" <?php echo $request['Status'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="staff_id">Assign Staff:</label>
                    <select name="staff_id" id="staff_id" required>
                        <option value="">Select Staff</option>
                        <?php foreach ($staff as $s): ?>
                            <option value="<?php echo $s['StaffID']; ?>" 
                                    <?php echo $request['StaffID'] == $s['StaffID'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['FirstName'] . ' ' . $s['LastName'] . ' (' . $s['Role'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn-primary">Update Request</button>
                    <a href="maintenance_manage.php" class="btn-primary">Back to List</a>
                </div>
            </form>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2025 ChokunDormitory. All rights reserved.</p>
    </div>
</body>
</html>