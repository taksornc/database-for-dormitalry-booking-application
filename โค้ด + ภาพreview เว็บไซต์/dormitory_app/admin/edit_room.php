<?php
session_start();
require_once('../db.php');
if (!isset($_SESSION['adminID'])) {
    header('Location: ../login_admin.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: room_manage.php');
    exit();
}

$roomID = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM Room WHERE RoomID = ?");
$stmt->execute([$roomID]);
$room = $stmt->fetch();

if (!$room) {
    header('Location: room_manage.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_type = $_POST['room_type'];
    $monthly_rent = $_POST['monthly_rent'];
    $room_status = $_POST['room_status'];
    $floor = $_POST['floor'];
    $size = $_POST['size'];
    $facilities = $_POST['facilities'];
    // อัพเดทข้อมูลห้องพัก
    $stmt = $pdo->prepare("UPDATE Room SET RoomType = ?, MonthlyRent = ?, RoomStatus = ?, 
                          Floor = ?, Size = ?, Facilities = ? WHERE RoomID = ?");
    if ($stmt->execute([$room_type, $monthly_rent, $room_status, $floor, $size, $facilities, $roomID])) {
        echo "<script>alert('Room updated successfully!'); window.location.href='room_manage.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Room</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        .current-image {
            max-width: 200px;
            margin: 10px 0;
        }
        .button-group {
            margin-top: 20px;
            display: flex;
            gap: 10px;
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
        }
        .input-group {
            position: relative;
        }
        .input-group span {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
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
        <a href="logout.php">Logout</a>
    </div>

    <div class="content">
        <h1>Edit Room</h1>

        <div class="form-container">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="room_type">Room Type:</label>
                    <input type="text" id="room_type" name="room_type" 
                           value="<?php echo htmlspecialchars($room['RoomType']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="monthly_rent">Monthly Rent:</label>
                    <div class="input-group">
                        <input type="number" id="monthly_rent" name="monthly_rent" 
                               value="<?php echo htmlspecialchars($room['MonthlyRent']); ?>" required>
                        <span>฿</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="room_status">Room Status:</label>
                    <select name="room_status" required>
                        <option value="Available" <?php echo $room['RoomStatus'] == 'Available' ? 'selected' : ''; ?>>
                            Available
                        </option>
                        <option value="Unavailable" <?php echo $room['RoomStatus'] == 'Unavailable' ? 'selected' : ''; ?>>
                            Unavailable
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="floor">Floor:</label>
                    <input type="number" id="floor" name="floor" 
                           value="<?php echo htmlspecialchars($room['Floor']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="size">Size:</label>
                    <div class="input-group">
                        <input type="number" id="size" name="size" 
                               value="<?php echo htmlspecialchars($room['Size']); ?>" required>
                        <span>m²</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="facilities">Facilities:</label>
                    <textarea name="facilities" id="facilities"><?php echo htmlspecialchars($room['Facilities']); ?></textarea>
                </div>

                

                <div class="button-group">
                    <button type="submit" class="submit-btn">Update Room</button>
                    <a href="room_manage.php" class="cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="footer">
        <p>© ChokunDormitory 2025</p>
    </div>
</body>
</html>
