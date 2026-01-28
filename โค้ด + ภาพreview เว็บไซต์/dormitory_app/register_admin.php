<?php
session_start();
require_once('db.php');  


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    if ($password !== $confirmPassword) {
        $errorMessage = "Passwords do not match!";
    } else {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
        $stmt = $pdo->prepare("INSERT INTO Admin (Username, PasswordHash, Email, Phone, Status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute([$username, $passwordHash, $email, $phone]);
        $successMessage = "Admin Registration successful!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Admin Register</title>
    <style>
        /* Basic styles for body */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Header styles */
        header {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 20px 0;
            font-size: 24px;
            letter-spacing: 2px;
        }

        h1 {
            margin: 0;
        }

        /* Register Form Container */
        .register-container {
            width: 100%;
            max-width: 450px;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .register-container h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        /* Form elements styles */
        input[type="text"], input[type="email"], input[type="password"], input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border-radius: 4px;
            border: 1px solid #ddd;
            box-sizing: border-box;
            font-size: 16px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        /* Error and Success Message */
        .message {
            text-align: center;
            padding: 10px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            margin: 20px 0;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>

    <header>
        <h1>Admin Registration</h1>
    </header>

    <div class="register-container">
        <h2>Register as Admin</h2>

        <!-- Display Error or Success Message -->
        <?php if (isset($errorMessage)): ?>
            <div class="message"><?php echo $errorMessage; ?></div>
        <?php elseif (isset($successMessage)): ?>
            <div class="message success-message"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form method="POST" action="register_admin.php">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required placeholder="Enter your username">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required placeholder="Enter your email address">

            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" placeholder="Enter your phone number">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Create a password">

            <label for="confirmPassword">Confirm Password</label>
            <input type="password" id="confirmPassword" name="confirmPassword" required placeholder="Confirm your password">

            <input type="submit" value="Register">
        </form>
    </div>

</body>
</html>


