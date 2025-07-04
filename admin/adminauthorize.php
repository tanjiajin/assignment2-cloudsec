<?php
session_start();

// Handle error message from failed login (optional)
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Authorization</title>
    <link rel="stylesheet" href="adminauthorize.css" />
</head>
<body>
    <div class="container">
        <h1>Admin Login</h1>
        
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="check_admin.php">
            <label for="admin_id">Admin ID</label>
            <input type="text" name="admin_id" required placeholder="Enter Admin ID">

            <label for="passcode">Passcode</label>
            <input type="password" name="passcode" required placeholder="Enter Passcode">

            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
