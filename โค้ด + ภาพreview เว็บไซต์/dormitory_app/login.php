<?php
// ตรวจสอบว่าผู้ใช้เลือกประเภทการล็อกอินหรือไม่
if (isset($_GET['role'])) {
    $role = $_GET['role'];
    
    if ($role == 'admin') {
        header("Location: login_admin.php");
    } else if ($role == 'user') {
        header("Location: login_user.php");
    } else {
        echo "Invalid role selected.";
    }
} else {
    echo "Please select a role to login.";
}
?>
