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
    <style>
        .applicants-list table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 20px;
        }
        .applicants-list th,
        .applicants-list td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .applicants-list th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        .applicants-list tr:hover {
            background-color: #f5f5f5;
        }
        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .actions form {
            margin: 0;
        }
        .view-letter-btn {
            background-color: #17a2b8;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }
        .view-letter-btn:hover {
            background-color: #138496;
        }
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        .status-approved {
            color: #28a745;
            font-weight: bold;
        }
        .status-rejected {
            color: #dc3545;
            font-weight: bold;
        }
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal.show {
            display: block;
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 700px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
        }
        .modal-header h2 {
            margin: 0;
            color: #007bff;
        }
        .close-btn {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }
        .close-btn:hover {
            color: #333;
        }
        .letter-content {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 4px;
            line-height: 1.6;
            color: #333;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .applicant-info {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .applicant-info p {
            margin: 8px 0;
        }
        .back-link {
            margin-bottom: 20px;
        }
        .back-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
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
                                    <button class="view-letter-btn" onclick="openModal('modal-<?php echo $applicant['id']; ?>')">View Letter</button>
                                    
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
                            
                            <!-- Modal for Cover Letter -->
                            <div id="modal-<?php echo $applicant['id']; ?>" class="modal">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h2>Cover Letter</h2>
                                        <button class="close-btn" onclick="closeModal('modal-<?php echo $applicant['id']; ?>')">&times;</button>
                                    </div>
                                    
                                    <div class="applicant-info">
                                        <p><strong>Applicant:</strong> <?php echo htmlspecialchars($applicant['name']); ?></p>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($applicant['email']); ?></p>
                                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($applicant['phone'] ?? 'Not provided'); ?></p>
                                        <p><strong>Applied Date:</strong> <?php echo date('M d, Y H:i', strtotime($applicant['applied_at'])); ?></p>
                                    </div>
                                    
                                    <h3>Cover Letter:</h3>
                                    <div class="letter-content">
                                        <?php echo htmlspecialchars($applicant['cover_letter'] ?: 'No cover letter provided'); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No applicants for this job yet.</p>
        <?php endif; ?>
    </main>
    
    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }
        
        // Close modal when clicking outside the content
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        }
    </script>
    
    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
