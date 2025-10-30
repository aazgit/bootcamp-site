<?php
/*
 * Kala-Klub Figma EDU Bootcamp Application System
 * Secure PHP form handling with validation, sanitization, and email functionality
 * Compatible with PHP 7.4+ and shared hosting environments
 */

// Configuration
$config = [
    'admin_email' => 'admissions@kala-klub.com',
    'from_email' => 'noreply@kala-klub.com',
    'from_name' => 'Kala-Klub Admissions',
    'success_redirect' => 'apply-success.html',
    'csv_file' => 'applications/applications.csv',
    'max_file_size' => 5242880, // 5MB
    'allowed_extensions' => ['pdf', 'doc', 'docx', 'txt'],
    'rate_limit_minutes' => 5,
    'honeypot_field' => 'website'
];

// Initialize variables
$form_errors = [];
$form_data = [];
$success = false;

// Rate limiting check
function checkRateLimit($minutes = 5) {
    session_start();
    $now = time();
    $limit_key = 'last_application_' . $_SERVER['REMOTE_ADDR'];
    
    if (isset($_SESSION[$limit_key])) {
        $time_diff = ($now - $_SESSION[$limit_key]) / 60;
        if ($time_diff < $minutes) {
            return false;
        }
    }
    
    $_SESSION[$limit_key] = $now;
    return true;
}

// Sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate phone number
function isValidPhone($phone) {
    $cleaned = preg_replace('/[^\d+]/', '', $phone);
    return strlen($cleaned) >= 10;
}

