<?php
session_start();

// Log the logout event
error_log("User logged out: " . (isset($_SESSION['email']) ? $_SESSION['email'] : 'Unknown user'));

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to home page with success message
session_start();
$_SESSION['success'] = "You have been successfully logged out!";
header("Location: ../index.php");
exit();
?>
