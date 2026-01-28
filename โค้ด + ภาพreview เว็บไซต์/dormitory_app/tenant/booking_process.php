<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['userID'])) {
    header('Location: ../login_user.php');
    exit();
}

if (!isset($_POST['room_id']) && !isset($_SESSION['selected_room'])) {
    header('Location: room_booking.php');
    exit();
}

if (isset($_POST['room_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM Room WHERE RoomID = ?");
    $stmt->execute([$_POST['room_id']]);
    $_SESSION['selected_room'] = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['first_name'])) {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT UserID FROM Users WHERE UserID = ?");
        $stmt->execute([$_SESSION['userID']]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception("Invalid user session. Please log in again.");
        }

        // Check if tenant already exists for this user
        $stmt = $pdo->prepare("SELECT TenantID FROM Tenant WHERE UserID = ?");
        $stmt->execute([$_SESSION['userID']]);
        $existingTenant = $stmt->fetch();
        
        if ($existingTenant) {
            // Use existing tenant
            $tenantID = $existingTenant['TenantID'];
            
            // Update existing tenant information
            $stmt = $pdo->prepare("UPDATE Tenant SET FirstName = ?, LastName = ?, Email = ?, 
                                  Phone = ?, IDCardNumber = ?, Address = ? WHERE TenantID = ?");
            $stmt->execute([
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['id_card_number'],
                $_POST['address'],
                $tenantID
            ]);
        } else {
            // Insert new tenant with verified UserID
            $stmt = $pdo->prepare("INSERT INTO Tenant (UserID, FirstName, LastName, Email, Phone, IDCardNumber, Address) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user['UserID'], // Use verified UserID
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['id_card_number'],
                $_POST['address']
            ]);
            
            $tenantID = $pdo->lastInsertId();
        }
        
        // Calculate total amount and deposit
        $monthlyRent = $_SESSION['selected_room']['MonthlyRent'];
        $depositAmount = 2000;
        $advancePaid = $monthlyRent;
        $totalAmount = $depositAmount + $advancePaid;
        
        // Create booking record
        $stmt = $pdo->prepare("INSERT INTO Booking (TenantID, RoomID, BookingDate, CheckInDate, CheckOutDate, 
                              TotalAmount, DepositPaid, AdvancePaid, PaymentStatus) 
                              VALUES (?, ?, CURRENT_DATE(), ?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([
            $tenantID,
            $_SESSION['selected_room']['RoomID'],
            $_POST['check_in_date'],
            $_POST['check_out_date'],
            $totalAmount,
            $depositAmount,
            $advancePaid
        ]);
        
        $bookingID = $pdo->lastInsertId();
        
        // Insert lease agreement
        $stmt = $pdo->prepare("INSERT INTO lease_agreement (TenantID, BookingID, StartDate, EndDate, RentAmount, DepositAmount, Terms) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $tenantID,
            $bookingID,
            $_POST['lease_start_date'],
            $_POST['lease_end_date'],
            $_POST['rent_amount'],
            $_POST['deposit_amount'],
            $_POST['terms']
        ]);
        
        $leaseID = $pdo->lastInsertId();
        
        // Update room status to booked
        $stmt = $pdo->prepare("UPDATE Room SET RoomStatus = 'booked' WHERE RoomID = ?");
        $stmt->execute([$_SESSION['selected_room']['RoomID']]);
        
        $_SESSION['tenant_id'] = $tenantID;
        $_SESSION['booking_id'] = $bookingID;
        $_SESSION['total_amount'] = $totalAmount;
        $_SESSION['deposit_amount'] = $depositAmount;
        
        $pdo->commit();
        header('Location: payment.php');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Booking Error: " . $e->getMessage());
        $error = "An error occurred during booking. Please try again. Error: " . $e->getMessage();
    }
}

