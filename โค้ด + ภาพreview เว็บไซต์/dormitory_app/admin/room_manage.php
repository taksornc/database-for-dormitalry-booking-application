<?php
session_start();
require_once('../db.php'); 
if (!isset($_SESSION['adminID'])) {
    header('Location: ../login_admin.php');
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_type = $_POST['room_type'];
    $monthly_rent = $_POST['monthly_rent'];
    $room_status = $_POST['room_status'];
    $floor = $_POST['floor'];
    $size = $_POST['size'];
    $facilities = $_POST['facilities'];

    if (isset($_FILES['room_image'])) {
        $image = $_FILES['room_image'];
        $image_name = $image['name'];
        $image_tmp_name = $image['tmp_name'];
        $image_size = $image['size'];
        $image_error = $image['error'];

        if ($image_error === 0) {
            $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array(strtolower($image_ext), $allowed_ext)) {
                if ($image_size <= 5000000) { 
                    $new_image_name = uniqid('room_', true) . '.' . $image_ext;
                    $image_path = '../images/' . $new_image_name;
                    move_uploaded_file($image_tmp_name, $image_path);  

                    $stmt = $pdo->prepare("INSERT INTO Room (RoomType, MonthlyRent, RoomStatus, Floor, Size, Facilities, RoomImage) 
                                           VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$room_type, $monthly_rent, $room_status, $floor, $size, $facilities, $new_image_name]);

                    // แสดงข้อความสำเร็จ
                    echo "<script>alert('Room added successfully!'); window.location.href='room_manage.php';</script>";
                } else {
                    echo "<script>alert('File size is too large!');</script>";
                }
            } else {
                echo "<script>alert('Invalid file type!');</script>";
            }
        } else {
            echo "<script>alert('Error uploading file!');</script>";
        }
    }
}

// ดึงข้อมูลห้องทั้งหมดจากฐานข้อมูล
$stmt = $pdo->prepare("SELECT * FROM Room");
$stmt->execute();
$rooms = $stmt->fetchAll();
// Add this after the existing database queries
$stmtRoomIDs = $pdo->prepare("SELECT DISTINCT RoomID FROM Room ORDER BY RoomID");
$stmtRoomIDs->execute();
$roomIDs = $stmtRoomIDs->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Navigation Bar -->
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

    <div class="content" style="padding-top: 10px;">
        <h1 style="margin-bottom: 10px;">Manage Rooms</h1>
        
        <div class="filter-section" style="margin: 20px 0;">
            <select id="roomTypeFilter" style="padding: 8px; border-radius: 4px;">
                <option value="">All Rooms</option>
                <?php foreach ($roomIDs as $id): ?>
                    <option value="<?php echo htmlspecialchars($id); ?>">
                        Room <?php echo htmlspecialchars($id); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="action-buttons" style="margin-bottom: 10px;">
            <a href="addroom.php" class="btn-add" style="display: inline-block; padding: 8px 16px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; transition: background-color 0.3s ease;">Add New Room</a>
        </div>

        <h2 style="margin-top: 10px; margin-bottom: 10px;">Existing Rooms</h2>
        <!-- Display Existing Rooms in a Table -->
        <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; margin-top: 20px;">
            <thead>
                <tr>
                    <th>Room ID</th>
                    <th>Room Type</th>
                    <th>Monthly rent</th>
                    <th>Room Status</th>
                    <th>Floor</th>
                    <th>Size</th>
                    <th>Facilities</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rooms as $room): 
                            $facilities = explode(',', $room['Facilities']);
                            foreach ($facilities as $facility):
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($room['RoomID']); ?></td>
                            <td><?php echo htmlspecialchars($room['RoomType']); ?></td>
                            <td><?php echo htmlspecialchars($room['MonthlyRent']); ?></td>
                            <td><?php echo htmlspecialchars($room['RoomStatus']); ?></td>
                            <td><?php echo htmlspecialchars($room['Floor']); ?></td>
                            <td><?php echo htmlspecialchars($room['Size']); ?></td>
                            <td><?php echo htmlspecialchars(trim($facility)); ?></td>
                            <td>
                                <!-- Edit and Delete buttons -->
                                <a href="edit_room.php?id=<?php echo $room['RoomID']; ?>" class="action-btn">Edit</a> |
                                <a href="delete_room.php?id=<?php echo $room['RoomID']; ?>" onclick="return confirm('Are you sure you want to delete this room?')" class="action-btn">Delete</a>
                            </td>
                        </tr>
                        <?php 
                            endforeach;
                        endforeach; 
                        ?>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>© ChokunDormitory 2025</p>
    </div>

</body>
</html>

<script>
document.getElementById('roomTypeFilter').addEventListener('change', function() {
    const filter = this.value;
    const rows = document.querySelectorAll('table tbody tr');
    
    rows.forEach(row => {
        const roomId = row.cells[0].textContent;
        row.style.display = filter === '' || roomId === filter ? '' : 'none';
    });
});
</script>