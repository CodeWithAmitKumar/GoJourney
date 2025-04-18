<?php
// Start the session
session_start();

// Database connection settings
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$dbname = "go_journey"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    $_SESSION['error'] = "Database connection failed: " . $conn->connect_error;
    header("Location: index.php");
    exit();
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);
    
    // Get submission date (from form or create now if not provided)
    if (isset($_POST['submission_date']) && !empty($_POST['submission_date'])) {
        $submission_date = $conn->real_escape_string($_POST['submission_date']);
    } else {
        $submission_date = date('Y-m-d H:i:s');
    }
    
    // Prepare SQL statement to insert feedback
    $sql = "INSERT INTO feedback (name, email, subject, message, submission_date) 
            VALUES ('$name', '$email', '$subject', '$message', '$submission_date')";
    
    // Execute the query
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Thank you for your feedback! We will get back to you soon.";
    } else {
        $_SESSION['error'] = "Error: " . $sql . "<br>" . $conn->error;
    }
    
    // Close connection
    $conn->close();
    
    // Redirect back to homepage
    header("Location: index.php");
    exit();
} else {
    // If not a POST request, redirect to homepage
    header("Location: index.php");
    exit();
}
?> 