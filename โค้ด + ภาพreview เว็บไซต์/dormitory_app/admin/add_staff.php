<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['adminID'])) {
    header('Location: ../login_admin.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("INSERT INTO AdminStaff (FirstName, LastName, Phone, Role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$firstName, $lastName, $phone, $role]);
        echo "<script>alert('Staff member added successfully!'); window.location.href='staff_manage.php';</script>";
    } catch(PDOException $e) {
        $error = "Error adding staff member.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Staff</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
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
        .submit-btn {
            background-color: #2ecc71;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .cancel-btn {
            background-color: #e74c3c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            margin-left: 10px;
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
        <a href="../login_admin.php">Logout</a>
    </div>

    <div class="content">
        <h1>Add New Staff</h1>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="tel" id="phone" name="phone" pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number" required>
                </div>

                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="">Select Role</option>
                        <option value="Admin">Admin</option>
                        <option value="Manager">Manager</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Security">Security</option>
                        <option value="Cleaner">Cleaner</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="submit-btn">Add Staff</button>
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