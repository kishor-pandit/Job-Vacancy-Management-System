<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

// Check if user is an employer
if ($_SESSION['role'] !== 'employer') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$employer_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $salary_min = $_POST['salary_min'];
    $salary_max = $_POST['salary_max'];
    $location = trim($_POST['location']);
    $job_type = $_POST['job_type'];
    $deadline = $_POST['deadline'];
    
    // Validation
    if (empty($title) || empty($description) || empty($location) || empty($job_type)) {
        $error = 'All required fields must be filled!';
    } else {
        // Insert job
        $insert_job = $conn->prepare("
            INSERT INTO jobs (title, description, employer_id, salary_min, salary_max, location, job_type, deadline, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        $insert_job->bind_param(
            "ssisssss",
            $title,
            $description,
            $employer_id,
            $salary_min,
            $salary_max,
            $location,
            $job_type,
            $deadline
        );
        
        if ($insert_job->execute()) {
            $success = 'Job posted successfully!';
        } else {
            $error = 'Failed to post job. Please try again.';
        }
        $insert_job->close();
    }
}

$page_title = 'Post New Job - Job Vacancy Management System';
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
    
    <main class="post-job">
        <div class="back-link">
            <a href="dashboard.php">&larr; Back to Dashboard</a>
        </div>
        
        <div class="form-container">
            <h1>Post New Job</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <a href="dashboard.php">Return to Dashboard</a>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="title">Job Title: *</label>
                        <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Job Description: *</label>
                        <textarea id="description" name="description" rows="8" required 
                                  placeholder="Describe the job details, responsibilities, and requirements..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="salary_min">Minimum Salary:</label>
                            <input type="number" id="salary_min" name="salary_min" step="0.01" value="<?php echo htmlspecialchars($_POST['salary_min'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="salary_max">Maximum Salary:</label>
                            <input type="number" id="salary_max" name="salary_max" step="0.01" value="<?php echo htmlspecialchars($_POST['salary_max'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location: *</label>
                        <input type="text" id="location" name="location" required value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="job_type">Job Type: *</label>
                        <select id="job_type" name="job_type" required>
                            <option value="">Select Job Type</option>
                            <option value="Full-time" <?php echo (isset($_POST['job_type']) && $_POST['job_type'] == 'Full-time') ? 'selected' : ''; ?>>Full-time</option>
                            <option value="Part-time" <?php echo (isset($_POST['job_type']) && $_POST['job_type'] == 'Part-time') ? 'selected' : ''; ?>>Part-time</option>
                            <option value="Contract" <?php echo (isset($_POST['job_type']) && $_POST['job_type'] == 'Contract') ? 'selected' : ''; ?>>Contract</option>
                            <option value="Freelance" <?php echo (isset($_POST['job_type']) && $_POST['job_type'] == 'Freelance') ? 'selected' : ''; ?>>Freelance</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="deadline">Application Deadline:</label>
                        <input type="date" id="deadline" name="deadline" value="<?php echo htmlspecialchars($_POST['deadline'] ?? ''); ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Post Job</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
    
    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
