<?php
session_start();

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // Simple validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: index.php#contact");
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        header("Location: index.php#contact");
        exit();
    }
    
    // Database connection (uncomment and configure when you have a database)
    /*
    $servername = "localhost";
    $username = "your_username";
    $password = "your_password";
    $dbname = "gojourney";
    
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO feedback (name, email, subject, message, created_at) 
                                VALUES (:name, :email, :subject, :message, NOW())");
        
        // Bind parameters
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':message', $message);
        
        // Execute query
        $stmt->execute();
        
        $_SESSION['success'] = "Thank you for your feedback! We'll get back to you soon.";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    
    $conn = null;
    */
    
    // For now, just show a success message
    $_SESSION['success'] = "Thank you for your feedback, $name! We'll get back to you soon.";
    
    // Redirect back to the contact section
    header("Location: index.php#contact");
    exit();
} else {
    // If someone tries to access this file directly
    header("Location: index.php");
    exit();
}
?> 