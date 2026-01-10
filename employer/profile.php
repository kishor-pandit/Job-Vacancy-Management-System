<?php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';

// Check if user is an employer
if ($_SESSION['role'] !== 'employer') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$employer_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch user data
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $employer_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $city = trim($_POST['city']);
    
    if (empty($name) || empty($email)) {
        $error = 'Name and email are required!';
    } else {
        // Update user profile
        $update = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, city = ? WHERE id = ?");
        $update->bind_param("ssssi", $name, $email, $phone, $city, $employer_id);
        
        if ($update->execute()) {
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated successfully!';
            // Refresh user data
            $user_query->execute();
            $user_result = $user_query->get_result();
            $user = $user_result->fetch_assoc();
        } else {
            $error = 'Failed to update profile. Please try again.';
        }
        $update->close();
    }
}

$page_title = 'My Profile - Job Vacancy Management System';
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
    
    <main class="profile-page">
        <div class="back-link">
            <a href="dashboard.php">&larr; Back to Dashboard</a>
        </div>
        
        <div class="profile-container">
            <h1>Company Profile</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Company Name:</label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($user['name']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="city">City:</label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                </div>
                
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </main>
    
    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
