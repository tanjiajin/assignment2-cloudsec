<?php
session_start();
require 'config.php';

$error = "";

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $email = $_POST['email'] ?? '';

    if (!empty($first_name) && !empty($email)) {
        $conn = getRoleConnection('staff');
        
        if ($conn === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        // Using the view for authentication
        $sql = "SELECT staff_id, first_name FROM vw_StaffAuth WHERE first_name = ? AND email = ? AND is_active = 1";
        $params = array($first_name, $email);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $staff = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if ($staff) {
            $_SESSION['staff_id'] = $staff['staff_id'];
            $_SESSION['first_name'] = $staff['first_name'];
            $_SESSION['user_role'] = 'staff';
            
            header("Location: admindashboard.php");
            exit();
        } else {
            $error = "Invalid credentials or account is inactive.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Crumby Cafe - Staff Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #643f3f;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-box {
            background-color: #d8c7a0;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            margin-bottom: 10px;
        }

        p {
            margin-bottom: 20px;
            color: #333;
        }

        label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: none;
            border-radius: 5px;
        }

        .loginbtn {
            width: 100%;
            background-color: #2d2d2d;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .loginbtn:hover {
            background-color: #444;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }

        .link {
            margin-top: 15px;
        }

        .link a {
            color: purple;
            text-decoration: none;
        }

        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <form method="post">
        <div class="login-box">
            <h1>Log in(Admin)</h1>
            <p>Please fill in this form to log in.</p>
            <?php if (!empty($error)): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <label for="first_name">First Name</label>
            <input type="text" placeholder="Enter First Name" name="first_name" required>

            <label for="email">Email</label>
            <input type="email" placeholder="Enter Email" name="email" required>

            <button type="submit" class="loginbtn">Log in</button>

        </div>
    </form>
</body>
</html>
