<?php
session_start();
require_once('../db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $payment_id = $_POST['payment_id'];
    $file = $_FILES['receipt'];
    
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'pdf');
    
    if (in_array($fileExt, $allowed)) {
        $uploadDir = "../uploads/receipts/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $newFileName = 'receipt_' . time() . '_' . $payment_id . '.' . $fileExt;
        $uploadPath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $stmt = $pdo->prepare("UPDATE Payment SET Receipt = ? WHERE PaymentID = ?");
            if ($stmt->execute([$newFileName, $payment_id])) {
                echo "<script>alert('Receipt uploaded successfully!'); window.location.href='payment_manage.php';</script>";
            } else {
                echo "<script>alert('Database update failed!'); window.location.href='payment_manage.php';</script>";
            }
        } else {
            echo "<script>alert('File upload failed!'); window.location.href='payment_manage.php';</script>";
        }
    } else {
        echo "<script>alert('Only JPG, PNG, GIF & PDF files are allowed!'); window.location.href='payment_manage.php';</script>";
    }
}
?>