<?php
require_once '../includes/config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

// Fetch statistics
$users_query = $conn->prepare("SELECT COUNT(*) as total FROM users");
$users_query->execute();
$users_result = $users_query->get_result();
$users_data = $users_result->fetch_assoc();

$jobs_query = $conn->prepare("SELECT COUNT(*) as total FROM jobs");
$jobs_query->execute();
$jobs_result = $jobs_query->get_result();
$jobs_data = $jobs_result->fetch_assoc();

$applications_query = $conn->prepare("SELECT COUNT(*) as total FROM applications");
$applications_query->execute();
$applications_result = $applications_query->get_result();
$applications_data = $applications_result->fetch_assoc();

// Fetch recent users
$recent_users_query = $conn->prepare("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");
$recent_users_query->execute();
$recent_users_result = $recent_users_query->get_result();

$page_title = 'Admin Dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #007bff;
            margin: 10px 0;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
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
    </style>
</head>
<body>
    <?php require_once '../includes/header.php'; ?>
    
    <main class="admin-page">
        <div class="container">
            <h1>Admin Dashboard</h1>
            
            <!-- Statistics Cards -->
            <div class="admin-container">
                <div class="stat-card">
                    <div class="stat-label">Total Users</div>
                    <div class="stat-number"><?php echo $users_data['total']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Jobs</div>
                    <div class="stat-number"><?php echo $jobs_data['total']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Applications</div>
                    <div class="stat-number"><?php echo $applications_data['total']; ?></div>
                </div>
            </div>

            <!-- Recent Users Table -->
            <section class="recent-users">
                <h2>Recent Users</h2>
                <?php if ($recent_users_result->num_rows > 0): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $recent_users_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><span class="badge"><?php echo htmlspecialchars($user['role']); ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No users found.</p>
                <?php endif; ?>
            </section>

            <!-- Admin Tools -->
            <section class="admin-tools" style="margin-top: 40px;">
                <h2>Admin Tools</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <a href="manage_users.php" class="btn btn-primary">Manage Users</a>
                    <a href="manage_jobs.php" class="btn btn-primary">Manage Jobs</a>
                    <a href="manage_applications.php" class="btn btn-primary">Manage Applications</a>
                    <a href="settings.php" class="btn btn-primary">Settings</a>
                </div>
            </section>
        </div>
    </main>

    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
