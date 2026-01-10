<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

// Check if user is an employee
if ($_SESSION['role'] !== 'employee') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// Fetch all active jobs
$jobs_query = $conn->prepare("SELECT * FROM jobs WHERE status = 'active' ORDER BY posted_date DESC");
$jobs_query->execute();
$jobs_result = $jobs_query->get_result();

// Fetch user's applications
$user_id = $_SESSION['user_id'];
$apps_query = $conn->prepare("
    SELECT a.*, j.title, j.salary_min, j.salary_max, u.name as employer_name 
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN users u ON j.employer_id = u.id
    WHERE a.employee_id = ?
    ORDER BY a.applied_at DESC
");
$apps_query->bind_param("i", $user_id);
$apps_query->execute();
$apps_result = $apps_query->get_result();

$page_title = 'Employee Dashboard - Job Vacancy Management System';
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
    
    <main class="dashboard employee-dashboard">
        <h1>Employee Dashboard</h1>
        
        <div class="dashboard-grid">
            <!-- Available Jobs Section -->
            <section class="dashboard-section">
                <h2>Available Jobs</h2>
                
                <?php if ($jobs_result->num_rows > 0): ?>
                    <div class="jobs-list">
                        <?php while ($job = $jobs_result->fetch_assoc()): ?>
                            <div class="job-card">
                                <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                                <p class="employer"><strong>Employer:</strong> <?php echo htmlspecialchars($job['employer_id']); ?></p>
                                <p class="job-type"><strong>Type:</strong> <?php echo htmlspecialchars($job['job_type']); ?></p>
                                <p class="salary">
                                    <strong>Salary:</strong> 
                                    $<?php echo htmlspecialchars($job['salary_min']); ?> - $<?php echo htmlspecialchars($job['salary_max']); ?>
                                </p>
                                <p class="location"><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                                <p class="description"><?php echo substr(htmlspecialchars($job['description']), 0, 150) . '...'; ?></p>
                                <div class="job-actions">
                                    <a href="view_job.php?id=<?php echo $job['id']; ?>" class="btn btn-secondary">View Details</a>
                                    <a href="apply_job.php?id=<?php echo $job['id']; ?>" class="btn btn-primary">Apply Now</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>No jobs available at the moment.</p>
                <?php endif; ?>
            </section>
            
            <!-- My Applications Section -->
            <section class="dashboard-section">
                <h2>My Applications</h2>
                
                <?php if ($apps_result->num_rows > 0): ?>
                    <div class="applications-list">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Job Title</th>
                                    <th>Employer</th>
                                    <th>Status</th>
                                    <th>Applied Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($app = $apps_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($app['title']); ?></td>
                                        <td><?php echo htmlspecialchars($app['employer_name']); ?></td>
                                        <td class="status-<?php echo strtolower($app['status']); ?>">
                                            <?php echo ucfirst(htmlspecialchars($app['status'])); ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></td>
                                        <td>
                                            <a href="view_job.php?id=<?php echo $app['job_id']; ?>" class="btn btn-small">View Job</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>You haven't applied to any jobs yet.</p>
                <?php endif; ?>
            </section>
        </div>
    </main>
    
    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
