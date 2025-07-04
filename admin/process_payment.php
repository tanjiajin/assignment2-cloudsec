<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'];
    $amount = $_POST['amount'];
    $method = $_POST['payment_method'];
    $card = $_POST['masked_card_number'];
    $status = $_POST['payment_status'] ?? 'Completed';

    $sql = "INSERT INTO Payments (order_id, amount, payment_method, payment_status, masked_card_number, payment_date)
            VALUES (?, ?, ?, ?, ?, GETDATE())";
    $params = [$orderId, $amount, $method, $status, $card];

    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        die("❌ Payment failed: " . print_r(sqlsrv_errors(), true));
    }

    // Redirect back to admin page
    header("Location: admindashboard.php"); // Change this to your actual admin page filename
    exit;
}
?>
