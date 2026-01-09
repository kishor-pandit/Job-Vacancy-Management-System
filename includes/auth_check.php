<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Get user role from session
$user_role = $_SESSION['role'] ?? null;

?>
