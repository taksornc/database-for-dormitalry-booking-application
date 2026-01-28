<?php
require_once('../db.php');
session_start();
if (!isset($_SESSION['adminID'])) {
    header('Location: ../login_admin.php');
    exit();
}
if (!isset($_GET['id'])) {
    header('Location: utility_manage.php');
    exit();
}

$utilityID = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM UtilityUsage WHERE UtilityID = ?");
$stmt->execute([$utilityID]);
$utility = $stmt->fetch();

if (!$utility) {
    header('Location: utility_manage.php');
    exit();
}

if (isset($_POST['edit'])) {
    try {
        // Set fixed rates
        $waterRate = 20;  // 20 baht per unit
        $electricityRate = 8;  // 8 baht per unit
        
        // Calculate bills using fixed rates
        $waterUsage = is_numeric($_POST['water_usage']) ? (float)$_POST['water_usage'] : 0;
        $electricityUsage = is_numeric($_POST['electricity_usage']) ? (float)$_POST['electricity_usage'] : 0;
        
        $waterBill = $waterUsage * $waterRate;
        $electricityBill = $electricityUsage * $electricityRate;
        $totalCost = $waterBill + $electricityBill;
        
        // Update the utility record
        $updateStmt = $pdo->prepare("UPDATE UtilityUsage SET 
            RoomID = ?, 
            BillDate = ?, 
            WaterUsage = ?, 
            WaterBill = ?, 
            ElectricityUsage = ?, 
            ElectricityBill = ?,
            TotalUtilityCost = ?
            WHERE UtilityID = ?");
            
        $updateStmt->execute([
            $_POST['room_id'],
            $_POST['bill_date'],
            $waterUsage,
            $waterBill,
            $electricityUsage,
            $electricityBill,
            $totalCost,
            $_POST['utility_id']
        ]);
        
        // Redirect back to manage page with success message
        header('Location: utility_manage.php?success=1');
        exit();
    } catch (PDOException $e) {
        $error = "Error updating record: " . $e->getMessage();
    }
}

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
    <title>Edit Utility Record</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="content">
        <div class="header">
            <h1>Edit Utility Record</h1>
        </div>

        <div class="form-section">
            <form method="POST" action="">
                <input type="hidden" name="utility_id" value="<?php echo $utility['UtilityID']; ?>">

                <div class="form-group">
                    <label>Room:</label>
                    <select name="room_id" required>
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo $room['RoomID']; ?>" 
                                    <?php echo ($room['RoomID'] == $utility['RoomID']) ? 'selected' : ''; ?>>
                                Room <?php echo $room['RoomID']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Bill Date:</label>
                    <input type="date" name="bill_date" value="<?php echo $utility['BillDate']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Water Usage (Units):</label>
                    <input type="number" name="water_usage" step="0.01" 
                           value="<?php echo $utility['WaterUsage']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Electricity Usage (Units):</label>
                    <input type="number" name="electricity_usage" step="0.01" 
                           value="<?php echo $utility['ElectricityUsage']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Electricity Bill (฿):</label>
                    <input type="number" name="electricity_bill" step="0.01" 
                           value="<?php echo $utility['ElectricityBill']; ?>" required>
                </div>

                <button type="submit" name="edit" class="action-btn">Update Record</button>
                <a href="utility_manage.php" class="action-btn">Cancel</a>
            </form>
        </div>
    </div>

    <div class="footer">
        <p>© ChokunDormitory 2025</p>
    </div>
</body>
</html>