<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input from the login form
    $identifier = $_POST['identifier']; // This can be either email or username
    $password = $_POST['password'];

    // Database configuration
    $servername = "localhost";
    $db_username = "root";
    $db_password = "";
    $dbname = "register";

    try {
        // Create a PDO instance
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $db_username, $db_password);

        // Set PDO to throw exceptions on errors
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Retrieve the user's hashed password and identifier (email or username) from the database
        $stmt = $pdo->prepare("SELECT * FROM register WHERE email = :identifier OR username = :identifier");
        $stmt->bindParam(':identifier', $identifier);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Password is correct, log the user in
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            header("Location: dashboard.php"); // Redirect to a dashboard page or any other authenticated page
        } else {
            echo "Incorrect email/username or password. Please try again.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
