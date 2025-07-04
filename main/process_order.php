<?php
require_once('config.php');
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderItems = $_POST['products'] ?? [];
    
    if (empty($orderItems)) {
        die("No items in order");
    }
    
    // Calculate total amount
    $total = 0;
    foreach ($orderItems as $item) {
        $productId = $item['product_id'];
        $quantity = $item['quantity'];
        
        // Get product price from database to prevent tampering
        $sql = "SELECT price FROM Products WHERE product_id = ?";
        $params = [$productId];
        $stmt = sqlsrv_query($conn, $sql, $params);
        $product = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        
        $total += $product['price'] * $quantity;
    }
    if(!isset($_SESSION['user_id'])){
        die("You must logged in to place an order");
    }

    $customerId = $_SESSION['user_id'];
    // Insert order (in a real app, you'd also handle customer session)
    $sql = "INSERT INTO Orders (customer_id, total_amount, status) 
            VALUES (?, ?, 'Pending'); SELECT SCOPE_IDENTITY() AS order_id;";// Using customer_id 1 as example
    $params = [$customerId, $total];
    $stmt = sqlsrv_query($conn, $sql, $params);
    if($stmt !== false && sqlsrv_next_result($stmt) && sqlsrv_fetch($stmt)){
        $orderId = sqlsrv_get_field($stmt, 0);
    }
    
    
    // Insert order items
    foreach ($orderItems as $item) {
        $productId = $item['product_id'];
        $quantity = $item['quantity'];
        
        $sql = "INSERT INTO Order_Items (order_id, product_id, quantity, unit_price)
                SELECT ?, ?, ?, price 
                FROM Products 
                WHERE product_id = ?";
        $params = [$orderId, $productId, $quantity, $productId];
        sqlsrv_query($conn, $sql, $params);
    }
    
    // Display receipt
    header('Location: order.php?success=1&order_id='.$orderId);
    exit;
}

// If this is a success redirect
if (isset($_GET['success'])) {
    echo "<script>
            document.getElementById('receipt').style.display = 'block';
            // You would populate the receipt here with order details
          </script>";
}
?>