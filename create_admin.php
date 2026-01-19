<?php
require_once 'includes/config.php';

$error = '';
$success = '';
$show_form = true;

// Simple authentication - check if admin already exists
$admin_check = $conn->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
$admin_check->execute();
$admin_result = $admin_check->get_result();
$admin_data = $admin_result->fetch_assoc();

// If admin already exists and user is not logged in as admin, deny access
if ($admin_data['admin_count'] > 0 && (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin')) {
    $error = 'Admin already exists. You cannot create another admin account.';
    $show_form = false;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $show_form) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $secret_key = trim($_POST['secret_key']);
    
    // Check secret key (change this to your own secret)
    $correct_secret = 'ADMIN_SECRET_2024';
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($secret_key)) {
        $error = 'All fields are required!';
    } elseif ($secret_key !== $correct_secret) {
        $error = 'Invalid secret key!';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match!';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format!';
    } else {
        // Check if email already exists
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $check_email->store_result();
        
        if ($check_email->num_rows > 0) {
            $error = 'Email already registered!';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert new admin user
            $insert = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $role = 'admin';
            $insert->bind_param("ssss", $name, $email, $hashed_password, $role);
            
            if ($insert->execute()) {
                $success = 'Admin account created successfully! <a href="login.php">Click here to login</a>';
                $show_form = false;
            } else {
                $error = 'Failed to create admin account. Please try again.';
            }
            $insert->close();
        }
        $check_email->close();
    }
}

$page_title = 'Create Admin Account';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .admin-creation {
            min-height: calc(100vh - 200px);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }
        .admin-box {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            width: 100%;
        }
        .admin-box h1 {
            color: #dc3545;
            text-align: center;
            margin-bottom: 10px;
        }
        .admin-box .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #dc3545;
            box-shadow: 0 0 5px rgba(220, 53, 69, 0.25);
        }
        .secret-key-hint {
            background: #fff3cd;
            padding: 12px;
            border-radius: 4px;
            margin-top: 10px;
            color: #856404;
            font-size: 12px;
        }
    </style>
</head>
<body class="admin-creation">
    <div class="admin-box">
        <h1>🔐 Admin Setup</h1>
        <p class="subtitle">Create Administrator Account</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php else: ?>
            <?php if ($show_form): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Full Name:</label>
                        <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="secret_key">Secret Admin Key:</label>
                        <input type="password" id="secret_key" name="secret_key" required placeholder="Enter the secret key">
                        <div class="secret-key-hint">
                            ℹ️ <strong>Hint:</strong> This is a special key to verify you are authorized to create an admin account.
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">Create Admin Account</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
