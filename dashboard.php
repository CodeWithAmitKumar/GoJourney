<?php
// This file redirects to the new dashboard location
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

// Redirect to the new dashboard location
header("Location: dashboard/index.php");
exit();
?> 