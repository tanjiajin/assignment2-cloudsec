<?php
require_once('config.php');
session_start();

$customerId = $_SESSION['user_id'];

$sql = "SELECT o.order_id, o.total_amount, o.status, o.order_datetime,
               STRING_AGG(CONCAT(p.name, ' x', oi.quantity), ', ') AS items
        FROM Orders o
        JOIN Order_Items oi ON o.order_id = oi.order_id
        JOIN Products p ON oi.product_id = p.product_id
        WHERE o.customer_id = ?
        GROUP BY o.order_id, o.total_amount, o.status, o.order_datetime
        ORDER BY o.order_datetime DESC";

$stmt = sqlsrv_query($conn, $sql, [$customerId]);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order History</title>
    <link rel="stylesheet" href="main.css" />
    <style>
        .order-history-container {
            width: 90%;
            margin: 20px auto;
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f8f8;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <nav>
        <h1>Crumby Cafe</h1>
        <ul>
            <li><a href="main.html">Home</a></li>
            <li><a href="menu.php">Menu</a></li>
            <li><a href="order.php">Order</a></li>
            <li><a href="login.php" class="logout">Logout</a></li>
        </ul>
    </nav>

    <div class="order-history-container">
        <h2>Your Order History</h2>
        <table>
            <tr>
                <th>#</th>
                <th>Items</th>
                <th>Total (RM)</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
            <?php 
            $counter = 1;
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><?php echo $row['items']; ?></td>
                    <td><?php echo number_format($row['total_amount'], 2); ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td><?php echo $row['order_datetime']->format('Y-m-d H:i'); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>