<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['userID'])) {
    header('Location: ../login_user.php');
    exit();
}
$stmt = $pdo->prepare("
    SELECT r.RoomID, r.RoomType, r.Floor, r.Size, r.Facilities, 
           'Available' as RoomStatus
    FROM Room r
    WHERE NOT EXISTS (
        SELECT 1 
        FROM Booking b 
        WHERE b.RoomID = r.RoomID 
        AND (b.PaymentStatus = 'Paid' OR b.PaymentStatus = 'Pending' OR b.PaymentStatus = 'Completed')
        AND b.CheckOutDate >= CURRENT_DATE
    )
    ORDER BY r.RoomID
");
$stmt->execute();
$rooms = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Booking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-links">
            <div>
                <a href="room_booking.php" class="active">Rooms</a>
                <a href="booking_history.php">Booking History</a>
                <a href="lease_agreement.php">Lease Agreement </a>
                <a href="utility_bills.php">Utility Bills </a>
                <a href="monthly_payment.php">Monthly Payment</a>
                <a href="maintenance.php">Maintenance Request</a>
            </div>
            <div>
                <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="content">
        <h1><i class="fas fa-building"></i> Available Rooms</h1>

        <div class="room-container">
            <?php foreach ($rooms as $room): ?>
                <div class="room-card">
                    <div class="room-info">
                        <h3 class="room-type"><?php echo htmlspecialchars($room['RoomType']); ?></h3>
                        
                        <div class="room-status <?php echo strtolower($room['RoomStatus']) === 'available' ? 'status-available' : 'status-booked'; ?>">
                            <i class="fas <?php echo $room['RoomStatus'] === 'Available' ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                            <?php echo htmlspecialchars($room['RoomStatus']); ?>
                        </div>

                        <div class="room-details">
                            <p><i class="fas fa-building"></i> <strong>Floor:</strong> <?php echo htmlspecialchars($room['Floor']); ?></p>
                            <p><i class="fas fa-expand"></i> <strong>Size:</strong> <?php echo htmlspecialchars($room['Size']); ?> m²</p>
                        </div>

                        <div class="facilities">
                            <h4><i class="fas fa-concierge-bell"></i> Facilities:</h4>
                            <div class="facility-list">
                                <?php 
                                $facilities = explode(',', $room['Facilities']);
                                foreach ($facilities as $facility): ?>
                                    <div class="facility-item">
                                        <i class="fas fa-check"></i>
                                        <?php echo htmlspecialchars(trim($facility)); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <form method="POST" action="booking_process.php">
                            <input type="hidden" name="room_id" value="<?php echo $room['RoomID']; ?>">
                            <button type="submit" class="book-button" 
                                    <?php echo $room['RoomStatus'] === 'Booked' ? 'disabled' : ''; ?>>
                                <i class="fas <?php echo $room['RoomStatus'] === 'Available' ? 'fa-bookmark' : 'fa-lock'; ?>"></i>
                                <?php echo $room['RoomStatus'] === 'Available' ? 'Book Now' : 'Not Available'; ?>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="footer">
    <p>&copy; 2025 ChokunDormitory. All rights reserved.</p>
    </div>
            <?php endforeach; ?>
        </div>
    </div>
    </div>

    </div>
</body>
</html>


