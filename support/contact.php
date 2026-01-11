<?php
require_once '../includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $user_id = $_SESSION['user_id'] ?? null;
    
    if (empty($subject) || empty($message)) {
        $error = 'Subject and message are required!';
    } else {
        // Insert support ticket
        $insert_ticket = $conn->prepare("
            INSERT INTO support_tickets (user_id, subject, message, status)
            VALUES (?, ?, ?, 'open')
        ");
        $insert_ticket->bind_param("iss", $user_id, $subject, $message);
        
        if ($insert_ticket->execute()) {
            $success = 'Your support ticket has been submitted! We will get back to you soon.';
        } else {
            $error = 'Failed to submit ticket. Please try again.';
        }
        $insert_ticket->close();
    }
}

$page_title = 'Contact Support - Job Vacancy Management System';
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
    
    <main class="contact-support">
        <div class="form-container">
            <h1>Contact Support</h1>
            <p>If you have any questions or issues, please fill out the form below and we will get back to you as soon as possible.</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="subject">Subject: *</label>
                        <input type="text" id="subject" name="subject" required value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message: *</label>
                        <textarea id="message" name="message" rows="8" required 
                                  placeholder="Please describe your issue or question..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Submit Ticket</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
    
    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
