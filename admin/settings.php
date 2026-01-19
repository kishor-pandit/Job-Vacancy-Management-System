<?php
require_once '../includes/config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

$error = '';
$success = '';
$page_title = 'Admin Settings';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .settings-container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .setting-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        .setting-section:last-child {
            border-bottom: none;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
    </style>
</head>
<body>
    <?php require_once '../includes/header.php'; ?>
    
    <main class="admin-page">
        <div class="settings-container">
            <h1>Admin Settings</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="setting-section">
                <h2>System Information</h2>
                <p><strong>System Name:</strong> Job Vacancy Management System</p>
                <p><strong>Current Date:</strong> <?php echo date('F d, Y H:i:s'); ?></p>
                <p><strong>Database Status:</strong> <span style="color: #28a745;">Connected</span></p>
            </div>
            
            <div class="setting-section">
                <h2>Admin Account</h2>
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" disabled value="<?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>">
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" disabled value="<?php echo htmlspecialchars($_SESSION['email'] ?? 'admin@system.com'); ?>">
                </div>
            </div>
            
            <div class="setting-section">
                <h2>Quick Links</h2>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 10px;">
                        <a href="manage_users.php" style="color: #007bff; text-decoration: none;">→ Manage Users</a>
                    </li>
                    <li style="margin-bottom: 10px;">
                        <a href="manage_jobs.php" style="color: #007bff; text-decoration: none;">→ Manage Jobs</a>
                    </li>
                    <li style="margin-bottom: 10px;">
                        <a href="manage_applications.php" style="color: #007bff; text-decoration: none;">→ Manage Applications</a>
                    </li>
                    <li>
                        <a href="dashboard.php" style="color: #007bff; text-decoration: none;">→ Back to Dashboard</a>
                    </li>
                </ul>
            </div>
            
            <div style="margin-top: 30px;">
                <a href="../logout.php" class="btn btn-danger" style="background-color: #dc3545;">Logout</a>
            </div>
        </div>
    </main>

    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
