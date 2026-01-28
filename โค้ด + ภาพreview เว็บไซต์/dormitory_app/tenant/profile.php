<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['userID'])) {
    header('Location: ../login_user.php');
    exit();
}

$userID = $_SESSION['userID'];
$message = '';

// ดึงข้อมูลประวัติการชำระเงิน
$stmt = $pdo->prepare("
    SELECT p.*, b.RoomID, r.RoomType,
           DATE_FORMAT(p.PaymentDate, '%d/%m/%Y') as FormattedDate
    FROM Payment p
    JOIN Booking b ON p.BookingID = b.BookingID
    JOIN Room r ON b.RoomID = r.RoomID
    WHERE p.TenantID = ?
    ORDER BY p.PaymentDate DESC
    LIMIT 5");
$stmt->execute([$userID]);
$payments = $stmt->fetchAll();

// ดึงข้อมูลผู้ใช้
$stmt = $pdo->prepare("SELECT * FROM Users WHERE UserID = ?");
$stmt->execute([$userID]);
$user = $stmt->fetch();

// จัดการการอัพเดทข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // ตรวจสอบรหัสผ่านปัจจุบัน
        if (!empty($currentPassword)) {
            if (!password_verify($currentPassword, $user['PasswordHash'])) {
                throw new Exception("Current password is incorrect");
            }

            // ตรวจสอบรหัสผ่านใหม่
            if ($newPassword !== $confirmPassword) {
                throw new Exception("New passwords do not match");
            }

            if (strlen($newPassword) < 6) {
                throw new Exception("Password must be at least 6 characters");
            }

            // อัพเดทข้อมูลพร้อมรหัสผ่านใหม่
            $stmt = $pdo->prepare("UPDATE Users SET Email = ?, Phone = ?, PasswordHash = ? WHERE UserID = ?");
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt->execute([$email, $phone, $passwordHash, $userID]);
        } else {
            // อัพเดทข้อมูลโดยไม่เปลี่ยนรหัสผ่าน
            $stmt = $pdo->prepare("UPDATE Users SET Email = ?, Phone = ? WHERE UserID = ?");
            $stmt->execute([$email, $phone, $userID]);
        }

        $message = "Profile updated successfully!";
        
        // อัพเดทข้อมูลในตัวแปร user
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE UserID = ?");
        $stmt->execute([$userID]);
        $user = $stmt->fetch();

    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .password-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #ddd;
        }
        .payment-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #ddd;
        }
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background-color: white;
            box-shadow: 0 0 5px rgba(0,0,0,0.05);
            border-radius: 4px;
            overflow: hidden;
        }
        .payment-table th {
            background-color: #8B0000;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .payment-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        .payment-table tr:hover {
            background-color: #f5f5f5;
        }
        .amount {
            font-weight: bold;
            color: #28a745;
        }
        .receipt-link {
            color: #007bff;
            text-decoration: none;
        }
        .receipt-link:hover {
            text-decoration: underline;
        }
        .view-all {
            text-align: right;
            margin-top: 1rem;
        }
        .view-all-link {
            color: #8B0000;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .view-all-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-links">
            <div>
                <a href="room_booking.php">Rooms</a>
                <a href="booking_history.php">Booking History</a>
                <a href="lease_agreement.php">Lease Agreement</a>
                <a href="utility_bills.php">Utility Bills</a>
                <a href="maintenance.php">Maintenance Request</a>
            </div>
            <div>
                <a href="profile.php" class="active"><i class="fas fa-user"></i> Profile</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="profile-container">
        <h1><i class="fas fa-user-circle"></i> User Profile</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : ''; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="profile.php">
            <div class="form-group">
                <label>Username</label>
                <input type="text" value="<?php echo htmlspecialchars($user['Username']); ?>" disabled>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['Phone']); ?>">
            </div>

            <div class="password-section">
                <h3>Change Password</h3>
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password">
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
            </div>

            <button type="submit" class="book-button">Update Profile</button>
        </form>

        <div class="payment-section">
            <h3><i class="fas fa-history"></i> ประวัติการชำระเงินล่าสุด</h3>
            <table class="payment-table">
                <thead>
                    <tr>
                        <th>วันที่ชำระ</th>
                        <th>ห้อง</th>
                        <th>จำนวนเงิน</th>
                        <th>วิธีการชำระเงิน</th>
                        <th>ใบเสร็จ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?php echo $payment['FormattedDate']; ?></td>
                        <td><?php echo htmlspecialchars($payment['RoomType']); ?></td>
                        <td class="amount">฿<?php echo number_format($payment['AmountPaid'], 2); ?></td>
                        <td><?php echo htmlspecialchars($payment['PaymentMethod']); ?></td>
                        <td>
                            <?php if ($payment['Receipt']): ?>
                                <a href="../uploads/receipts/<?php echo $payment['Receipt']; ?>" 
                                   target="_blank" 
                                   class="receipt-link">ดูใบเสร็จ</a>
                            <?php else: ?>
                                ไม่มีใบเสร็จ
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="view-all">
                <a href="payment_history.php" class="view-all-link">ดูประวัติการชำระเงินทั้งหมด</a>
            </div>
        </div>
    </div>
    </div>

    <div class="footer">
    <p>&copy; 2025 ChokunDormitory. All rights reserved.</p>
</div>
    </div>
</body>
</html>