// Validate required field
function validateRequired($value, $field_name) {
    if (empty(trim($value))) {
        return "The {$field_name} field is required.";
    }
    return null;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check rate limiting
    if (!checkRateLimit($config['rate_limit_minutes'])) {
        $form_errors['general'] = 'Please wait before submitting another application.';
    }
    
    // Check honeypot field (spam protection)
    if (!empty($_POST[$config['honeypot_field']])) {
        // This is likely spam, silently ignore
        header('Location: ' . $config['success_redirect']);
        exit;
    }
    
    // Sanitize all inputs
    $form_data = [
        'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
        'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
        'email' => sanitizeInput($_POST['email'] ?? ''),
        'phone' => sanitizeInput($_POST['phone'] ?? ''),
        'cohort' => sanitizeInput($_POST['cohort'] ?? ''),
        'experience_level' => sanitizeInput($_POST['experience_level'] ?? ''),
        'portfolio_url' => sanitizeInput($_POST['portfolio_url'] ?? ''),
        'motivation' => sanitizeInput($_POST['motivation'] ?? ''),
        'goals' => sanitizeInput($_POST['goals'] ?? ''),
        'time_commitment' => sanitizeInput($_POST['time_commitment'] ?? ''),
        'heard_about' => sanitizeInput($_POST['heard_about'] ?? ''),
        'additional_info' => sanitizeInput($_POST['additional_info'] ?? '')
    ];
    
    // Validation
    if (empty($form_errors)) {
        // Required field validation
        $required_fields = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name', 
            'email' => 'Email',
            'phone' => 'Phone',
            'cohort' => 'Preferred Cohort',
            'experience_level' => 'Experience Level',
            'motivation' => 'Motivation',
            'time_commitment' => 'Time Commitment'
        ];
        
        foreach ($required_fields as $field => $label) {
            $error = validateRequired($form_data[$field], $label);
            if ($error) {
                $form_errors[$field] = $error;
            }
        }
        
        // Email validation
        if (!empty($form_data['email']) && !isValidEmail($form_data['email'])) {
            $form_errors['email'] = 'Please enter a valid email address.';
        }
        
        // Phone validation
        if (!empty($form_data['phone']) && !isValidPhone($form_data['phone'])) {
            $form_errors['phone'] = 'Please enter a valid phone number.';
        }
        
        // URL validation (if provided)
        if (!empty($form_data['portfolio_url'])) {
            if (!filter_var($form_data['portfolio_url'], FILTER_VALIDATE_URL)) {
                $form_errors['portfolio_url'] = 'Please enter a valid URL.';
            }
        }
        
        // Text length validation
        if (strlen($form_data['motivation']) < 50) {
            $form_errors['motivation'] = 'Please provide a more detailed motivation (minimum 50 characters).';
        }
    }
    
    // If no errors, process the application
    if (empty($form_errors)) {
        try {
            // Prepare CSV data
            $csv_data = [
                date('Y-m-d H:i:s'),
                $form_data['first_name'],
                $form_data['last_name'],
                $form_data['email'],
                $form_data['phone'],
                $form_data['cohort'],
                $form_data['experience_level'],
                $form_data['portfolio_url'],
                str_replace(["\r\n", "\r", "\n"], ' | ', $form_data['motivation']),
                str_replace(["\r\n", "\r", "\n"], ' | ', $form_data['goals']),
                $form_data['time_commitment'],
                $form_data['heard_about'],
                str_replace(["\r\n", "\r", "\n"], ' | ', $form_data['additional_info']),
                $_SERVER['REMOTE_ADDR']
            ];
            
            // Create applications directory if it doesn't exist
            if (!file_exists('applications')) {
                mkdir('applications', 0755, true);
            }
            
            // Save to CSV file
            $csv_file = fopen($config['csv_file'], 'a');
            if ($csv_file) {
                fputcsv($csv_file, $csv_data);
                fclose($csv_file);
            }
            
            // Prepare email content
            $email_subject = 'New Bootcamp Application - ' . $form_data['first_name'] . ' ' . $form_data['last_name'];
            $email_body = "
New Figma EDU Bootcamp Application Received

Applicant Details:
=================
Name: {$form_data['first_name']} {$form_data['last_name']}
Email: {$form_data['email']}
Phone: {$form_data['phone']}
Preferred Cohort: {$form_data['cohort']}
Experience Level: {$form_data['experience_level']}
Portfolio URL: {$form_data['portfolio_url']}
Time Commitment Confirmed: {$form_data['time_commitment']}
How they heard about us: {$form_data['heard_about']}

Motivation:
===========
{$form_data['motivation']}

Goals:
======
{$form_data['goals']}

Additional Information:
======================
{$form_data['additional_info']}

Application submitted on: " . date('Y-m-d H:i:s') . "
IP Address: {$_SERVER['REMOTE_ADDR']}
";
            
            // Send email to admin
            $headers = [
                'From: ' . $config['from_name'] . ' <' . $config['from_email'] . '>',
                'Reply-To: ' . $form_data['email'],
                'Content-Type: text/plain; charset=UTF-8',
                'X-Mailer: PHP/' . phpversion()
            ];
            
            $mail_sent = mail($config['admin_email'], $email_subject, $email_body, implode("\r\n", $headers));
            
            // Send confirmation email to applicant
            $confirmation_subject = 'Application Received - Kala-Klub Figma EDU Bootcamp';
            $confirmation_body = "
Dear {$form_data['first_name']},

Thank you for your application to the Kala-Klub Figma EDU Bootcamp!

We have received your application for the {$form_data['cohort']} cohort and will review it carefully. Our admissions team will contact you within 3-5 business days with next steps.

Application Summary:
- Name: {$form_data['first_name']} {$form_data['last_name']}
- Email: {$form_data['email']}
- Preferred Cohort: {$form_data['cohort']}
- Submitted: " . date('F j, Y \a\t g:i A') . "

What's Next:
1. Our team will review your application
2. We may schedule a brief interview call
3. You'll receive an admission decision within one week
4. If accepted, you'll receive enrollment instructions

In the meantime, feel free to:
- Join our community: [Slack invite link]
- Follow us on LinkedIn for updates
- Review the curriculum at https://kala-klub.com/curriculum.html

If you have any questions, please don't hesitate to contact us at admissions@kala-klub.com.

Best regards,
The Kala-Klub Team

---
Kala-Klub Figma EDU Bootcamp
175, Sonagiri, Bhopal, MP
https://kala-klub.com
";
            
            $confirmation_headers = [
                'From: ' . $config['from_name'] . ' <' . $config['from_email'] . '>',
                'Content-Type: text/plain; charset=UTF-8',
                'X-Mailer: PHP/' . phpversion()
            ];
            
            mail($form_data['email'], $confirmation_subject, $confirmation_body, implode("\r\n", $confirmation_headers));
            
            // Redirect to success page
            header('Location: ' . $config['success_redirect']);
            exit;
            
        } catch (Exception $e) {
            $form_errors['general'] = 'There was an error processing your application. Please try again.';
            error_log('Application form error: ' . $e->getMessage());
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Apply to Kala-Klub Figma EDU Bootcamp. Submit your application for professional design education and transform your career with expert instruction.">
    <meta name="keywords" content="apply Figma bootcamp, design education application, UI UX bootcamp enrollment">
    <meta name="author" content="Kala-Klub">
    <title>Apply Now - Kala-Klub Figma EDU Bootcamp</title>
    <link rel="canonical" href="https://kala-klub.com/apply.php">
    <link rel="stylesheet" href="css/main.css">
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
</head>
<body>
    <!-- Header Navigation -->
    <header class="header">
        <nav class="navbar">
            <a href="index.html" class="logo">Kala-Klub</a>
            <button class="nav-toggle" aria-label="Toggle navigation menu">☰</button>
            <ul class="nav-menu">
                <li><a href="index.html" class="nav-link">Home</a></li>
                <li><a href="about.html" class="nav-link">About</a></li>
                <li><a href="curriculum.html" class="nav-link">Curriculum</a></li>
                <li><a href="instructors.html" class="nav-link">Instructors</a></li>
                <li><a href="schedule.html" class="nav-link">Schedule</a></li>
                <li><a href="apply.php" class="nav-link active">Apply</a></li>
                <li><a href="contact.php" class="nav-link">Contact</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main id="main-content">
        <!-- Page Header -->
        <section class="hero" style="padding: 6rem 0 4rem;">
            <div class="container">
                <div class="hero-content fade-in">
                    <h1>Apply to Join Our Program</h1>
                    <p class="hero-subtitle">Take the first step towards becoming a professional UI/UX designer. Complete the application below to secure your spot in our next cohort.</p>
                </div>
            </div>
        </section>

        <!-- Application Form -->
        <section class="section">
            <div class="container">
                <?php if (!empty($form_errors)): ?>
                <div class="card glass-panel" style="background: rgba(231, 76, 60, 0.1); border-color: rgba(231, 76, 60, 0.3); margin-bottom: 2rem;">
                    <h3 style="color: #e74c3c;">Please correct the following errors:</h3>
                    <ul style="color: #e74c3c;">
                        <?php foreach ($form_errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-3">
                        <!-- Application Tips -->
                        <div class="card glass-panel">
                            <h3 style="color: #3498db;">💡 Application Tips</h3>
                            <ul style="margin-bottom: 2rem;">
                                <li><strong>Be Genuine:</strong> Share your authentic motivation and goals</li>
                                <li><strong>Portfolio:</strong> Include any creative work, even if not design-related</li>
                                <li><strong>Commitment:</strong> Confirm you can dedicate 15-20 hours per week</li>
                                <li><strong>Questions:</strong> Don't hesitate to ask if you need clarification</li>
                            </ul>
                            
                            <h4 style="color: #27ae60;">What Happens Next?</h4>
                            <ol style="margin-bottom: 1.5rem;">
                                <li>Application review (3-5 days)</li>
                                <li>Brief interview call (if selected)</li>
                                <li>Admission decision (within 1 week)</li>
                                <li>Enrollment and payment instructions</li>
                            </ol>
                            
                            <div style="background: rgba(52, 152, 219, 0.1); padding: 1rem; border-radius: 8px;">
                                <h4 style="color: #3498db;">📞 Need Help?</h4>
                                <p style="margin: 0; font-size: 0.9rem;">Contact our admissions team at <a href="mailto:admissions@kala-klub.com" style="color: #3498db;">admissions@kala-klub.com</a></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-3" style="flex: 2;">
                        <!-- Application Form -->
                        <div class="card glass-panel">
                            <h2>Application Form</h2>
                            <form method="post" action="apply.php" id="application-form">
                                <!-- Honeypot field for spam protection -->
                                <input type="text" name="<?php echo $config['honeypot_field']; ?>" style="display: none;" tabindex="-1" autocomplete="off">
                                
                                <!-- Personal Information -->
                                <fieldset style="border: none; padding: 0; margin-bottom: 2rem;">
                                    <legend style="font-size: 1.2rem; font-weight: 600; color: #2c3e50; margin-bottom: 1rem;">Personal Information</legend>
                                    
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="form-group">
                                                <label for="first_name" class="form-label">First Name *</label>
                                                <input type="text" id="first_name" name="first_name" class="form-input" 
                                                       value="<?php echo htmlspecialchars($form_data['first_name'] ?? ''); ?>" 
                                                       required maxlength="50">
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="form-group">
                                                <label for="last_name" class="form-label">Last Name *</label>
                                                <input type="text" id="last_name" name="last_name" class="form-input" 
                                                       value="<?php echo htmlspecialchars($form_data['last_name'] ?? ''); ?>" 
                                                       required maxlength="50">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="form-group">
                                                <label for="email" class="form-label">Email Address *</label>
                                                <input type="email" id="email" name="email" class="form-input" 
                                                       value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" 
                                                       required maxlength="100">
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="form-group">
                                                <label for="phone" class="form-label">Phone Number *</label>
                                                <input type="tel" id="phone" name="phone" class="form-input" 
                                                       value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>" 
                                                       required maxlength="20" placeholder="+91 XXXXXXXXXX">
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                                
                                <!-- Program Information -->
                                <fieldset style="border: none; padding: 0; margin-bottom: 2rem;">
                                    <legend style="font-size: 1.2rem; font-weight: 600; color: #2c3e50; margin-bottom: 1rem;">Program Information</legend>
                                    
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="form-group">
                                                <label for="cohort" class="form-label">Preferred Cohort *</label>
                                                <select id="cohort" name="cohort" class="form-select" required>
                                                    <option value="">Select a cohort</option>
                                                    <option value="Winter 2026" <?php echo ($form_data['cohort'] ?? '') === 'Winter 2026' ? 'selected' : ''; ?>>Winter 2026 (Jan 15 - Apr 9)</option>
                                                    <option value="Spring 2026" <?php echo ($form_data['cohort'] ?? '') === 'Spring 2026' ? 'selected' : ''; ?>>Spring 2026 (Apr 21 - Jul 15)</option>
                                                    <option value="Summer 2026" <?php echo ($form_data['cohort'] ?? '') === 'Summer 2026' ? 'selected' : ''; ?>>Summer 2026 (Jul 27 - Oct 21)</option>
                                                    <option value="Fall 2026" <?php echo ($form_data['cohort'] ?? '') === 'Fall 2026' ? 'selected' : ''; ?>>Fall 2026 (Nov 2 - Jan 27, 2027)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="form-group">
                                                <label for="experience_level" class="form-label">Design Experience Level *</label>
                                                <select id="experience_level" name="experience_level" class="form-select" required>
                                                    <option value="">Select your level</option>
                                                    <option value="Complete Beginner" <?php echo ($form_data['experience_level'] ?? '') === 'Complete Beginner' ? 'selected' : ''; ?>>Complete Beginner</option>
                                                    <option value="Some Creative Experience" <?php echo ($form_data['experience_level'] ?? '') === 'Some Creative Experience' ? 'selected' : ''; ?>>Some Creative Experience</option>
                                                    <option value="Basic Design Knowledge" <?php echo ($form_data['experience_level'] ?? '') === 'Basic Design Knowledge' ? 'selected' : ''; ?>>Basic Design Knowledge</option>
                                                    <option value="Intermediate Designer" <?php echo ($form_data['experience_level'] ?? '') === 'Intermediate Designer' ? 'selected' : ''; ?>>Intermediate Designer</option>
                                                    <option value="Career Changer" <?php echo ($form_data['experience_level'] ?? '') === 'Career Changer' ? 'selected' : ''; ?>>Career Changer</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="portfolio_url" class="form-label">Portfolio URL (Optional)</label>
                                        <input type="url" id="portfolio_url" name="portfolio_url" class="form-input" 
                                               value="<?php echo htmlspecialchars($form_data['portfolio_url'] ?? ''); ?>" 
                                               maxlength="200" placeholder="https://yourportfolio.com or https://dribbble.com/yourusername">
                                        <small style="color: #666; font-size: 0.9rem;">Share any creative work, even if not design-related</small>
                                    </div>
                                </fieldset>
                                
                                <!-- Motivation & Goals -->
                                <fieldset style="border: none; padding: 0; margin-bottom: 2rem;">
                                    <legend style="font-size: 1.2rem; font-weight: 600; color: #2c3e50; margin-bottom: 1rem;">Tell Us About Yourself</legend>
                                    
                                    <div class="form-group">
                                        <label for="motivation" class="form-label">Why do you want to join this bootcamp? *</label>
                                        <textarea id="motivation" name="motivation" class="form-textarea" 
                                                  required minlength="50" maxlength="1000" rows="4" 
                                                  placeholder="Share your motivation for learning design and why this program interests you..."><?php echo htmlspecialchars($form_data['motivation'] ?? ''); ?></textarea>
                                        <small style="color: #666; font-size: 0.9rem;">Minimum 50 characters</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="goals" class="form-label">What are your career goals? (Optional)</label>
                                        <textarea id="goals" name="goals" class="form-textarea" 
                                                  maxlength="500" rows="3" 
                                                  placeholder="What do you hope to achieve after completing the program?"><?php echo htmlspecialchars($form_data['goals'] ?? ''); ?></textarea>
                                    </div>
                                </fieldset>
                                
                                <!-- Commitment & Logistics -->
                                <fieldset style="border: none; padding: 0; margin-bottom: 2rem;">
                                    <legend style="font-size: 1.2rem; font-weight: 600; color: #2c3e50; margin-bottom: 1rem;">Commitment & Logistics</legend>
                                    
                                    <div class="form-group">
                                        <label for="time_commitment" class="form-label">Time Commitment Confirmation *</label>
                                        <select id="time_commitment" name="time_commitment" class="form-select" required>
                                            <option value="">Please confirm</option>
                                            <option value="Yes, I can commit 15-20 hours per week" <?php echo ($form_data['time_commitment'] ?? '') === 'Yes, I can commit 15-20 hours per week' ? 'selected' : ''; ?>>Yes, I can commit 15-20 hours per week</option>
                                            <option value="I need more information about time requirements" <?php echo ($form_data['time_commitment'] ?? '') === 'I need more information about time requirements' ? 'selected' : ''; ?>>I need more information about time requirements</option>
                                            <option value="I can commit but may need flexible scheduling" <?php echo ($form_data['time_commitment'] ?? '') === 'I can commit but may need flexible scheduling' ? 'selected' : ''; ?>>I can commit but may need flexible scheduling</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="heard_about" class="form-label">How did you hear about us? (Optional)</label>
                                        <select id="heard_about" name="heard_about" class="form-select">
                                            <option value="">Select an option</option>
                                            <option value="LinkedIn" <?php echo ($form_data['heard_about'] ?? '') === 'LinkedIn' ? 'selected' : ''; ?>>LinkedIn</option>
                                            <option value="Google Search" <?php echo ($form_data['heard_about'] ?? '') === 'Google Search' ? 'selected' : ''; ?>>Google Search</option>
                                            <option value="Friend/Colleague Referral" <?php echo ($form_data['heard_about'] ?? '') === 'Friend/Colleague Referral' ? 'selected' : ''; ?>>Friend/Colleague Referral</option>
                                            <option value="Design Community" <?php echo ($form_data['heard_about'] ?? '') === 'Design Community' ? 'selected' : ''; ?>>Design Community</option>
                                            <option value="Social Media" <?php echo ($form_data['heard_about'] ?? '') === 'Social Media' ? 'selected' : ''; ?>>Social Media</option>
                                            <option value="Educational Platform" <?php echo ($form_data['heard_about'] ?? '') === 'Educational Platform' ? 'selected' : ''; ?>>Educational Platform</option>
                                            <option value="Other" <?php echo ($form_data['heard_about'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </fieldset>
                                
                                <!-- Additional Information -->
                                <fieldset style="border: none; padding: 0; margin-bottom: 2rem;">
                                    <legend style="font-size: 1.2rem; font-weight: 600; color: #2c3e50; margin-bottom: 1rem;">Additional Information</legend>
                                    
                                    <div class="form-group">
                                        <label for="additional_info" class="form-label">Anything else you'd like us to know? (Optional)</label>
                                        <textarea id="additional_info" name="additional_info" class="form-textarea" 
                                                  maxlength="500" rows="3" 
                                                  placeholder="Questions, special circumstances, accessibility needs, etc."><?php echo htmlspecialchars($form_data['additional_info'] ?? ''); ?></textarea>
                                    </div>
                                </fieldset>
                                
                                <!-- Terms and Submit -->
                                <div style="background: rgba(52, 152, 219, 0.1); padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                                    <h4 style="color: #2c3e50; margin-bottom: 1rem;">Before You Submit</h4>
                                    <ul style="margin-bottom: 1rem; color: #666;">
                                        <li>Review all information for accuracy</li>
                                        <li>Ensure your email is correct for communication</li>
                                        <li>Double-check your preferred cohort dates</li>
                                        <li>Confirm you can meet the time commitment</li>
                                    </ul>
                                    
                                    <label style="display: flex; align-items: flex-start; cursor: pointer;">
                                        <input type="checkbox" required style="margin-right: 0.5rem; margin-top: 0.2rem;">
                                        <span style="font-size: 0.9rem; color: #555;">
                                            I confirm that the information provided is accurate and I understand the program requirements. I agree to be contacted by the Kala-Klub admissions team regarding my application.
                                        </span>
                                    </label>
                                </div>
                                
                                <div style="text-align: center;">
                                    <button type="submit" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 3rem;">
                                        Submit Application
                                    </button>
                                    <p style="font-size: 0.9rem; color: #666; margin-top: 1rem;">
                                        You'll receive a confirmation email within a few minutes
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>Kala-Klub</h4>
                    <p>Professional design education focused on Figma mastery and UI/UX excellence.</p>
                    <p><strong>Address:</strong><br>
                    175, Sonagiri<br>
                    Bhopal, MP, India</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <a href="about.html" class="footer-link">About Program</a>
                    <a href="curriculum.html" class="footer-link">Curriculum</a>
                    <a href="instructors.html" class="footer-link">Instructors</a>
                    <a href="schedule.html" class="footer-link">Schedule</a>
                </div>
                <div class="footer-section">
                    <h4>Apply</h4>
                    <a href="apply.php" class="footer-link">Application Form</a>
                    <a href="downloadables/download.php?file=syllabus" class="footer-link">Download Syllabus</a>
                    <a href="contact.php" class="footer-link">Contact Us</a>
                </div>
                <div class="footer-section">
                    <h4>Connect</h4>
                    <a href="mailto:info@kala-klub.com" class="footer-link">info@kala-klub.com</a>
                    <a href="https://linkedin.com/in/aazsocial" class="footer-link" target="_blank" rel="noopener">LinkedIn - Gaurav</a>
                    <a href="https://linkedin.com/in/kaushikkishor" class="footer-link" target="_blank" rel="noopener">LinkedIn - Kaushik</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 TheGaurav.in. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>