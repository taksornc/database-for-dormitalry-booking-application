<?php
session_start();
require_once('db.php');  

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

    $stmt = $pdo->prepare("SELECT * FROM Admin WHERE Username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['PasswordHash'])) {
        $_SESSION['adminID'] = $admin['AdminID'];
        $_SESSION['adminUsername'] = $admin['Username'];

        header('Location: admin/index.php');  
        exit;
    } else {
        $errorMessage = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <title>Admin Login</title>
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

        /* Login Form Container */
        .login-container {
            width: 100%;
            max-width: 450px;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .login-container h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        /* Form elements styles */
        input[type="text"], input[type="password"], input[type="submit"] {
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

        /* Error Message */
        .message {
            text-align: center;
            padding: 10px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>

    <header>
        <h1>Admin Login</h1>
    </header>

    <!-- Login Form Container -->
    <div class="login-container">
        <h2>Login</h2>

        <!-- Display Error Message -->
        <?php if (isset($errorMessage)): ?>
            <div class="message"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="login_admin.php">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required placeholder="Enter your username">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Enter your password">

            <input type="submit" value="Login">
        </form>
    </div>

    <div class="footer">
        <p>&copy; 2025 ChokunDormitory. All rights reserved.</p>
    </div>
</body>
</html>


