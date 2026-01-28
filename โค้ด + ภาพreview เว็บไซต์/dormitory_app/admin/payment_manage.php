<?php
session_start();
require_once('../db.php'); 
if (!isset($_SESSION['adminID'])) {
    header('Location: ../login_admin.php');
    exit();
}
if (isset($_GET['delete'])) {
    $deleteID = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM Payment WHERE PaymentID = ?");
    $stmt->execute([$deleteID]);
    echo "<script>alert('Payment record has been deleted successfully!'); window.location.href='payment_manage.php';</script>";
}

$stmt = $pdo->prepare("
    SELECT p.PaymentID, p.TenantID, p.BookingID, p.UtilityID,
           p.PaymentType, p.AmountPaid, p.LateFee,
           p.PaymentDate, p.PaymentMethod, p.PaymentStatus, p.Receipt,
           DATE_FORMAT(p.PaymentDate, '%d/%m/%Y') as FormattedDate
    FROM Payment p
    LEFT JOIN Booking b ON p.BookingID = b.BookingID
    LEFT JOIN utilityusage u ON p.UtilityID = u.UtilityID
    ORDER BY p.PaymentDate DESC");

$stmt->execute();
$payments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Payment Management</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .payment-status-paid {
            background-color:rgb(138, 217, 156);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }
        
        .payment-status-pending {
            background-color: #ffc107;
            color: black;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }

        .payment-status-failed {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }

        .amount {
            font-weight: bold;
            color: #28a745;
        }

        /* Remove these styles */
        .filter-section {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .filter-section select {
            padding: 5px;
            margin-right: 10px;
        }
        
        .btn-view {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9em;
        }
        
        .btn-view:hover {
            background-color: #0056b3;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
        }

        .modal-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 90vh;
            margin-top: 2%;
        }

        .close {
            position: absolute;
            right: 35px;
            top: 15px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }

        #receipt-image {
            width: auto;
            height: auto;
            max-width: 100%;
            max-height: 90vh;
        }

        /* Add these table styles */
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            background: white;
        }

        th, td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="tenant_manage.php">Tenants</a>
        <a href="room_manage.php">Rooms</a>
        <a href="booking_manage.php">Bookings</a>
        <a href="lease_manage.php">Lease Agreements</a>
        <a href="payment_manage.php">Payments</a>
        <a href="utility_manage.php">Utility Usage</a>
        <a href="maintenance_manage.php">Maintenance</a>
        <a href="staff_manage.php">Staff</a>
        <a href="contact_manage.php">Messages</a>
        <a href="../login_admin.php">Logout</a>
    </div>

    <div class="content">
        <div class="header">
            <h1>Payment Management</h1>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Tenant ID</th>
                    <th>Booking ID</th>
                    <th>Utility ID</th>
                    <th>Payment Type</th>
                    <th>Amount Paid</th>
                    <th>Late Fee</th>
                    <th>Payment Date</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Receipt</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $payment): ?>
                <tr class="payment-row">
                    <td><?php echo htmlspecialchars($payment['PaymentID'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($payment['TenantID'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($payment['BookingID'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($payment['UtilityID'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($payment['PaymentType'] ?? ''); ?></td>
                    <td class="amount">฿<?php echo number_format($payment['AmountPaid'] ?? 0, 2); ?></td>
                    <td class="amount">฿<?php echo number_format($payment['LateFee'] ?? 0, 2); ?></td>
                    <td><?php echo $payment['FormattedDate'] ?? ''; ?></td>
                    <td><?php echo htmlspecialchars($payment['PaymentMethod'] ?? ''); ?></td>
                    <td>
                        <span class="payment-status-<?php echo strtolower($payment['PaymentStatus']); ?>">
                            <?php echo htmlspecialchars($payment['PaymentStatus']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if (!empty($payment['Receipt']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/dormitory_app/uploads/receipts/' . $payment['Receipt'])): ?>
                            <button type="button" class="btn-view" onclick="window.open('/dormitory_app/uploads/receipts/<?php echo htmlspecialchars($payment['Receipt']); ?>', '_blank')">
                                <i class="fas fa-file-invoice"></i> View Receipt
                            </button>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form action="upload_receipt.php" method="POST" enctype="multipart/form-data" style="display: inline;">
                            <input type="hidden" name="payment_id" value="<?php echo $payment['PaymentID']; ?>">
                            <input type="file" name="receipt" 
                                   accept=".jpg,.jpeg,.png,.gif,.pdf" 
                                   style="display: none;" 
                                   onchange="validateFile(this)">
                            <button type="button" class="btn-upload" onclick="this.previousElementSibling.click()">
                                <i class="fas fa-upload"></i> Upload Receipt
                            </button>
                        </form>
                        <a href="payment_manage.php?delete=<?php echo $payment['PaymentID']; ?>" 
                           class="action-btn" 
                           onclick="return confirm('Are you sure you want to delete this payment record?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>© ChokunDormitory 2025</p>
    </div>

    <!-- Modal -->
    <div id="receiptModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="receipt-image">
    </div>

    <script>
        document.getElementById('statusFilter')?.addEventListener('change', filterPayments);

        function filterPayments() {
            const statusFilter = document.getElementById('statusFilter').value;
            const rows = document.getElementsByClassName('payment-row');

            for (let row of rows) {
                const status = row.dataset.status;
                const statusMatch = statusFilter === 'all' || status === statusFilter;
                row.style.display = statusMatch ? '' : 'none';
            }
        }

        function showReceipt(imagePath) {
            const modal = document.getElementById('receiptModal');
            const modalImg = document.getElementById('receipt-image');
            modal.style.display = 'block';
            modalImg.src = imagePath;
        }

        function closeModal() {
            document.getElementById('receiptModal').style.display = 'none';
        }

        // Close modal when clicking outside the image
        window.onclick = function(event) {
            const modal = document.getElementById('receiptModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

    function validateFile(input) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        const file = input.files[0];
        
        if (file && allowedTypes.includes(file.type)) {
            input.form.submit();
        } else {
            alert('Only JPG, PNG, GIF & PDF files are allowed.');
            input.value = '';
        }
    }
    </script>
</body>
</html>