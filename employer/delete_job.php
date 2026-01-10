<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

// Check if user is an employer
if ($_SESSION['role'] !== 'employer') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$employer_id = $_SESSION['user_id'];
$job_id = intval($_GET['id'] ?? 0);

// Verify job belongs to employer
$verify = $conn->prepare("SELECT id FROM jobs WHERE id = ? AND employer_id = ?");
$verify->bind_param("ii", $job_id, $employer_id);
$verify->execute();
$verify->store_result();

if ($verify->num_rows === 0) {
    header("Location: dashboard.php");
    exit();
}

// Delete job
$delete = $conn->prepare("DELETE FROM jobs WHERE id = ? AND employer_id = ?");
$delete->bind_param("ii", $job_id, $employer_id);

if ($delete->execute()) {
    header("Location: dashboard.php?message=Job deleted successfully");
} else {
    header("Location: dashboard.php?error=Failed to delete job");
}
exit();
