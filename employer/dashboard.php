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
    <style>
        .deadline-warning {
            padding: 10px 15px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        .deadline-expired {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .deadline-warning-text {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 20px;
        }
        .table th,
        .table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .table th {
            background-color: #007bff;
            color: white;
        }
        .table tr:hover {
            background-color: #f5f5f5;
        }
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        .status-closed {
            color: #6c757d;
            font-weight: bold;
        }
        .status-draft {
            color: #ffc107;
            font-weight: bold;
        }
        .status-expired {
            color: #dc3545;
            font-weight: bold;
            background: #ffe5e5;
            padding: 4px 8px;
            border-radius: 4px;
        }
        .deadline-info {
            font-size: 12px;
            color: #666;
        }
        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
        }
        .app-count a {
            color: #007bff;
            text-decoration: none;
        }
        .app-count a:hover {
            text-decoration: underline;
        }
    </style>
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
                                    <th>Deadline</th>
                                    <th>Applications</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($job = $jobs_result->fetch_assoc()): ?>
                                    <?php
                                        // Check if deadline has passed
                                        $is_expired = ($job['deadline'] && strtotime($job['deadline']) < time());
                                        $days_left = 0;
                                        if ($job['deadline'] && !$is_expired) {
                                            $days_left = ceil((strtotime($job['deadline']) - time()) / (60 * 60 * 24));
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($job['title']); ?></td>
                                        <td class="status-<?php echo strtolower($job['status']); ?>">
                                            <?php echo ucfirst(htmlspecialchars($job['status'])); ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($job['posted_date'])); ?></td>
                                        <td>
                                            <?php if ($job['deadline']): ?>
                                                <div class="deadline-info">
                                                    <?php echo date('M d, Y', strtotime($job['deadline'])); ?>
                                                </div>
                                                <?php if ($is_expired): ?>
                                                    <span class="status-expired">EXPIRED</span>
                                                <?php elseif ($days_left <= 7): ?>
                                                    <span style="color: #ff9800; font-weight: bold;"><?php echo $days_left; ?> days left</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="deadline-info">No deadline</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="app-count">
                                            <a href="view_applicants.php?job_id=<?php echo $job['id']; ?>">
                                                <?php echo $app_counts[$job['id']] ?? 0; ?> Applications
                                            </a>
                                        </td>
                                        <td class="actions">
                                            <a href="edit_job.php?id=<?php echo $job['id']; ?>" class="btn btn-small btn-secondary">Edit</a>
                                            <a href="delete_job.php?id=<?php echo $job['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this job?')">Delete</a>
                                            <?php if ($is_expired): ?>
                                                <span style="font-size: 11px; color: #dc3545; font-weight: bold;">⚠ Deadline Passed</span>
                                            <?php endif; ?>
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
