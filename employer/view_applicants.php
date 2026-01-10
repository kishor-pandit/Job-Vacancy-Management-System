<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

// Check if user is an employer
if ($_SESSION['role'] !== 'employer') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$employer_id = $_SESSION['user_id'];
$job_id = intval($_GET['job_id'] ?? 0);

// Verify job belongs to employer
$job_verify = $conn->prepare("SELECT title FROM jobs WHERE id = ? AND employer_id = ?");
$job_verify->bind_param("ii", $job_id, $employer_id);
$job_verify->execute();
$job_result = $job_verify->get_result();

if ($job_result->num_rows === 0) {
    header("Location: dashboard.php");
    exit();
}

$job = $job_result->fetch_assoc();

// Fetch all applicants for this job
$applicants_query = $conn->prepare("
    SELECT a.*, u.name, u.email, u.phone, u.city
    FROM applications a
    JOIN users u ON a.employee_id = u.id
    WHERE a.job_id = ?
    ORDER BY a.applied_at DESC
");
$applicants_query->bind_param("i", $job_id);
$applicants_query->execute();
$applicants_result = $applicants_query->get_result();

// Handle approve/reject
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $application_id = intval($_POST['application_id']);
    $action = $_POST['action'];
    
    if ($action == 'approve' || $action == 'reject') {
        $status = $action == 'approve' ? 'approved' : 'rejected';
        $update = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
        $update->bind_param("si", $status, $application_id);
        $update->execute();
        $update->close();
        
        // Refresh page
        header("Location: view_applicants.php?job_id=$job_id");
        exit();
    }
}

$page_title = 'View Applicants - Job Vacancy Management System';
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
    
    <main class="view-applicants">
        <div class="back-link">
            <a href="dashboard.php">&larr; Back to Dashboard</a>
        </div>
        
        <h1>Applicants for: <?php echo htmlspecialchars($job['title']); ?></h1>
        
        <?php if ($applicants_result->num_rows > 0): ?>
            <div class="applicants-list">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>City</th>
                            <th>Applied Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($applicant = $applicants_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($applicant['name']); ?></td>
                                <td><?php echo htmlspecialchars($applicant['email']); ?></td>
                                <td><?php echo htmlspecialchars($applicant['phone'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($applicant['city'] ?? '-'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($applicant['applied_at'])); ?></td>
                                <td class="status-<?php echo strtolower($applicant['status']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($applicant['status'])); ?>
                                </td>
                                <td class="actions">
                                    <?php if ($applicant['status'] == 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="application_id" value="<?php echo $applicant['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-small btn-success">Approve</button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="application_id" value="<?php echo $applicant['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn btn-small btn-danger">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="btn btn-small btn-disabled">Already <?php echo ucfirst($applicant['status']); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No applicants for this job yet.</p>
        <?php endif; ?>
    </main>
    
    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
