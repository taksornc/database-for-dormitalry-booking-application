<?php
session_start();
require_once('db.php');

$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $email = $_POST['email'];
        $username = $_POST['username'];

        // ตรวจสอบว่ามีผู้ใช้ที่ตรงกับ email และ username หรือไม่
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE Email = ? AND Username = ?");
        $stmt->execute([$email, $username]);
        $user = $stmt->fetch();

        if ($user) {
            // สร้างรหัสผ่านใหม่
            $newPassword = bin2hex(random_bytes(4)); // สร้างรหัสผ่านสุ่ม 8 ตัวอักษร
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

            // อัพเดทรหัสผ่านในฐานข้อมูล
            $stmt = $pdo->prepare("UPDATE Users SET PasswordHash = ? WHERE UserID = ?");
            $stmt->execute([$passwordHash, $user['UserID']]);

            // ส่งรหัสผ่านใหม่ไปที่อีเมล (ในที่นี้จะแสดงบนหน้าเว็บแทน)
            $message = "Your new password is: " . $newPassword . "\nPlease change it after logging in.";
            $messageType = 'success';
        } else {
            $message = "No account found with this email and username combination.";
            $messageType = 'error';
        }
    } catch (Exception $e) {
        $message = "An error occurred. Please try again.";
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Reset Password</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        
        .reset-container {
            max-width: 450px;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .back-link {
            text-align: center;
            margin-top: 15px;
        }

        .back-link a {
            color: #4CAF50;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h2>Reset Password</h2>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo nl2br(htmlspecialchars($message)); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="reset_password.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <button type="submit">Reset Password</button>
        </form>

        <div class="back-link">
            <a href="login_user.php">Back to Login</a>
        </div>
    </div>
</body>
</html>