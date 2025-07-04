<?php
require_once('config.php');

// Check for successful order submission
$orderSuccess = isset($_GET['success']);
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Fetch products from database
$sql = "SELECT product_id, name, price FROM Products ORDER BY name";
$stmt = sqlsrv_query($conn, $sql);
$products = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $row['price'] = (float)$row['price'];
    $products[] = $row;
}

// If this is a success page, fetch order details
$orderDetails = [];
if ($orderSuccess && $orderId > 0) {
    $sql = "SELECT p.name, oi.quantity, oi.unit_price 
            FROM Order_Items oi
            JOIN Products p ON oi.product_id = p.product_id
            WHERE oi.order_id = ?";
    $params = [$orderId];
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $orderDetails[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Crumby Cafe</title>
    <link rel="stylesheet" href="main.css" />
    <link rel="stylesheet" href="order.css" />
    <script>
        // Pass PHP products data to JavaScript
        const products = <?php echo json_encode($products); ?>;
        const orderDetails = <?php echo json_encode($orderDetails); ?>;
        const orderSuccess = <?php echo $orderSuccess ? 'true' : 'false'; ?>;
        const orderId = <?php echo $orderId; ?>;
        
        function addOrderItem() {
            const container = document.getElementById('order-items');
            const itemId = Date.now();
            
            const itemDiv = document.createElement('div');
            itemDiv.className = 'order-item';
            itemDiv.id = `item-${itemId}`;
            
            itemDiv.innerHTML = `
                <div class="form-group">
                    <label>Item</label>
                    <select name="products[${itemId}][product_id]" required>
                        <option value="">Select an item</option>
                        ${products.map(p => 
                            `<option value="${p.product_id}" data-price="${p.price}">
                                ${p.name} (RM${parseFloat(p.price).toFixed(2)})
                            </option>`
                        ).join('')}
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" name="products[${itemId}][quantity]" 
                           value="1" min="1" max="10" required>
                </div>
                <button type="button" class="remove-btn" 
                        onclick="document.getElementById('item-${itemId}').remove()">
                    Remove
                </button>
                <hr>
            `;
            
            container.appendChild(itemDiv);
        }

        function validateOrder() {
            const items = document.querySelectorAll('.order-item');
            if (items.length === 0) {
                alert('Please add at least one item to your order');
                return false;
            }
            return true;
        }

        function displayReceipt() {
            if (!orderSuccess) return;
            
            const receiptContainer = document.getElementById('receipt');
            const receiptList = document.getElementById('receipt-list');
            
            // Clear previous items
            receiptList.innerHTML = '';
            
            // Add order items to receipt
            let total = 0;
            orderDetails.forEach(item => {
                const itemTotal = item.quantity * item.unit_price;
                total += itemTotal;
                
                const li = document.createElement('li');
                li.innerHTML = `
                    ${item.quantity} x ${item.name} - 
                    RM${parseFloat(item.unit_price).toFixed(2)} = 
                    RM${parseFloat(itemTotal).toFixed(2)}
                `;
                receiptList.appendChild(li);
            });
            
            // Add total
            const totalLi = document.createElement('li');
            totalLi.innerHTML = `<strong>Total: RM${total.toFixed(2)}</strong>`;
            totalLi.style.marginTop = '10px';
            totalLi.style.borderTop = '1px solid #ddd';
            totalLi.style.paddingTop = '10px';
            receiptList.appendChild(totalLi);
            
            // Show receipt
            receiptContainer.style.display = 'block';
            
            // Scroll to receipt
            receiptContainer.scrollIntoView({ behavior: 'smooth' });
        }

        window.onload = function() {
            addOrderItem();
            displayReceipt();
        };
    </script>
</head>
<body>
    <nav>
        <h1>Welcome to Crumby Cafe!</h1>
        <ul>
            <li><a href="main.html">Home</a></li>
            <li><a href="menu.php">Menu</a></li>
            <li><a href="order.php">Order</a></li>
            <li><a href="login.php" class="logout">Logout</a></li>
        </ul>
    </nav>
    
    <div class="order-container">
        <h2>Order Your Favorite Items</h2>
        <h3>Read this before making an order. Thank you.</h3>
        <p>- Please select the items you want to order and specify the quantity.</p>
        <p>- Click "Add Item" to add more items to your order.</p>
        <p>- Click "Place Order" to submit your order.</p>
        <p>- If you want to cancel an item, click "Remove" then click "Place Order" to update your order.</p>
        <p>- To add more items after placing order, just add quantity or "Add Item" and click "Place Order" again.</p>
        <br>
        
        <form onsubmit="return validateOrder()" action="process_order.php" method="post">
            <div id="order-items"></div>
            <button type="button" onclick="addOrderItem()">Add Item</button>
            <input type="submit" value="Place Order">
            <button type="button" onclick="window.location.href='order_history.php'">History</button>
        </form>
    </div>

    <div id="receipt" class="receipt-container" style="display: none;">
        <h3>Receipt [Please Pay at Counter]</h3>
        <p>Order ID: #<?php echo $orderId; ?></p>
        <ul id="receipt-list"></ul>
    </div>
</body>
</html>