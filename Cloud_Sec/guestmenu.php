<?php
require_once('config.php');

// Query to get all products
$sql = "SELECT product_id, name, description, image_url 
        FROM Products 
        ORDER BY name";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch all products
$products = array();
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $products[] = $row;
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Crumby Cafe</title>
        <link rel="stylesheet" href="main.css" />
        <link rel="stylesheet" href="menu.css" />
        <link rel="stylesheet" href="guestorder.css" />
    </head>
    <body>
        <nav>
            <h1>Welcome to Crumby Cafe!</h1>
            <ul>
                <li><a href="guestmain.php">Home</a></li>
                <li><a href="guestmenu.php">Menu</a></li>
                <li><a href="guestorder.php">Order</a></li>
                <li><a href="signup.php" class="signup">Sign up</a></li>
            </ul>
        </nav>
        
        <div class="product-categories">
            <?php foreach ($products as $product): ?>
                <div class="category">
                    <img src="<?php echo htmlspecialchars($product['image_url'] ?: 'img/default.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <?php if (!empty($product['description'])): ?>
                        <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </body>
</html>