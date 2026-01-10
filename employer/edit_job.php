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
$job_query = $conn->prepare("SELECT * FROM jobs WHERE id = ? AND employer_id = ?");
$job_query->bind_param("ii", $job_id, $employer_id);
$job_query->execute();
$job_result = $job_query->get_result();

if ($job_result->num_rows === 0) {
    header("Location: dashboard.php");
    exit();
}

$job = $job_result->fetch_assoc();
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
    $status = $_POST['status'];
    
    if (empty($title) || empty($description) || empty($location)) {
        $error = 'All required fields must be filled!';
    } else {
        $update_job = $conn->prepare("
            UPDATE jobs 
            SET title = ?, description = ?, salary_min = ?, salary_max = ?, 
                location = ?, job_type = ?, deadline = ?, status = ? 
            WHERE id = ? AND employer_id = ?
        ");
        $update_job->bind_param(
            "ssisssssii",
            $title,
            $description,
            $salary_min,
            $salary_max,
            $location,
            $job_type,
            $deadline,
            $status,
            $job_id,
            $employer_id
        );
        
        if ($update_job->execute()) {
            $success = 'Job updated successfully!';
            // Refresh job data
            $job_query->execute();
            $job_result = $job_query->get_result();
            $job = $job_result->fetch_assoc();
        } else {
            $error = 'Failed to update job. Please try again.';
        }
        $update_job->close();
    }
}

$page_title = 'Edit Job - Job Vacancy Management System';
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
    
    <main class="edit-job">
        <div class="back-link">
            <a href="dashboard.php">&larr; Back to Dashboard</a>
        </div>
        
        <div class="form-container">
            <h1>Edit Job</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="title">Job Title: *</label>
                    <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($job['title']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="description">Job Description: *</label>
                    <textarea id="description" name="description" rows="8" required><?php echo htmlspecialchars($job['description']); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="salary_min">Minimum Salary:</label>
                        <input type="number" id="salary_min" name="salary_min" step="0.01" value="<?php echo htmlspecialchars($job['salary_min'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="salary_max">Maximum Salary:</label>
                        <input type="number" id="salary_max" name="salary_max" step="0.01" value="<?php echo htmlspecialchars($job['salary_max'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="location">Location: *</label>
                    <input type="text" id="location" name="location" required value="<?php echo htmlspecialchars($job['location']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="job_type">Job Type: *</label>
                    <select id="job_type" name="job_type" required>
                        <option value="Full-time" <?php echo $job['job_type'] == 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                        <option value="Part-time" <?php echo $job['job_type'] == 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                        <option value="Contract" <?php echo $job['job_type'] == 'Contract' ? 'selected' : ''; ?>>Contract</option>
                        <option value="Freelance" <?php echo $job['job_type'] == 'Freelance' ? 'selected' : ''; ?>>Freelance</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="deadline">Application Deadline:</label>
                    <input type="date" id="deadline" name="deadline" value="<?php echo htmlspecialchars($job['deadline']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" required>
                        <option value="active" <?php echo $job['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="closed" <?php echo $job['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                        <option value="draft" <?php echo $job['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Job</button>
            </form>
        </div>
    </main>
    
    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
