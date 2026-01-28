<?php
require_once('db.php');  // เชื่อมต่อกับฐานข้อมูล

// ถ้าผู้ใช้ส่งข้อมูลจากฟอร์ม
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับข้อมูลจากฟอร์ม
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $message = $_POST['message'];

    // เพิ่มข้อมูลลงในตาราง ContactMessages
    $stmt = $pdo->prepare("INSERT INTO ContactMessages (Name, Email, Phone, Message, MessageDate, Status) 
                           VALUES (?, ?, ?, ?, NOW(), 'unread')");
    $stmt->execute([$name, $email, $phone, $message]);

    // แสดงข้อความว่าได้ส่งข้อความแล้ว
    echo "Your message has been sent successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - ChokunDormitory</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        .contact-container {
            max-width: 800px;
            margin: 100px auto 50px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .contact-container h1 {
            text-align: center;
            color: #333;
            font-size: 2.5em;
            margin-bottom: 30px;
        }

        .contact-form {
            display: grid;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .submit-btn {
            background: #4CAF50;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 20px;
        }

        .submit-btn:hover {
            background: #45a049;
            transform: translateY(-2px);
        }

        .success-message {
            background: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            display: none;
        }


    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="contact.php">Contact</a>
    </div>

    <!-- Contact Form -->
    <div class="contact-container">
        <h1>ติดต่อเรา</h1>
        <div id="successMessage" class="success-message">ส่งข้อความเรียบร้อยแล้ว!</div>
        <form method="POST" action="contact.php" class="contact-form" id="contactForm">
            <div class="form-group">
                <label for="name">ชื่อ:</label>
                <input type="text" name="name" id="name" required>
            </div>
            
            <div class="form-group">
                <label for="email">อีเมล:</label>
                <input type="email" name="email" id="email" required>
            </div>
            
            <div class="form-group">
                <label for="phone">เบอร์โทรศัพท์:</label>
                <input type="tel" name="phone" id="phone" pattern="[0-9]{10}" title="กรุณากรอกหมายเลขโทรศัพท์ 10 หลัก">
            </div>
            
            <div class="form-group">
                <label for="message">ข้อความ:</label>
                <textarea name="message" id="message" required></textarea>
            </div>
            
            <button type="submit" class="submit-btn">ส่งข้อความ</button>
        </form>
    </div>

    <div class="footer">
        <p>&copy; 2025 ChokunDormitory. All rights reserved.</p>
    </div>

    <script>
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('successMessage').style.display = 'block';
                this.reset();
                setTimeout(() => {
                    document.getElementById('successMessage').style.display = 'none';
                }, 3000);
            })
            .catch(error => console.error('Error:', error));
        });
    </script>
</body>
</html>

