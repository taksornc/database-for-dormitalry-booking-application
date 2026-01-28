<?php
require_once('../db.php');
session_start();
if (!isset($_SESSION['adminID'])) {
    header('Location: ../login_admin.php');
    exit();
}
//ลบ
if (isset($_GET['delete'])) {
    $deleteID = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM UtilityUsage WHERE UtilityID = ?");
    $stmt->execute([$deleteID]);
    echo "<script>alert('Utility record has been deleted successfully!'); window.location.href='utility_manage.php';</script>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set fixed rates
    $waterRate = 20;  // 20 baht per unit
    $electricityRate = 8;  // 8 baht per unit
    
    $waterTotal = $_POST['water_usage'] * $waterRate;
    $electricityTotal = $_POST['electricity_usage'] * $electricityRate;
    $totalCost = $waterTotal + $electricityTotal;
    
    if (isset($_POST['add'])) {
        $stmt = $pdo->prepare("INSERT INTO UtilityUsage (RoomID, BillDate, WaterUsage, ElectricityUsage, WaterBill, ElectricityBill, TotalUtilityCost) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['room_id'], $_POST['bill_date'], $_POST['water_usage'], $_POST['electricity_usage'], $waterTotal, $electricityTotal, $totalCost]);
        echo "<script>alert('Utility record added successfully!'); window.location.href='utility_manage.php';</script>";
    }
    
    if (isset($_POST['edit'])) {
        $stmt = $pdo->prepare("UPDATE UtilityUsage SET RoomID = ?, BillDate = ?, WaterUsage = ?, ElectricityUsage = ?, WaterBill = ?, ElectricityBill = ?, TotalUtilityCost = ? WHERE UtilityID = ?");
        $stmt->execute([$_POST['room_id'], $_POST['bill_date'], $_POST['water_usage'], $_POST['electricity_usage'], $waterTotal, $electricityTotal, $totalCost, $_POST['utility_id']]);
        echo "<script>alert('Utility record updated successfully!'); window.location.href='utility_manage.php';</script>";
    }
}

// Fetch all utility records
$stmt = $pdo->prepare("
    SELECT u.*, r.RoomType 
    FROM UtilityUsage u 
    JOIN Room r ON u.RoomID = r.RoomID 
    ORDER BY u.BillDate DESC
");
$stmt->execute();
$utilities = $stmt->fetchAll();

// Fetch rooms for dropdown
$stmt = $pdo->prepare("SELECT RoomID, RoomType FROM Room ORDER BY RoomType");
$stmt->execute();
$rooms = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Utility Usage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<title>Admin - Manage Utility Usage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navigation Bar (Top) -->
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
        <div class="header">
            <h1>Admin - Manage Utility Usage</h1>
        </div>

        <div class="form-section">
            <h2>Add New Utility Record</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Room:</label>
                    <select name="room_id" required>
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo $room['RoomID']; ?>">
                                Room <?php echo $room['RoomID']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Bill Date:</label>
                    <input type="date" name="bill_date" required>
                </div>

                <div class="form-group">
                    <label>Water Usage (Units) (20฿/unit):</label>
                    <input type="number" name="water_usage" step="0.01" required>
                </div>

                <div class="form-group">
                    <label>Electricity Usage (Units) (8฿/unit):</label>
                    <input type="number" name="electricity_usage" step="0.01" required>
                </div>

                <button type="submit" name="add" class="action-btn">Add Record</button>
            </form>
        </div>

        <!-- Display Utility Records in a Table -->
        <!-- Update the table display section -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Room ID</th>
                    <th>Bill Date</th>
                    <th>Water Usage</th>
                    <th>Water Bill</th>
                    <th>Electricity Usage</th>
                    <th>Electricity Bill</th>
                    <th>Total Cost</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilities as $utility): ?>
                <tr>
                    <td><?php echo htmlspecialchars($utility['UtilityID']); ?></td>
                    <td><?php echo htmlspecialchars($utility['RoomID']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($utility['BillDate'])); ?></td>
                    <td><?php echo number_format($utility['WaterUsage'], 2); ?> หน่วย</td>
                    <td>฿<?php echo number_format($utility['WaterBill'], 2); ?></td>
                    <td><?php echo number_format($utility['ElectricityUsage'], 2); ?> หน่วย</td>
                    <td>฿<?php echo number_format($utility['ElectricityBill'], 2); ?></td>
                    <td>฿<?php echo number_format($utility['TotalUtilityCost'], 2); ?></td>
                    <td>
                        <a href="utility_edit.php?id=<?php echo $utility['UtilityID']; ?>" class="action-btn">Edit</a>
                        <a href="utility_manage.php?delete=<?php echo $utility['UtilityID']; ?>" 
                           class="action-btn" 
                           onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>© ChokunDormitory 2025</p>
    </div>
</body>
</html>