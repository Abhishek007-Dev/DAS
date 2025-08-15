<?php
// Start session only if it's not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkRole($allowedRoles) {
    // Convert allowedRoles to an array if it's not already
    $allowedRoles = (array) $allowedRoles;

    // Verify that session role exists and matches one of the allowed roles
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
        header("Location: ../login.php");
        exit();
    }
}