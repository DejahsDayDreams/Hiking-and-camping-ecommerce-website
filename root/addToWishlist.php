<?php
// database connection details 
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "products";

// Create a connection to the database
$conn = new mysqli($servername, $db_username, $db_password, $dbname);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve product ID and action from the form
    $productId = $_POST["productId"];
    $action = $_POST["action"];

    if ($action === "add_to_wishlist") {
        // Check if the product already exists in the wishlist
        $sql_check_product_in_wishlist = "SELECT * FROM wishlist WHERE productId = ?";
        $stmt_check_product_in_wishlist = $conn->prepare($sql_check_product_in_wishlist);

        if (!$stmt_check_product_in_wishlist) {
            die("Error preparing check statement: " . $conn->error);
        }

        $stmt_check_product_in_wishlist->bind_param("i", $productId);
        $stmt_check_product_in_wishlist->execute();
        $result_check_product_in_wishlist = $stmt_check_product_in_wishlist->get_result();

        if ($result_check_product_in_wishlist->num_rows > 0) {
            // Product already exists in the wishlist, update the quantity
            $sql_update_quantity = "UPDATE wishlist SET productQuantity = productQuantity + 1 WHERE productId = ?";
            $stmt_update_quantity = $conn->prepare($sql_update_quantity);

            if (!$stmt_update_quantity) {
                die("Error preparing update statement: " . $conn->error);
            }

            $stmt_update_quantity->bind_param("i", $productId);
            $stmt_update_quantity->execute();

            $stmt_update_quantity->close();
        } else {
            // Product doesn't exist in the wishlist, add it
            $sql_select_product = "SELECT * FROM product_list WHERE productId = ?";
            $stmt_select_product = $conn->prepare($sql_select_product);

            if (!$stmt_select_product) {
                die("Error preparing select statement: " . $conn->error);
            }

            $stmt_select_product->bind_param("i", $productId);
            $stmt_select_product->execute();
            $result_select_product = $stmt_select_product->get_result();

            if ($result_select_product->num_rows > 0) {
                // Fetch the product details from the result
                $row_product = $result_select_product->fetch_assoc();

                // Now you have the product details, and you can add it to the wishlist
                $productName = $row_product["productName"];
                $productPrice = $row_product["productPrice"];
                $productQuantity = 1; // You can set an initial quantity here
                $productImg = $row_product["productImg"];

                // Insert the selected product into the wishlist table
                $sql_insert_into_wishlist = "INSERT INTO wishlist (productId, productName, productPrice, productQuantity, productImg) VALUES (?, ?, ?, ?, ?)";
                $stmt_insert_into_wishlist = $conn->prepare($sql_insert_into_wishlist);

                if (!$stmt_insert_into_wishlist) {
                    die("Error preparing insert statement: " . $conn->error);
                }

                $stmt_insert_into_wishlist->bind_param("issis", $productId, $productName, $productPrice, $productQuantity, $productImg);
                $stmt_insert_into_wishlist->execute();

                $stmt_insert_into_wishlist->close();
            } else {
                echo "Product not found in the database.";
            }

            $stmt_select_product->close();
        }

        $stmt_check_product_in_wishlist->close();
    }
}

// Display the contents of the wishlist table
$sql_select_wishlist = "SELECT * FROM wishlist"; 
$result_select_wishlist = $conn->query($sql_select_wishlist); 

if ($result_select_wishlist->num_rows > 0) {
    echo "<table style='width:100%; max-width:900px; >";
    echo "<tr style='padding: 20px; margin-bottom:20px;'><th>Name</th><th>Price</th><th>Quantity</th></tr>";

    while ($row_wishlist = $result_select_wishlist->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding: 20px; '>" . $row_wishlist["productName"] . "</td>"; 
        echo "<td style='padding: 20px;'>R" . $row_wishlist["productPrice"] . "</td>"; 
        echo "<td style='padding: 20px; '>" . $row_wishlist["productQuantity"] . "</td>"; 
        echo "<td style='padding: 20px;'><img src='Media/Images/Products/" . $row_wishlist["productImg"] . "' alt='Product Image' height='50' width='50'></td>"; // Changed "cart" to "wishlist"
        echo "<td ><button style='background-color:#85c54d;
        border-radius:15px;
         border-style:none; 
         padding:8px;

         color:white;' class='remove-from-wishlist' data-product-id='" . $row_wishlist["productId"] . "'>Remove</button></td>"; 
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "Your wishlist is empty."; 
}

$conn->close();
?>


<script>
$(document).ready(function() {
    $(".add-to-wishlist").on("click", function(event) { 
        event.preventDefault(); // Prevent the form from submitting

        var productId = $(this).data("product-id"); 

        // Send an AJAX request to addToWishlist.php (update the URL)
        $.ajax({
            type: "POST",
            url: "addToWishlist.php", // Update the URL to addToWishlist.php
            data: { productId: productId, action: "add_to_wishlist" }, 
            dataType: "json",
            success: function(data) {
                if (data.success) {
                    // Show the success message in an alert
                    alert(data.message);
                } else {
                    // Handle errors if needed
                    console.log("Error adding product to wishlist."); 
                }
            },
            error: function() {
                // Handle AJAX error if needed
                console.log("AJAX request failed");
            }
        });
    });
});

// Function to remove an item from the wishlist
function removeFromWishlist(productId) {
    console.log("Removing product with ID: " + productId);

    // Send an AJAX request to remove the item from the wishlist
    $.ajax({
        type: "POST",
        url: "removeFromWishlist.php",
        data: { productId: productId },
        success: function(response) {
            console.log("Response from server: " + response);

            // Update the table on the web page
            if (response === "success") {
                console.log("Successfully removed product from wishlist.");
                // Remove the row from the table
                $("button[data-product-id='" + productId + "']").closest("tr").remove();
            } else {
                console.log("Failed to remove product from wishlist.");
                alert("Failed to remove the item from the wishlist.");
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log("AJAX request failed with error: " + textStatus);
            console.log("Error thrown: " + errorThrown);
            alert("AJAX request failed.");
        }
    });
}
</script>


