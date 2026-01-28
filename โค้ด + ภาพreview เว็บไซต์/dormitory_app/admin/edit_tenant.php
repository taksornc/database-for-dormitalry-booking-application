<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['adminID'])) {
    header('Location: ../login_admin.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: tenant_manage.php');
    exit();
}

$tenantID = $_GET['id'];

$stmt = $pdo->prepare("SELECT t.*, u.Username, u.Email as UserEmail, u.Phone as UserPhone 
FROM Tenant t 
JOIN Users u ON t.UserID = u.UserID 
WHERE t.TenantID = ?");
$stmt->execute([$tenantID]);
$tenant = $stmt->fetch();

if (!$tenant) {
    header('Location: tenant_manage.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = $pdo->prepare("
            UPDATE Tenant 
            SET FirstName = ?, 
                LastName = ?, 
                Email = ?, 
                Phone = ?, 
                IDCardNumber = ?,
                Address = ?
            WHERE TenantID = ?
        ");
        
        $stmt->execute([
            $_POST['firstName'],
            $_POST['lastName'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['idCardNumber'],
            $_POST['address'],
            $tenantID
        ]);

        header('Location: tenant_manage.php');
        exit();
    } catch (PDOException $e) {
        $error = "Error updating tenant: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tenant</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="tenant_manage.php" class="active">Tenants</a>
        <a href="room_manage.php">Rooms</a>
        <a href="booking_manage.php">Bookings</a>
        <a href="payment_manage.php">Payments</a>
        <a href="utility_manage.php">Utility Usage</a>
        <a href="maintenance_manage.php">Maintenance</a>
        <a href="staff_manage.php">Staff</a>
        <a href="contact_manage.php">Messages</a>
        <a href="../login_admin.php">Logout</a>
    </div>

    <div class="content">
        <div class="edit-form">
            <h2>Edit Tenant Information</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName">First Name:</label>
                        <input type="text" id="firstName" name="firstName" 
                               value="<?php echo htmlspecialchars($tenant['FirstName']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="lastName">Last Name:</label>
                        <input type="text" id="lastName" name="lastName" 
                               value="<?php echo htmlspecialchars($tenant['LastName']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($tenant['Email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone:</label>
                        <input type="text" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($tenant['Phone']); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="idCardNumber">ID Card Number:</label>
                    <input type="text" id="idCardNumber" name="idCardNumber" 
                           value="<?php echo htmlspecialchars($tenant['IDCardNumber']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="address">Address:</label>
                    <textarea id="address" name="address" rows="3" required><?php echo htmlspecialchars($tenant['Address']); ?></textarea>
                </div>



                <div class="btn-container">
                    <a href="tenant_manage.php" class="btn btn-back">Back</a>
                    <button type="submit" class="btn btn-primary">Update Tenant</button>
                </div>
            </form>
        </div>
    </div>

    <div class="footer">
        <p>© ChokunDormitory 2025</p>
    </div>
</body>
</html>