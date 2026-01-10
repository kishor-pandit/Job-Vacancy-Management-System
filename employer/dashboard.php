<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

// Check if user is an employer
if ($_SESSION['role'] !== 'employer') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$employer_id = $_SESSION['user_id'];

// Fetch all jobs posted by this employer
$jobs_query = $conn->prepare("
    SELECT * FROM jobs 
    WHERE employer_id = ? 
    ORDER BY posted_date DESC
");
$jobs_query->bind_param("i", $employer_id);
$jobs_query->execute();
$jobs_result = $jobs_query->get_result();

// Count applications for each job
$stats_query = $conn->prepare("
    SELECT j.id, COUNT(a.id) as app_count 
    FROM jobs j 
    LEFT JOIN applications a ON j.id = a.job_id 
    WHERE j.employer_id = ? 
    GROUP BY j.id
");
$stats_query->bind_param("i", $employer_id);
$stats_query->execute();
$stats_result = $stats_query->get_result();
$app_counts = [];
while ($stat = $stats_result->fetch_assoc()) {
    $app_counts[$stat['id']] = $stat['app_count'];
}

$page_title = 'Employer Dashboard - Job Vacancy Management System';
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
    
    <main class="dashboard employer-dashboard">
        <h1>Employer Dashboard</h1>
        
        <div class="dashboard-actions">
            <a href="post_job.php" class="btn btn-primary">+ Post New Job</a>
        </div>
        
        <div class="dashboard-grid">
            <!-- Posted Jobs Section -->
            <section class="dashboard-section">
                <h2>My Posted Jobs</h2>
                
                <?php if ($jobs_result->num_rows > 0): ?>
                    <div class="jobs-management">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Job Title</th>
                                    <th>Status</th>
                                    <th>Posted Date</th>
                                    <th>Applications</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($job = $jobs_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($job['title']); ?></td>
                                        <td class="status-<?php echo strtolower($job['status']); ?>">
                                            <?php echo ucfirst(htmlspecialchars($job['status'])); ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($job['posted_date'])); ?></td>
                                        <td class="app-count">
                                            <a href="view_applicants.php?job_id=<?php echo $job['id']; ?>">
                                                <?php echo $app_counts[$job['id']] ?? 0; ?> Applications
                                            </a>
                                        </td>
                                        <td class="actions">
                                            <a href="edit_job.php?id=<?php echo $job['id']; ?>" class="btn btn-small btn-secondary">Edit</a>
                                            <a href="delete_job.php?id=<?php echo $job['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>You haven't posted any jobs yet. <a href="post_job.php">Post your first job now!</a></p>
                <?php endif; ?>
            </section>
        </div>
    </main>
    
    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
