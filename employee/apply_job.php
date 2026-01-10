<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

// Check if user is an employee
if ($_SESSION['role'] !== 'employee') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$job_id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch job details
$job_query = $conn->prepare("SELECT * FROM jobs WHERE id = ? AND status = 'active'");
$job_query->bind_param("i", $job_id);
$job_query->execute();
$job_result = $job_query->get_result();

if ($job_result->num_rows === 0) {
    header("Location: dashboard.php");
    exit();
}

$job = $job_result->fetch_assoc();

// Check if user has already applied
$check_apply = $conn->prepare("SELECT id FROM applications WHERE job_id = ? AND employee_id = ?");
$check_apply->bind_param("ii", $job_id, $user_id);
$check_apply->execute();
$check_apply->store_result();

if ($check_apply->num_rows > 0) {
    $error = 'You have already applied for this job!';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$error) {
    $cover_letter = trim($_POST['cover_letter']);
    
    if (empty($cover_letter)) {
        $error = 'Cover letter is required!';
    } else {
        // Insert application
        $insert_app = $conn->prepare("INSERT INTO applications (job_id, employee_id, status, cover_letter) VALUES (?, ?, 'pending', ?)");
        $insert_app->bind_param("iis", $job_id, $user_id, $cover_letter);
        
        if ($insert_app->execute()) {
            $success = 'Application submitted successfully!';
        } else {
            $error = 'Failed to submit application. Please try again.';
        }
        $insert_app->close();
    }
}

$page_title = 'Apply for Job - Job Vacancy Management System';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php require_once '../includes/header.php'; ?>
    
    <main class="apply-job">
        <div class="back-link">
            <a href="dashboard.php">&larr; Back to Dashboard</a>
        </div>
        
        <div class="form-container">
            <h1>Apply for: <?php echo htmlspecialchars($job['title']); ?></h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <a href="dashboard.php">Return to Dashboard</a>
                </div>
            <?php elseif (!$error): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="cover_letter">Cover Letter:</label>
                        <textarea id="cover_letter" name="cover_letter" rows="8" required 
                                  placeholder="Write your cover letter here..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Submit Application</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
    
    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
