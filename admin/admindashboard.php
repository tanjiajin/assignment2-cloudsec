<?php
require 'config.php';

function executeQuery($conn, $sql, $params = array()) {
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    return $stmt;
}

// Get all customers
$customerQuery = "SELECT customer_id, first_name, last_name, email, phone, address, password_hash,
    FORMAT(created_at, 'yyyy/MM/dd') AS AccountCreated,
    FORMAT(updated_at, 'yyyy/MM/dd') AS AccountUpdate
    FROM Customers
    ORDER BY customer_id";
$customersResult = executeQuery($conn, $customerQuery);

// Get all orders
$orderQuery = "SELECT o.order_id, o.customer_id, o.order_datetime, o.total_amount, o.status, 
                      o.notes, c.first_name, c.last_name, c.address AS delivery_address
               FROM Orders o
               JOIN Customers c ON o.customer_id = c.customer_id
               ORDER BY o.order_datetime ";
$ordersResult = executeQuery($conn, $orderQuery);

// Get all payments
$paymentQuery = "SELECT payment_id, order_id, amount, payment_method, 
                        masked_card_number, payment_status, payment_date 
                 FROM Payments 
                 ORDER BY payment_date DESC";
$paymentsResult = executeQuery($conn, $paymentQuery);

// Get all order items
$orderItemsQuery = "SELECT oi.order_item_id, oi.order_id, oi.product_id, 
                           oi.quantity, oi.unit_price, oi.special_instructions
                    FROM Order_Items oi
                    ORDER BY oi.order_item_id";
