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

// Fetch job details
$job_query = $conn->prepare("
    SELECT j.*, u.name as employer_name, u.email as employer_email, u.phone as employer_phone
    FROM jobs j
    JOIN users u ON j.employer_id = u.id
    WHERE j.id = ? AND j.status = 'active'
");
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
$already_applied = $check_apply->num_rows > 0;

$page_title = htmlspecialchars($job['title']) . ' - Job Vacancy Management System';
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
    
    <main class="job-details">
        <div class="back-link">
            <a href="dashboard.php">&larr; Back to Dashboard</a>
        </div>
        
        <div class="job-card-full">
            <h1><?php echo htmlspecialchars($job['title']); ?></h1>
            
            <div class="job-meta">
                <p><strong>Employer:</strong> <?php echo htmlspecialchars($job['employer_name']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                <p><strong>Job Type:</strong> <?php echo htmlspecialchars($job['job_type']); ?></p>
                <p><strong>Salary:</strong> $<?php echo htmlspecialchars($job['salary_min']); ?> - $<?php echo htmlspecialchars($job['salary_max']); ?></p>
                <p><strong>Posted:</strong> <?php echo date('M d, Y', strtotime($job['posted_date'])); ?></p>
                <p><strong>Deadline:</strong> <?php echo date('M d, Y', strtotime($job['deadline'])); ?></p>
            </div>
            
            <div class="job-description">
                <h3>Job Description</h3>
                <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
            </div>
            
            <div class="employer-contact">
                <h3>Employer Information</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($job['employer_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($job['employer_email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($job['employer_phone']); ?></p>
            </div>
            
            <div class="apply-section">
                <?php if ($already_applied): ?>
                    <p class="info-message">You have already applied for this job.</p>
                <?php else: ?>
                    <a href="apply_job.php?id=<?php echo $job_id; ?>" class="btn btn-primary">Apply for This Job</a>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
