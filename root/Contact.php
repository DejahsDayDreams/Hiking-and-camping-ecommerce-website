<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST["name"];
        $email = $_POST["email"];
        $idNumber = $_POST["id_number"];
        $phone = $_POST["phone"];
        $address = $_POST["address"];
        $gender = isset($_POST["gender"]) ? $_POST["gender"] : "";
        $nationality = $_POST["nationality"];
        $comment = $_POST["comment"];

        $validationMessages = array();

        // Validate Name
        if (!preg_match("/^[a-zA-Z]*$/", $name)) {
            $validationMessages[] = "Invalid Name";
        }

        // Validate Email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $validationMessages[] = "Invalid email format";
        }

        // Validate ID Number
        if (!preg_match("/^\d{4}$/", $idNumber)) {
            $validationMessages[] = "Invalid ID Number";
        }

        // Validate Phone Number
        if (!preg_match("/^\d{10}$/", $phone)) {
            $validationMessages[] = "Invalid Phone Number";
        }

        // Validate Physical Address
        if (empty($address)) {
            $validationMessages[] = "Physical Address is required";
        }

        // Validate Gender (optional)
        if (empty($gender)) {
            $validationMessages[] = "Gender is required";
        }

        // Validate Nationality
        if (empty($nationality)) {
            $validationMessages[] = "Nationality is required";
        }

        // Validate Comment
        if (empty($comment)) {
            $validationMessages[] = "Comment is required";
        }

        // Display validation messages or submitted information
        echo "<h2>Submitted Information:</h2>";
        echo "<p><strong>Name:</strong> $name</p>";
        echo "<p><strong>Email:</strong> $email</p>";
        echo "<p><strong>ID Number:</strong> $idNumber</p>";
        echo "<p><strong>Phone Number:</strong> $phone</p>";
        echo "<p><strong>Physical Address:</strong> $address</p>";
        echo "<p><strong>Gender:</strong> $gender</p>";
        echo "<p><strong>Nationality:</strong> $nationality</p>";
        echo "<p><strong>Comment:</strong> $comment</p>";

        if (count($validationMessages) > 0) {
            echo "<h2>Validation Errors:</h2>";
            foreach ($validationMessages as $message) {
                echo "<p>$message</p>";
            }
        }
    }
    ?>