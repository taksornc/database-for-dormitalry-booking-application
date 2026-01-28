<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dormitory System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ส่วนของ Navigation Bar -->
<div class="navbar">
    <a href="index.php">Home</a>
    <a href="contact.php">Contact</a>
    
    <?php
    // หากผู้ใช้งานล็อกอินเป็น Admin
    session_start();
    if (isset($_SESSION['adminID'])) {
        echo '<a href="admin/index.php">Admin Dashboard</a>';
        echo '<a href="logout.php">Logout</a>';
    }
    // หากผู้ใช้งานล็อกอินเป็น User
    elseif (isset($_SESSION['tenantID'])) {
        echo '<a href="tenant/room_booking.php">Room Booking</a>';
        echo '<a href="logout.php">Logout</a>';
    } else {
        echo '<a href="loginuser_or_admin.php">Login</a>';
    }
    ?>
</div>

