<?php
// Include the database connection
include_once 'connection/db_connect.php';

// Get the structure of the users table
$query = "DESCRIBE users";
$result = mysqli_query($conn, $query);

echo "<h2>Users Table Structure</h2>";
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>Error: " . mysqli_error($conn) . "</td></tr>";
}

echo "</table>";
?> 