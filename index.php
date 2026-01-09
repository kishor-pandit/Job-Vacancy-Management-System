<?php
require_once 'includes/config.php';

// Fetch all active jobs
$jobs_query = $conn->prepare("SELECT * FROM jobs WHERE status = 'active' ORDER BY posted_date DESC");
$jobs_query->execute();
$jobs_result = $jobs_query->get_result();

$page_title = 'Job Vacancy Management System - Find Your Dream Job';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    
    <main class="home-page">
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h1>Find Your Dream Job</h1>
                <p>Browse thousands of job opportunities and start your career journey</p>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="hero-buttons">
                        <a href="register.php?role=employee" class="btn btn-primary">I'm Looking for a Job</a>
                        <a href="register.php?role=employer" class="btn btn-secondary">I'm Hiring</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- Job Listings Section -->
        <section class="job-listings">
            <h2>Latest Job Openings</h2>
            
            <?php if ($jobs_result->num_rows > 0): ?>
                <div class="jobs-grid">
                    <?php while ($job = $jobs_result->fetch_assoc()): ?>
                        <div class="job-card">
                            <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                            <p class="job-type"><span class="badge"><?php echo htmlspecialchars($job['job_type']); ?></span></p>
                            <p class="location">
                                <strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?>
                            </p>
                            <p class="salary">
                                <strong>Salary:</strong> 
                                $<?php echo htmlspecialchars($job['salary_min']); ?> - $<?php echo htmlspecialchars($job['salary_max']); ?>
                            </p>
                            <p class="description">
                                <?php echo substr(htmlspecialchars($job['description']), 0, 100) . '...'; ?>
                            </p>
                            <div class="job-actions">
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'employee'): ?>
                                    <a href="employee/view_job.php?id=<?php echo $job['id']; ?>" class="btn btn-primary">View Details</a>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-primary">Login to Apply</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="no-jobs">No jobs available at the moment. Please check back later.</p>
            <?php endif; ?>
        </section>
        
        <!-- Features Section -->
        <section class="features">
            <h2>Why Choose Us?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <h3>Easy to Use</h3>
                    <p>Simple and intuitive interface for both job seekers and employers.</p>
                </div>
                <div class="feature-card">
                    <h3>Wide Selection</h3>
                    <p>Access to thousands of job opportunities from top companies.</p>
                </div>
                <div class="feature-card">
                    <h3>Secure</h3>
                    <p>Your data is protected with industry-leading security measures.</p>
                </div>
                <div class="feature-card">
                    <h3>Fast Matching</h3>
                    <p>Find the right job or candidate quickly and efficiently.</p>
                </div>
            </div>
        </section>
    </main>
    
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>