$orderItemsResult = executeQuery($conn, $orderItemsQuery);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Crumby Cafe Admin - Customer Orders</title>
    <link rel="stylesheet" href="admindashboard.css" />
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 40%;
            border-radius: 10px;
        }
        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <nav>
        <div class="sidebar">
            <div class="sidebar-header">Admin Dashboard</div>
            <ul class="sidebar-menu">
                <li><a href="admindashboard.php">Dashboard</a></li>
                <li><a href="adminauthorize.php">Staff</a></li>
                <li><a href="adminproduct.php">Product</a></li>
            </ul>
        </div>
    </nav>

    <div class="main-content">
        <div class="header">
            <h1>Customer Orders</h1>
            <button class="logout-btn" onclick="window.location.href='index.php'">Logout</button>
        </div>
       
        <!-- Customer Information Section -->
        <div class="customer-section">
            <div class="card">
                <h2>Customer Information</h2>
                <table class="customer-table">
                    <thead>
                        <tr>
                            <th>Customer ID</th>
                            <th>First name</th>
                            <th>Last name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Password Hash</th>
                            <th>Account Created</th>
                            <th>Account Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($customer = sqlsrv_fetch_array($customersResult, SQLSRV_FETCH_ASSOC)): ?>
                            <tr>
                                <td>#<?php echo $customer['customer_id']; ?></td>
                                <td><?php echo $customer['first_name']; ?></td>
                                <td><?php echo $customer['last_name']; ?></td>
                                <td><?php echo $customer['email']; ?></td>
                                <td><?php echo $customer['phone']; ?></td>
                                <td><?php echo $customer['address']; ?></td>
                                <td><?php echo $customer['password_hash']; ?></td>
                                <td><?php echo $customer['AccountCreated']; ?></td>
                                <td><?php echo $customer['AccountUpdate']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent Orders Section -->
        <div class="customer-section">
            <div class="card">
                <h2>Recent Orders</h2>
                <table class="customer-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer ID</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Delivery Address</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = sqlsrv_fetch_array($ordersResult, SQLSRV_FETCH_ASSOC)): ?>
                            <?php
                                $paymentStmt = sqlsrv_query($conn, "SELECT TOP 1 payment_status FROM Payments WHERE order_id = ? ORDER BY payment_date DESC", [$order['order_id']]);
                                $payment = sqlsrv_fetch_array($paymentStmt, SQLSRV_FETCH_ASSOC);
                                $status = $payment['payment_status'] ?? 'Pending';
                                $isPaid = $status === 'Completed';
                                $statusLabel = $isPaid ? 'Successfully Paid' : 'Pending';
                                $statusColor = $isPaid ? 'green' : 'red';
                                ?>
                            <tr>
                                <td>#<?= $order['order_id'] ?></td>
                                <td>#<?= $order['customer_id'] ?></td>
                                <td><?= $order['order_datetime']->format('Y/m/d H:i') ?></td>
                                <td>RM<?= number_format($order['total_amount'], 2) ?></td>
                                <td style="color: <?= $statusColor ?>;"><strong><?= $statusLabel ?></strong></td>
                                <td><?= $order['delivery_address'] ?></td>
                                <td><?= $order['notes'] ?></td>
                                <td>
                                    <?php if (!$isPaid): ?>
                                    <button class="btn btn-pay" data-order-id="<?= $order['order_id'] ?>" data-amount="<?= $order['total_amount'] ?>">
                                        <i class="fas fa-credit-card"></i> Pay
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                
                </table>
            </div>

            <div id="paymentModal" class="modal">
                <div class="modal-content">
                    <span class="close" id="closePaymentModal">&times;</span>
                    <h2>Enter Payment Details</h2>
                    <form method="post" action="process_payment.php">
                        <input type="hidden" name="order_id" id="payment-order-id">

                        <label>Amount (RM):</label>
                        <input type="number" name="amount" id="payment-amount" step="0.01" readonly><br><br>

                        <label>Payment Method:</label>
                        <select name="payment_method" required>
                            <option value="">-- Select --</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Debit Card">Debit Card</option>
                        </select><br><br>

                        <label>Card Last 4 Digits:</label>
                        <input type="text" name="masked_card_number" maxlength="4" pattern="\d{4}" required><br><br>

                        <input type="hidden" name="payment_status" value="Completed">

                        <button type="submit" class="btn btn-primary">Submit Payment</button>
                        <button type="button" class="btn" id="cancelPaymentBtn">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
        
        
        <!-- Payment History Section -->
        <div class="customer-section">
            <div class="card">
                <h2>Payment History</h2>
                <table class="customer-table">
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Order ID</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Transaction ID</th>
                            <th>Status</th>
                            <th>Payment Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($payment = sqlsrv_fetch_array($paymentsResult, SQLSRV_FETCH_ASSOC)): ?>
                            <tr>
                                <td>#<?php echo $payment['payment_id']; ?></td>
                                <td>#<?php echo $payment['order_id']; ?></td>
                                <td>RM<?php echo number_format($payment['amount'], 2); ?></td>
                                <td><?php echo $payment['payment_method']; ?></td>
                                <td><?php echo isset($payment['masked_card_number']) ? '#CC' . substr($payment['masked_card_number'], -3) : 'N/A'; ?></td>
                                <td><?php echo $payment['payment_status']; ?></td>
                                <td><?php echo $payment['payment_date']->format('Y/m/d H:i'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Order Items Section -->
        <div class="customer-section">
            <div class="card">
                <h2>Order Items</h2>
                <table class="customer-table">
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Order ID</th>
                            <th>Product ID</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Special Instructions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = sqlsrv_fetch_array($orderItemsResult, SQLSRV_FETCH_ASSOC)): ?>
                            <tr>
                                <td>#<?php echo $item['order_item_id']; ?></td>
                                <td>#<?php echo $item['order_id']; ?></td>
                                <td>#<?php echo $item['product_id']; ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>RM<?php echo number_format($item['unit_price'], 2); ?></td>
                                <td><?php echo $item['special_instructions']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
   <script>
        document.addEventListener('DOMContentLoaded', function () {
            const paymentModal = document.getElementById('paymentModal');
            const closePaymentModal = document.getElementById('closePaymentModal');
            const cancelPaymentBtn = document.getElementById('cancelPaymentBtn');

            document.addEventListener('click', function (e) {
                if (e.target.closest('.btn-pay')) {
                    const btn = e.target.closest('.btn-pay');
                    const orderId = btn.getAttribute('data-order-id');
                    const amount = btn.getAttribute('data-amount');

                    document.getElementById('payment-order-id').value = orderId;
                    document.getElementById('payment-amount').value = parseFloat(amount).toFixed(2);

                    paymentModal.style.display = 'block';
                }
            });

            function closePayModal() {
                paymentModal.style.display = 'none';
            }

            closePaymentModal.addEventListener('click', closePayModal);
            cancelPaymentBtn.addEventListener('click', closePayModal);

            window.addEventListener('click', function (e) {
                if (e.target === paymentModal) {
                    closePayModal();
                }
            });
        });
    </script>
        

</body>
</html>