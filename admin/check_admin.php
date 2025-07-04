<?php
session_start();
require 'config.php'; // optional, for database login check

$adminId = $_POST['admin_id'] ?? '';
$passcode = $_POST['passcode'] ?? '';

// Replace with real DB logic if needed
$validAdminId = "admin123";
$validPasscode = "securepass";

if ($adminId === $validAdminId && $passcode === $validPasscode) {
    $_SESSION['is_admin'] = true;
    header('Location: adminstaffpage.php');
    exit();
} else {
    // Redirect back with error
    $error = urlencode("Invalid credentials");
    header("Location: adminauthorize.php?error=$error");
    exit();
}
