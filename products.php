<!DOCTYPE html>
<html>
<head>
    <title>Our Products</title>
</head>
<body>
    <nav>
        <a href="index.html">Home</a> | 
        <a href="register.php">Register</a> | 
        <a href="login.php">Login</a>
    </nav>

    <h1>Our Products</h1>

    <?php
    require_once('db_connect.php');

    $query = "SELECT * FROM products"; // Adjust this query based on your database schema
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div>";
            echo "<h2>" . htmlspecialchars($row['name']) . "</h2>";
            if (!empty($row['image'])) {
                echo "<img src='uploads/" . htmlspecialchars($row['image']) . "' alt='" . htmlspecialchars($row['name']) . "'>";
            }
            echo "<p>" . htmlspecialchars($row['description']) . "</p>";
            echo "<p>Price: $" . htmlspecialchars($row['price']) . "</p>";
            // More product details
            echo "</div>";
        }
    } else {
        echo "<p>No products found.</p>";
    }

    $conn->close();
    ?>
</body>
</html>
