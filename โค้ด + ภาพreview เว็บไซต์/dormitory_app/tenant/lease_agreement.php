<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['userID'])) {
    header('Location: ../login_user.php');
    exit();
}

// ดึงข้อมูลสัญญาเช่า
$sql = "SELECT l.*, l.Terms as LeaseTerms, t.FirstName, t.LastName, t.Email, t.Phone, 
        t.IDCardNumber, t.Address, r.RoomType, r.Floor, r.RoomID, b.BookingDate
        FROM lease_agreement l
        JOIN tenant t ON l.TenantID = t.TenantID
        JOIN booking b ON l.BookingID = b.BookingID
        JOIN Room r ON b.RoomID = r.RoomID
        WHERE t.UserID = ?
        AND b.CheckOutDate >= CURRENT_DATE
        ORDER BY l.CreatedAt DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['userID']]);
$leases = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลสัญญาเช่า</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
        }
        .lease-card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            background-color: white;
            transition: transform 0.2s;
        }
        .lease-card:hover {
            transform: translateY(-5px);
        }
        .lease-header {
            background-color: #006699;
            color: white;
            padding: 15px;
            border-radius: 15px 15px 0 0;
        }
        .lease-body {
            padding: 20px;
        }
        .lease-info {
            margin-bottom: 15px;
        }
        .lease-footer {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 0 0 15px 15px;
        }
        .badge-status {
            font-size: 0.9rem;
            padding: 8px 12px;
            border-radius: 20px;
        }
        .info-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 1.1rem;
            font-weight: 500;
        }
        .btn-action {
            margin-right: 5px;
            border-radius: 20px;
            padding: 8px 15px;
        }
    </style>
</head>
<body>
<nav class="navbar">
        <div class="nav-links">
            <div>
                <a href="room_booking.php">Rooms</a>
                <a href="booking_history.php">Booking History</a>
                <a href="lease_agreement.php"class="active">Lease Agreement</a>
                <a href="utility_bills.php">Utility Bills</a>
                <a href="monthly_payment.php">Monthly Payment</a>
                <a href="maintenance.php">Maintenance Request</a>
            </div>
            <div>
                <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>
    <div class="content">
        <h1 class="text-center mb-4">Lease Agreement History</h1>
        
        <div class="row">
            <?php if (!empty($leases)) : ?>
                <?php foreach ($leases as $lease) : 
                    // Calculate lease status
                    $today = new DateTime();
                    $endDate = new DateTime($lease['EndDate']);
                    $daysDiff = $endDate->diff($today)->days;
                    
                    if ($today > $endDate) {
                        $status = "Lease Expired";
                        $statusClass = "danger";
                    } else if ($daysDiff <= 30) {
                        $status = "Lease Expiring Soon";
                        $statusClass = "warning";
                    } else {
                        $status = "";
                        $statusClass = "";
                    }
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="lease-card">
                        <div class="lease-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">LeaseID: <?php echo $lease['LeaseID']; ?></h5>
                                <div>
                                    <?php if ($status): ?>
                                    <span class="badge bg-<?php echo $statusClass; ?> badge-status">
                                        <?php echo $status; ?>
                                    </span>
                                    <?php endif; ?>
                                    <span class="badge bg-info badge-status ms-2">
                                        Room <?php echo $lease['RoomID']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="lease-body">
                            <div class="lease-info">
                                <div class="info-label">Tenant Information</div>
                                <div class="info-value">
                                    <?php echo $lease['FirstName'] . ' ' . $lease['LastName']; ?><br>
                                    <small>ID Card: <?php echo $lease['IDCardNumber']; ?><br>
                                    Phone: <?php echo $lease['Phone']; ?><br>
                                    Address: <?php echo $lease['Address']; ?></small>
                                </div>
                            </div>
                            <div class="lease-info">
                                <div class="info-label">Room Details</div>
                                <div class="info-value">
                                    <?php echo $lease['RoomType'] . ' Floor ' . $lease['Floor']; ?>
                                </div>
                            </div>
                            <div class="lease-info">
                                <div class="info-label">Lease Period</div>
                                <div class="info-value">
                                    <?php echo date('d/m/Y', strtotime($lease['StartDate'])); ?> - 
                                    <?php echo date('d/m/Y', strtotime($lease['EndDate'])); ?>
                                </div>
                            </div>
                            <div class="lease-info">
                                <div class="info-label">Booking Date</div>
                                <div class="info-value">
                                    <?php echo date('d/m/Y', strtotime($lease['BookingDate'])); ?>
                                </div>
                            </div>
                            <div class="lease-info">
                                <div class="info-label">Monthly Rent</div>
                                <div class="info-value">
                                    <?php echo number_format($lease['RentAmount'], 2); ?> THB
                                </div>
                            </div>
                            <div class="lease-info">
                                <div class="info-label">DepositAmount</div>
                                <div class="info-value">
                                    <?php echo number_format($lease['DepositAmount'], 2); ?> THB
                                </div>
                            </div>
                            <div class="lease-info">
                                <div class="info-label">Lease Terms</div>
                                <div class="info-value">
                                    <?php echo nl2br(htmlspecialchars($lease['LeaseTerms'])); ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        ไม่พบข้อมูลสัญญาเช่า
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS and Icons -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
</body>
</html>

