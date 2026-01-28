<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['adminID'])) {
    header('Location: ../login_admin.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: staff_manage.php');
    exit();
}

$staffID = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM AdminStaff WHERE StaffID = ?");
$stmt->execute([$staffID]);
$staff = $stmt->fetch();

if (!$staff) {
    header('Location: staff_manage.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            UPDATE AdminStaff 
            SET FirstName = ?, 
                LastName = ?, 
                Phone = ?, 
                Role = ?
            WHERE StaffID = ?
        ");
        
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['phone'],
            $_POST['role'],
            $staffID
        ]);

        echo "<script>alert('Staff member updated successfully!'); window.location.href='staff_manage.php';</script>";
    } catch (PDOException $e) {
        $error = "Error updating staff member: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Staff</title>
    <link rel="stylesheet" href="style.css">
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
        <a href="staff_manage.php" class="active">Staff</a>
        <a href="contact_manage.php">Messages</a>
        <a href="../login_admin.php">Logout</a>
    </div>

    <div class="content">
        <h1>Edit Staff Member</h1>

        <div class="form-container">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" 
                           value="<?php echo htmlspecialchars($staff['FirstName']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" 
                           value="<?php echo htmlspecialchars($staff['LastName']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($staff['Phone']); ?>" 
                           pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number" required>
                </div>

                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="Admin" <?php echo $staff['Role'] == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="Manager" <?php echo $staff['Role'] == 'Manager' ? 'selected' : ''; ?>>Manager</option>
                        <option value="Maintenance" <?php echo $staff['Role'] == 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                        <option value="Security" <?php echo $staff['Role'] == 'Security' ? 'selected' : ''; ?>>Security</option>
                        <option value="Cleaner" <?php echo $staff['Role'] == 'Cleaner' ? 'selected' : ''; ?>>Cleaner</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="submit-btn">Update Staff</button>
                    <a href="staff_manage.php" class="cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="footer">
        <p>© ChokunDormitory 2025</p>
    </div>
</body>
</html>