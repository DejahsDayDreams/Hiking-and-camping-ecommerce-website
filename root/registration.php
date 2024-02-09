<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    // Get user input from the registration form
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "Passwords do not match. Please try again.";
    } else {
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

            // Hash the user's password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user data into the database
            $stmt = $pdo->prepare("INSERT INTO register (full_name, email, password) VALUES (:name, :email, :password)");
            $stmt->bindParam(':name', $full_name);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();

            echo "Registration successful!";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

