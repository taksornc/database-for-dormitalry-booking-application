<?php
session_start();
// ล้างข้อมูล session ทั้งหมด
session_unset();
session_destroy();

// ส่งกลับไปยังหน้า login
header('Location: ../login_user.php');
exit();
?>