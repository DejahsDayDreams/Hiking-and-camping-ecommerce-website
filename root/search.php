<?php
// Database connection details 
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "products"; // Name of the database

// Connect to the database
$mysqli = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Get the search term from the client-side AJAX request
$searchTerm = isset($_GET['searchTerm']) ? $_GET['searchTerm'] : "";

// Prepare the SQL statement with a placeholder
$sql = "SELECT * FROM product_list WHERE productTag = ?";
$stmt = $mysqli->prepare($sql);

if ($stmt) {
    // Bind the parameter
    $stmt->bind_param("s", $searchTerm);

    // Execute the query
    $stmt->execute();

    // Get the result set
    $result = $stmt->get_result();

    // Create an array to hold the search results
    $results = array();

    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }

    // Close the statement
    $stmt->close();

    // Check if results were found
    if (empty($results)) {
        // No results found, send a message
        $response = array("No results found. Please try again.");
    } else {
        // Send the results as JSON
        $response = $results;
    }

    // Send the response as JSON to the client-side JavaScript
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // Handle errors with the prepared statement
    die("Error in the prepared statement: " . $mysqli->error);
}

// Close the database connection
$mysqli->close();
?>



