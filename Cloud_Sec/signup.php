<?php
require 'config.php'; // Your database configuration file

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['hp'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['psw'] ?? '';
    $confirmPassword = $_POST['psw-repeat'] ?? '';

    // Validation
    if (empty($firstName) || empty($lastName)) {
        $errors[] = "Full name is required";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }

    // Check if email exists
    if (empty($errors)) {
        $checkEmail = "SELECT email FROM Customers WHERE email = ?";
        $params = array($email);
        $stmt = sqlsrv_query($conn, $checkEmail, $params);
        
        if (sqlsrv_fetch($stmt)) {
            $errors[] = "Email already registered";
        }
    }

    // Insert into database if no errors
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO Customers (
            first_name, 
            last_name, 
            email, 
            phone, 
            address, 
            password_hash
        ) VALUES (?, ?, ?, ?, ?, ?)";
        
        $params = array(
            $firstName,
            $lastName,
            $email,
            $phone,
            $address,
            $hashedPassword
        );
        
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if ($stmt) {
            $success = "Registration successful! Redirecting to login...";
            header("Refresh: 3; url=login.php");
        } else {
            $errors[] = "Registration failed: " . print_r(sqlsrv_errors(), true);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Crumby Cafe - Sign Up</title>
    <link rel="stylesheet" href="signup.css" />
    <style>
        .error { color: red; margin-bottom: 15px; }
        .success { color: green; margin-bottom: 15px; }
    </style>
</head>
<body>
    <form method="post" action="signup.php">
        <div class="container">
            <div class="top">
                <h1>Sign Up</h1>
                <p>Please fill in this form to create an account.</p>
                
                <?php if (!empty($errors)): ?>
                    <div class="error">
                        <?php foreach ($errors as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
            </div>

            <label for="first_name"><b>First Name</b></label>
            <input type="text" placeholder="Enter First Name" name="first_name" required>

            <label for="last_name"><b>Last Name</b></label>
            <input type="text" placeholder="Enter Last Name" name="last_name" required>

            <label for="email"><b>Email</b></label>
            <input type="email" placeholder="Enter Email" name="email" required>

            <label for="hp"><b>Phone Number</b></label>
            <input type="tel" id="hp" name="hp" pattern="[0-9]{10,11}" placeholder="Phone Number" required>

            <label for="address"><b>Home Address</b></label>
            <input type="text" id="address" name="address" placeholder="Address" required>

            <label for="psw"><b>Password</b></label>
            <input type="password" placeholder="Enter Password" name="psw" required minlength="8">

            <label for="psw-repeat"><b>Repeat Password</b></label>
            <input type="password" placeholder="Repeat Password" name="psw-repeat" required>

            <button type="submit" class="signupbtn">Sign Up</button>
        </form>

        <div class="link">
            <a>Already have an Account? </a><a href="login.php">Log in</a>
        </div>
    </div>
</body>
</html>