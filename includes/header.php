<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Job Vacancy Management System'; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="<?php echo BASE_URL; ?>" class="logo">Job Vacancy System</a>
            <ul class="nav-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span></li>
                    <?php if ($_SESSION['role'] == 'employee'): ?>
                        <li><a href="<?php echo BASE_URL; ?>employee/dashboard.php">Dashboard</a></li>
                        <li><a href="<?php echo BASE_URL; ?>employee/profile.php">Profile</a></li>
                    <?php elseif ($_SESSION['role'] == 'employer'): ?>
                        <li><a href="<?php echo BASE_URL; ?>employer/dashboard.php">Dashboard</a></li>
                        <li><a href="<?php echo BASE_URL; ?>employer/profile.php">Profile</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>login.php">Login</a></li>
                    <li><a href="<?php echo BASE_URL; ?>register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <div class="container">
