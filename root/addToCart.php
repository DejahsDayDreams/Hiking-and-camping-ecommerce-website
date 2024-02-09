<?php
// Database connection details 
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
    // Retrieve product ID and action from the form
    $productId = $_POST["productId"];
    $action = $_POST["action"];

    if ($action === "add_to_cart") {
        // Check if the product is already in the cart
        $sql_check_cart = "SELECT * FROM cart WHERE productId = ?";
        $stmt_check_cart = $conn->prepare($sql_check_cart);

        if (!$stmt_check_cart) {
            die("Error preparing check statement: " . $conn->error);
        }

        $stmt_check_cart->bind_param("i", $productId);
        $stmt_check_cart->execute();
        $result_check_cart = $stmt_check_cart->get_result();

        if ($result_check_cart->num_rows > 0) {
            // Product is already in the cart, update its quantity
            $sql_update_cart = "UPDATE cart SET productQuantity = productQuantity + 1 WHERE productId = ?";
            $stmt_update_cart = $conn->prepare($sql_update_cart);

            if (!$stmt_update_cart) {
                die("Error preparing update statement: " . $conn->error);
            }

            $stmt_update_cart->bind_param("i", $productId);
            $stmt_update_cart->execute();

            echo "Product quantity updated in the cart.";
        } else {
            // Product is not in the cart, insert it
            // Retrieve product information from the specific table
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

                // Now you have the product details, and you can add it to the cart
                $productName = $row_product["productName"];
                $productPrice = $row_product["productPrice"];
                $productQuantity = 1; // You can set an initial quantity here
                $productImg = $row_product["productImg"];

                // Insert the selected product into the cart table
                $sql_insert_into_cart = "INSERT INTO cart (productId, productName, productPrice, productQuantity, productImg) VALUES (?, ?, ?, ?, ?)";
                $stmt_insert_into_cart = $conn->prepare($sql_insert_into_cart);

                if (!$stmt_insert_into_cart) {
                    die("Error preparing insert statement: " . $conn->error);
                }

                $stmt_insert_into_cart->bind_param("issis", $productId, $productName, $productPrice, $productQuantity, $productImg);
                $stmt_insert_into_cart->execute();

                echo "Product added to cart.";
            } else {
                echo "Product not found in the database.";
            }

            $stmt_select_product->close();
            $stmt_insert_into_cart->close();
        }

        $stmt_check_cart->close();
        if (isset($stmt_update_cart)) {
            $stmt_update_cart->close();
        }
    }
}

// Display the contents of the cart table
$sql_select_cart = "SELECT * FROM cart";
$result_select_cart = $conn->query($sql_select_cart);

$totalPrice = 0; // Initialize the total price

if ($result_select_cart->num_rows > 0) {
    echo "<table style='width:100%; max-width:900px;'>";
    echo "<tr style='padding: 20px; margin-bottom:20px;'><th>Name</th><th>Price</th><th>Quantity</th ></tr>";

    while ($row_cart = $result_select_cart->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding: 20px;'>" . $row_cart["productName"] . "</td>";
        echo "<td style='padding: 20px;'>R" . $row_cart["productPrice"] . "</td>";
        echo "<td style='padding: 20px;'>" . $row_cart["productQuantity"] . "</td>";
        echo "<td style='padding: 20px;'><img src='Media/Images/Products/" . $row_cart["productImg"] . "' alt='Product Image' height='50' width='50'></td>";
        echo "<td style='padding: 20px;'><button style='background-color:#85c54d;
        border-radius:15px;
         border-style:none; 
         padding:8px;
         color:white;' class='remove-from-cart' data-product-id='" . $row_cart["productId"] . "'>Remove</button></td>";
        echo "</tr>";

        // Calculate subtotal for each item and add it to the total
        $subtotal = $row_cart["productPrice"] * $row_cart["productQuantity"];
        $totalPrice += $subtotal;
    }


    // Add space between the products and the total/button section
    echo "<tr style='padding: 20px;'></tr>";

    // Display a horizontal line
    echo "<tr><td colspan='10' style='border-top: 2px solid #c1c1c1;'></td></tr>";

    // Display the total and checkout button on the same line with swapped positions
    echo "<tr style='padding: 10px;'>";
    echo "<td colspan='4' style='text-align: left; padding-left: 20px;'>
    <button style='background-color:#85c54d;
     border-radius:15px;
      border-style:none; 
      padding:15px;
      color:white;' margin-top:30px; id='checkoutButton'>Checkout</button></td>";
    echo "<td colspan='6' style='text-align: right; padding-right: 20px;'>
    Total: R" . number_format($totalPrice, 2) . "</td>"; // Format the total as a currency
    echo "</tr>";
    echo "</table>";
}
else {
    echo "Your cart is empty."; // Display a message if the cart is empty
}

$conn->close();

?>

<script>

$(document).ready(function() {
    $(".add-to-cart").on("click", function(event) {
        event.preventDefault(); // Prevent the form from submitting

        var productId = $(this).data("product-Id");

        // Send an AJAX request to addToCart.php
        $.ajax({
            type: "POST",
            url: "addToCart.php",
            data: { productId: productId, action: "add_to_cart" },
            dataType: "json",
            success: function(data) {
                if (data.success) {
                    // Show the success message in an alert
                    alert(data.message);
                } else {
                    // Handle errors if needed
                    console.log("Error adding product to cart");
                }
            },
            error: function() {
                // Handle AJAX error if needed
                console.log("AJAX request failed");
            }
        });
    });
});

 $(document).ready(function() {
    
    // Function to remove an item from the cart
function removeFromCart(productId) {
    console.log("Removing product with ID: " + productId);
    
    // Send an AJAX request to remove the item from the cart
    $.ajax({
        type: "POST",
        url: "removeFromCart.php",
        data: { productId: productId },
        success: function(response) {
            console.log("Response from server: " + response);
            
            // Update the table on the web page
            if (response === "success") {
                console.log("Successfully removed product.");
                // Remove the row from the table
                $("button[data-product-id='" + productId + "']").closest("tr").remove();
            } else {
                console.log("Failed to remove product.");
                alert("Failed to remove the item from the cart.");
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log("AJAX request failed with error: " + textStatus);
            console.log("Error thrown: " + errorThrown);
            alert("AJAX request failed.");
        }
    });
}
});
</script>



   

   