<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chokun Dormitory</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="contact.php">Contact</a>
        <a href="login_user.php">Login</a>
        <a href="register_user.php">Register as User</a>
    </div>

    <div class="hero-section">
        <h1>Welcome to Dormitory</h1>
        <p>หอพักราคาสุดคุ้ม ใกล้ๆกับ KMITL เดินทางสะดวก</p>
    </div>

    <div class="room-showcase">
        <h2>Our Room Types</h2>
        <div class="room-cards">
            <div class="room-card">
                <img src="https://www.collinsdictionary.com/images/full/singleroom_713511961_1000.jpg" alt="Fan Room">
                <div class="room-info">
                    <h3>Single Room</h3>
                    <ul>
                        <li><i class="fas fa-user"></i> 1 คน</li>
                        <li><i class="fas fa-snowflake"></i> ห้องแอร์ </li>
                        <li><i class="fas fa-bed"></i>  1 เตียง </li>
                        <li><i class="fa-solid fa-kitchen-set"></i> ตู้เย็น </li>
                        </ul>
                    <div class="price">4000/เดือน</div>
                </div>
            </div>

            <div class="room-card">
                <img src="https://cf.bstatic.com/xdata/images/hotel/max1024x768/390657179.jpg?k=7c41c351cb7be1489311247b7bc4a0b73a846dfa27dabe53f15699842a03e507&o=&hp=1" alt="AC Room">
                <div class="room-info">
                    <h3>Double Room</h3>
                    <ul>
                        <li><i class="fas fa-user"></i> 2 คน</li>
                        <li><i class="fas fa-snowflake"></i> ห้องแอร์ </li>
                        <li><i class="fas fa-bed"></i> 2 เตียง </li>
                        <li><i class="fa-solid fa-kitchen-set"></i> ตู้เย็น </li>
                    </ul>
                    <div class="price">฿6000/เดือน</div>
                </div>
            </div>

            <div class="room-card">
                <img src="https://www.xn--12c7bhaw4iemu7j3c5c.com/upload/r1529145581.jpg" alt="Triple Room">
                <div class="room-info">
                    <h3>Tripple Room</h3>
                    <ul>
                        <li><i class="fas fa-users"></i> 3 คน</li>
                        <li><i class="fas fa-snowflake"></i> ห้องแอร์ </li>
                        <li><i class="fas fa-bed"></i> 3 เตียง </li>
                        <li><i class="fa-solid fa-kitchen-set"></i> ตู้เย็น </li>
                    </ul>
                    <div class="price">฿9,000/เดือน</div>
                </div>
            </div>
        </div>
    </div>

    <div class="features-section">
        <h2>Why Choose Us?</h2>
        <div class="features-grid">
            <div class="feature">
                <i class="fas fa-wifi"></i>
                <h3>Free WiFi</h3>
                <p>High-speed internet in all rooms</p>
            </div>
            <div class="feature">
                <i class="fas fa-shield-alt"></i>
                <h3>24/7 Security</h3>
                <p>CCTV and keycard access</p>
            </div>
            <div class="feature">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Prime Location</h3>
                <p>Near universities and shopping centers</p>
            </div>
        </div>
    </div>

    <div class="cta-section">
        <h2>Ready to Join Us?</h2>
        <p>Book your perfect room today!</p>
        <div class="cta-buttons">
            <a href="register_user.php" class="cta-btn register">Register Now</a>
            <a href="login_user.php" class="cta-btn login">Login</a>
        </div>
    </div>
    <div class="footer">
        <p>&copy; 2025 Dormitory. All rights reserved.</p>
    </div>
</body>
</html>

