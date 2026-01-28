<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['userID'])) {
    header('Location: ../login_user.php');
    exit();
}

// แจ้งซ่อม
$stmt = $pdo->prepare("
    SELECT r.*, b.BookingID 
    FROM Room r 
    INNER JOIN Booking b ON r.RoomID = b.RoomID 
    INNER JOIN Tenant t ON b.TenantID = t.TenantID 
    WHERE t.UserID = ? 
    AND b.CheckOutDate >= CURRENT_DATE
");
$stmt->execute([$_SESSION['userID']]);
$rooms = $stmt->fetchAll();


$stmt = $pdo->prepare("SELECT TenantID FROM Tenant WHERE UserID = ?");
$stmt->execute([$_SESSION['userID']]);
$tenant = $stmt->fetch();

// Add error reporting at the top after session_start()
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Update the try-catch block for better error handling
// Update the SQL query to match the actual database structure
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_request'])) {
    try {
        // Check if tenant exists
        if (!isset($tenant['TenantID']) || empty($tenant['TenantID'])) {
            throw new Exception("Tenant ID not found. Please contact support.");
        }
        
        // Modified query - removed TenantID if it doesn't exist in the table
        $stmt = $pdo->prepare("
            INSERT INTO MaintenanceRequest (TenantID, RoomID, RequestDate, IssueDescription, Status) 
            VALUES (?, ?, CURRENT_DATE(), ?, 'Pending')
        ");
        $stmt->execute([
            $tenant['TenantID'],
            $_POST['room_id'],
            $_POST['issue_description']
        ]);
        $success_message = "Maintenance request submitted successfully!";
    } catch (Exception $e) {
        $error_message = "Error details: " . $e->getMessage();
        error_log($e->getMessage()); // Log the error message for debugging
    }
}

// Fetch existing maintenance requests for the user
// แก้ไข query การดึงข้อมูล maintenance requests
$stmt = $pdo->prepare("
    SELECT mr.*, r.RoomType, r.Floor, r.RoomID 
    FROM MaintenanceRequest mr 
    INNER JOIN Room r ON mr.RoomID = r.RoomID 
    INNER JOIN Tenant t ON mr.TenantID = t.TenantID 
    WHERE t.UserID = ? 
    ORDER BY mr.RequestDate DESC
");
$stmt->execute([$_SESSION['userID']]);
$maintenance_requests = $stmt->fetchAll();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Request</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
    </style>
</head>
<body>
<nav class="navbar">
        <div class="nav-links">
            <div>
                <a href="room_booking.php">Rooms</a>
                <a href="booking_history.php">Booking History</a>
                <a href="lease_agreement.php">Lease Agreement </a>
                <a href="utility_bills.php">Utility Bills </a>
                <a href="monthly_payment.php">Monthly Payment</a>
                <a href="maintenance.php" class="active">Maintenance Request</a>
            </div>
            <div>
                <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="maintenance-container">
        <h1>Maintenance Request</h1>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="request-form">
            <h2>Submit New Request</h2>
            <?php if (empty($rooms)): ?>
                <p>You don't have any active room bookings.</p>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="room_id">The room you rent:</label>
                        <select name="room_id" id="room_id" required>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo $room['RoomID']; ?>">
                                    Room <?php echo htmlspecialchars($room['RoomID']); ?> - 
                                    Floor <?php echo htmlspecialchars($room['Floor']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="issue_description">Issue Description:</label>
                        <textarea name="issue_description" id="issue_description" rows="4" required></textarea>
                    </div>
                    <input type="hidden" name="tenant_id" value="<?php echo $tenant['TenantID']; ?>">
                    <button type="submit" name="submit_request" class="btn-submit">Submit Request</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="request-history">
            <h2>Request History</h2>
            <?php if (empty($maintenance_requests)): ?>
                <p>No maintenance requests found.</p>
            <?php else: ?>


                <?php foreach ($maintenance_requests as $request): ?>
                    <div class="request-item">
                        <h3 class="room-id">Room <?php echo htmlspecialchars($request['RoomID']); ?></h3>
                        <p><strong>Room Type:</strong> <?php echo htmlspecialchars($request['RoomType']); ?></p>
                        <p><strong>Floor:</strong> <?php echo htmlspecialchars($request['Floor']); ?></p>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars($request['RequestDate']); ?></p>
                        <p><strong>Issue:</strong> <?php echo htmlspecialchars($request['IssueDescription']); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="status-<?php echo strtolower($request['Status']); ?>">
                                <?php echo htmlspecialchars($request['Status']); ?>
                            </span>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="footer">
    <p>&copy; 2025 ChokunDormitory. All rights reserved.</p>
    </div>
</body>
</html>