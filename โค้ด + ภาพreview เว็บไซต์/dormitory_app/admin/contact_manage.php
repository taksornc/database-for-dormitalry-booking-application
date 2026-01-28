<?php
session_start();
require_once('../db.php'); 
if (!isset($_SESSION['adminID'])) {
    header('Location: ../login_admin.php');
    exit();
}
$stmt = $pdo->prepare("UPDATE ContactMessages SET Status = 'Read' WHERE Status = 'Unread'");
$stmt->execute();

if (isset($_GET['delete'])) {
    $deleteID = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM ContactMessages WHERE MessageID = ?");
    $stmt->execute([$deleteID]);
    echo "<script>alert('Message has been deleted successfully!'); window.location.href='contact_manage.php';</script>";
}

$stmt = $pdo->prepare("SELECT * FROM ContactMessages ORDER BY MessageDate DESC");
$stmt->execute();
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Contact Messages</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Navigation Bar (Top) -->
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

    <!-- Main content area -->
    <div class="content">
        <div class="header">
            <h1>Admin - Manage Contact Messages</h1>
        </div>

        <!-- Display success message -->
        <?php if (isset($successMessage)): ?>
            <div class="message"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <!-- Display Messages in a Table -->
        <table>
            <thead>
                <tr>
                    <th>Message ID</th> <!-- เพิ่ม MessageID ที่นี่ -->
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Message</th>
                    <th>Message Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $message): ?>
                <tr>
                    <td><?php echo htmlspecialchars($message['MessageID']); ?></td> <!-- แสดง MessageID -->
                    <td><?php echo htmlspecialchars($message['Name']); ?></td>
                    <td><?php echo htmlspecialchars($message['Email']); ?></td>
                    <td><?php echo htmlspecialchars($message['Phone']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($message['Message'])); ?></td>
                    <td><?php echo htmlspecialchars($message['MessageDate']); ?></td>
                    <td>
                        <?php 
                            $statusClass = strtolower($message['Status']);
                            echo "<span class='status-badge status-{$statusClass}'>" . 
                                    htmlspecialchars($message['Status']) . 
                                 "</span>";
                        ?>
                    </td>
                    <td>
                        <!-- Delete button -->
                        <a href="contact_manage.php?delete=<?php echo $message['MessageID']; ?>" class="action-btn" onclick="return confirm('Are you sure you want to delete this message?')">Delete</a>
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


