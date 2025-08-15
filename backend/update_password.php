<?php
// Include your database connection
require_once "../backend/db_connect.php"; // Ensure this path is correct

// Array of users to update
$users = [
    ['username' => 'admin1', 'password' => 'admin1234'],
    ['username' => 'maker1', 'password' => 'admin'],
    ['username' => 'checker1', 'password' => 'admin'],
    ['username' => 'superadmin1', 'password' => 'admin1234']
];

// Prepare the update statement
$sql = "UPDATE users SET password = ? WHERE username = ?";
$stmt = $conn->prepare($sql);

foreach ($users as $user) {
    // Hash the password
    $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);

    // Bind the parameters
    $stmt->bind_param("ss", $hashed_password, $user['username']);
    
    // Execute the statement
    if ($stmt->execute()) {
        echo "Password for user {$user['username']} updated successfully.<br>";
    } else {
        echo "Error updating password for user {$user['username']}: " . $stmt->error . "<br>";
    }
}

// Close the connection
$stmt->close();
$conn->close();

