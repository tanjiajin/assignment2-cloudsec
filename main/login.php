<?php
require 'config.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['psw'] ?? ''; 

    if (!empty($email) && !empty($password)) {
        $sql = "SELECT * FROM Customers WHERE email = ?"; 
        $params = array($email);
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if ($user && password_verify($password, $user['password_hash'])) {
            // Start session and redirect
            session_start();
            $_SESSION['user_id'] = $user['customer_id'];
            $_SESSION['user_role'] = 'customer';
            header("Location: main.html");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Crumby Cafe - Login</title>
        <link rel="stylesheet" href="signup.css" />
    </head>
    <body>
        <form method="post">
            <div class="container">
                <div class="top">
                    <h1>Log in</h1>
                    <p>Please fill in this form to log in.</p>
                    <?php if (!empty($error)): ?>
                        <div class="error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                </div>

                <label for="email"><b>Email</b></label>
                <input type="text" placeholder="Enter Email" name="email" required>
            
                <label for="psw"><b>Password</b></label>
                <input type="password" placeholder="Enter Password" name="psw" required>
            
                <button type="submit" class="loginbtn">Log in</button>

                <div class="link">
                    <a>Don't have an Account? </a><a href="signup.php">Sign Up</a>
                    <br><a href="guestmain.php">Continue as guest</a></br>
                </div>
            </div>
        </form>
    </body>
</html>