$room = $_SESSION['selected_room'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Process</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .booking-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .room-summary, .total-calculation {
            background-color: #f8f9fa;
            padding: 25px;
            margin: 0 0 30px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="booking-container">
        <h1>Booking Process</h1>
        
        <div class="room-summary">
            <h3>Selected Room Details</h3>
            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($room['RoomType']); ?></p>
            <p><strong>Floor:</strong> <?php echo htmlspecialchars($room['Floor']); ?></p>
            <p><strong>Deposit paid:</strong> ฿2,000.00</p>
            <p><strong>Advance paid:</strong> ฿<?php echo number_format($room['MonthlyRent'], 2); ?></p>
            <p><strong>Total Amount:</strong> ฿<?php echo number_format(2000 + $room['MonthlyRent'], 2); ?></p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="booking_process.php" id="bookingForm">
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>
            
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="text" id="phone" name="phone" required>
            </div>

            <div class="form-group">
                <label for="id_card_number">IDCardNumber:</label>
                <input type="text" id="id_card_number" name="id_card_number" required>
            </div>

            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" required></textarea>
            </div>

            <div class="form-group">
                <label for="check_in_date">Check-in Date:</label>
                <input type="date" id="check_in_date" name="check_in_date" required 
                       min="<?php echo date('Y-m-d'); ?>" onchange="calculateTotal()">
            </div>

            <div class="form-group">
                <label for="check_out_date">Check-out Date:</label>
                <input type="date" id="check_out_date" name="check_out_date" required
                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" onchange="calculateTotal()">
            </div>
            
            <h3>Lease Agreement Information</h3>
            
            <div class="form-group">
                <label for="lease_start_date">Lease Start Date:</label>
                <input type="date" id="lease_start_date" name="lease_start_date" required 
                       min="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="form-group">
                <label for="lease_end_date">Lease End Date:</label>
                <input type="date" id="lease_end_date" name="lease_end_date" required
                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
            </div>
            
            <div class="form-group">
                <label for="rent_amount">Monthly Rent (THB):</label>
                <input type="number" id="rent_amount" name="rent_amount" required value="<?php echo (int)$room['MonthlyRent']; ?>" step="1" readonly>
            </div>
            
            <div class="form-group">
                <label for="deposit_amount">Deposit Amount (THB):</label>
                <input type="number" id="deposit_amount" name="deposit_amount" required value="2000" step="0.01" readonly>
            </div>
            
            <div class="form-group">
                <label for="terms">Lease Terms and Conditions:</label>
                <textarea id="terms" name="terms" required rows="5" readonly>1. ผู้เช่าต้องชำระค่าเช่าล่วงหน้าทุกเดือน
2. ผู้เช่าต้องรักษาความสะอาดและความเป็นระเบียบเรียบร้อยของห้องพัก
3. ห้ามนำสัตว์เลี้ยงเข้ามาในห้องพัก
4. ห้ามส่งเสียงดังรบกวนผู้อื่น
5. ผู้เช่าต้องรับผิดชอบค่าซ่อมแซมหากทำให้ทรัพย์สินเสียหาย
6. ผู้เช่าต้องแจ้งล่วงหน้าอย่างน้อย 30 วันก่อนย้ายออก
7. ผู้เช่าต้องปฏิบัติตามกฎระเบียบของหอพักอย่างเคร่งครัด</textarea>
            </div>

            <div id="totalAmount" class="total-calculation" style="display: none;">
                <h4>Booking Summary</h4>
                <p id="rentCalculation"></p>
                <p id="depositAmount"></p>
                <p><strong>Total Amount: </strong><span id="totalAmountValue"></span></p>
            </div>
            
            <button type="submit" class="btn btn-primary">Proceed to Payment</button>
        </form>
    </div>
    </div>

    <div class="footer">
    <p>&copy; 2025 ChokunDormitory. All rights reserved.</p>
</div>

    <script>
        function calculateTotal() {
            const checkInDate = new Date(document.getElementById('check_in_date').value);
            const checkOutDate = new Date(document.getElementById('check_out_date').value);
            const monthlyRent = <?php echo $room['MonthlyRent']; ?>;
            
            if (checkInDate && checkOutDate && checkOutDate > checkInDate) {
                const deposit = 2000;
                const advance = monthlyRent;
                const total = deposit + advance;

                document.getElementById('rentCalculation').innerHTML = 
                    `Deposit Amount = ฿${deposit.toLocaleString()}`;
                document.getElementById('depositAmount').innerHTML = 
                    `Advance paid = ฿${advance.toLocaleString()}`;
                document.getElementById('totalAmountValue').innerHTML = 
                    `฿${total.toLocaleString()}`;
                document.getElementById('totalAmount').style.display = 'block';
            }
        }

        document.getElementById('check_in_date').addEventListener('change', function() {
            document.getElementById('check_out_date').min = this.value;
            calculateTotal();
        });

        document.getElementById('check_out_date').addEventListener('change', calculateTotal);
    </script>
</body>
</html>


