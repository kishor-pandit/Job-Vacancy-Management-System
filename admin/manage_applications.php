<?php
require_once '../includes/config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

$error = '';
$success = '';

// Handle delete application
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_query = $conn->prepare("DELETE FROM applications WHERE id = ?");
    $delete_query->bind_param("i", $delete_id);
    
    if ($delete_query->execute()) {
        $success = 'Application deleted successfully!';
    } else {
        $error = 'Failed to delete application.';
    }
    $delete_query->close();
}

// Fetch all applications with job and employee details
$applications_query = $conn->prepare("
    SELECT a.id, a.status, a.applied_at, j.title as job_title, u.name as employee_name
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN users u ON a.employee_id = u.id
    ORDER BY a.applied_at DESC
");
$applications_query->execute();
$applications_result = $applications_query->get_result();

$page_title = 'Manage Applications - Admin Dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            cursor: pointer;
            border: none;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
        }
        .admin-table th,
        .admin-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .admin-table th {
            background-color: #007bff;
            color: white;
        }
        .admin-table tr:hover {
            background-color: #f5f5f5;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-pending {
            background-color: #ffc107;
            color: black;
        }
        .badge-approved {
            background-color: #28a745;
            color: white;
        }
        .badge-rejected {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <?php require_once '../includes/header.php'; ?>
    
    <main class="admin-page">
        <div class="container">
            <h1>Manage Applications</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee</th>
                        <th>Job Title</th>
                        <th>Status</th>
                        <th>Applied Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($app = $applications_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $app['id']; ?></td>
                            <td><?php echo htmlspecialchars($app['employee_name']); ?></td>
                            <td><?php echo htmlspecialchars($app['job_title']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $app['status']; ?>">
                                    <?php echo ucfirst($app['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?delete_id=<?php echo $app['id']; ?>" class="btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div style="margin-top: 20px;">
                <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
            </div>
        </div>
    </main>

    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
