<?php
session_start();
require_once('../db.php');
if (!isset($_SESSION['adminID'])) {
    header('Location: ../login_admin.php');
    exit();
}

//เพิ่มห้องๆ
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_id = $_POST['room_id'];
    $room_type = $_POST['room_type'];
    $monthly_rent = $_POST['monthly_rent'];
    $room_status = $_POST['room_status'];
    $floor = $_POST['floor'];
    $size = $_POST['size'];
    $facilities = $_POST['facilities'];

    //คิวรี่สำหรับเพิ่มห้องใหม่
    $stmt = $pdo->prepare("INSERT INTO Room (RoomID, RoomType, MonthlyRent, RoomStatus, Floor, Size, Facilities) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$room_id, $room_type, $monthly_rent, $room_status, $floor, $size, $facilities]);

    //แสดงข้อความสำเร็จและเปลี่ยนหน้า
    echo "<script>
            alert('Room added successfully!');
            window.location.href = 'room_manage.php';
          </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Room</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navigation Bar -->
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
        <h1>Add New Room</h1>
        <!-- Add New Room Form -->
        <div class="form-container">
            <form method="POST" action="addroom.php" enctype="multipart/form-data" class="room-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="room_id">Room ID</label>
                        <input type="text" id="room_id" name="room_id" required placeholder="Enter room ID">
                    </div>
                    <div class="form-group">
                        <label for="room_type">Room Type</label>
                        <input type="text" id="room_type" name="room_type" required placeholder="Enter room type">
                    </div>
                </div>
                <div class="form-group">
                    <label for="monthly_rent">Monthly Rent (฿)</label>
                    <input type="number" id="monthly_rent" name="monthly_rent" required placeholder="Enter rent amount">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="room_status">Room Status</label>
                        <select name="room_status" required>
                            <option value="">Select Status</option>
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="floor">Floor</label>
                        <input type="number" id="floor" name="floor" required placeholder="Enter floor number">
                    </div>
                    <div class="form-group">
                        <label for="size">Size (m²)</label>
                        <input type="number" id="size" name="size" required placeholder="Enter room size">
                    </div>
                </div>
                <div class="form-group">
                    <label for="facilities">Facilities</label>
                    <textarea name="facilities" id="facilities" rows="4" placeholder="Enter room facilities (e.g., Air Conditioning, WiFi, Furniture)"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Add Room</button>
                    <button type="reset" class="btn-reset">Reset</button>
                    <a href="room_manage.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>© ChokunDormitory 2025</p>
    </div>
</body>
</html>