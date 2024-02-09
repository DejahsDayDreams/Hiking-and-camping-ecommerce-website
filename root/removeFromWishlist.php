<?php

// database connection details 
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "products";

// Create a connection to the database
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productId = $_POST["productId"];
    
    // Prepare and execute a DELETE query to remove the item from the wishlist
    $sql_delete = "DELETE FROM wishlist WHERE productId = ?"; 
    $stmt_delete = $conn->prepare($sql_delete);

    if (!$stmt_delete) {
        die("Error preparing delete statement: " . $conn->error);
    }

    $stmt_delete->bind_param("i", $productId);
    $stmt_delete->execute();

    // Check for SQL errors
    if ($stmt_delete->errno) {
        die("SQL Error: " . $stmt_delete->error);
    }

    // Check if the deletion was successful
    if ($stmt_delete->affected_rows > 0) {
        echo "success"; // Send a success response to JavaScript
    } else {
        echo "error"; // Send an error response to JavaScript
    }
}

// Close the database connection
$conn->close();
?>
