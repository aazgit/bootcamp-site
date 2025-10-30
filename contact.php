<?php
/*
 * Kala-Klub Contact Form Handler
 * Secure PHP contact form with validation and email functionality
 */

// Configuration
$config = [
    'to_email' => 'info@kala-klub.com',
    'from_email' => 'noreply@kala-klub.com',
    'from_name' => 'Kala-Klub Contact Form',
    'rate_limit_minutes' => 3,
    'honeypot_field' => 'website'
];

$form_errors = [];
$form_data = [];
$success_message = '';

// Rate limiting function
function checkContactRateLimit($minutes = 3) {
    session_start();
    $now = time();
    $limit_key = 'last_contact_' . $_SERVER['REMOTE_ADDR'];
    
    if (isset($_SESSION[$limit_key])) {
        $time_diff = ($now - $_SESSION[$limit_key]) / 60;
        if ($time_diff < $minutes) {
            return false;
        }
    }
    
    $_SESSION[$limit_key] = $now;
    return true;
}

// Sanitize input
function sanitizeContactInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Process contact form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting
    if (!checkContactRateLimit($config['rate_limit_minutes'])) {
        $form_errors['general'] = 'Please wait before sending another message.';
    }
    
    // Honeypot check
    if (!empty($_POST[$config['honeypot_field']])) {
        // Likely spam, silently ignore
        $success_message = 'Thank you for your message. We\'ll be in touch soon!';
    } else {
        // Sanitize inputs
        $form_data = [
            'name' => sanitizeContactInput($_POST['name'] ?? ''),
            'email' => sanitizeContactInput($_POST['email'] ?? ''),
            'subject' => sanitizeContactInput($_POST['subject'] ?? ''),
            'message' => sanitizeContactInput($_POST['message'] ?? '')
        ];
        
        // Validation
        if (empty($form_data['name'])) {
            $form_errors['name'] = 'Name is required.';
        }
        
        if (empty($form_data['email'])) {
            $form_errors['email'] = 'Email is required.';
        } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
            $form_errors['email'] = 'Please enter a valid email address.';
        }
        
        if (empty($form_data['subject'])) {
            $form_errors['subject'] = 'Subject is required.';
        }
        
        if (empty($form_data['message'])) {
            $form_errors['message'] = 'Message is required.';
        } elseif (strlen($form_data['message']) < 10) {
            $form_errors['message'] = 'Please provide a more detailed message.';
        }
        
        // Send email if no errors
        if (empty($form_errors)) {
            $email_subject = 'Contact Form: ' . $form_data['subject'];
            $email_body = "
New contact form submission from Kala-Klub website

From: {$form_data['name']}
Email: {$form_data['email']}
Subject: {$form_data['subject']}

Message:
{$form_data['message']}

Submitted: " . date('Y-m-d H:i:s') . "
IP Address: {$_SERVER['REMOTE_ADDR']}
";
            
            $headers = [
                'From: ' . $config['from_name'] . ' <' . $config['from_email'] . '>',
                'Reply-To: ' . $form_data['email'],
                'Content-Type: text/plain; charset=UTF-8'
            ];
            
            if (mail($config['to_email'], $email_subject, $email_body, implode("\r\n", $headers))) {
                $success_message = 'Thank you for your message! We\'ll get back to you within 24 hours.';
                $form_data = []; // Clear form data on success
            } else {
                $form_errors['general'] = 'There was an error sending your message. Please try again.';
            }
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Contact Kala-Klub Figma EDU Bootcamp for questions about our design education program, admissions, or general inquiries. Located in Bhopal, MP.">
    <meta name="keywords" content="contact Kala-Klub, design bootcamp inquiries, Figma education contact, Bhopal design school">
    <meta name="author" content="Kala-Klub">
    <title>Contact Us - Kala-Klub Figma EDU Bootcamp</title>
    <link rel="canonical" href="https://kala-klub.com/contact.php">
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
                <li><a href="apply.php" class="nav-link">Apply</a></li>
                <li><a href="contact.php" class="nav-link active">Contact</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main id="main-content">
        <!-- Page Header -->
        <section class="hero" style="padding: 6rem 0 4rem;">
            <div class="container">
                <div class="hero-content fade-in">
                    <h1>Get in Touch</h1>
                    <p class="hero-subtitle">Have questions about our program? Need help with your application? We're here to help you start your design journey.</p>
                </div>
            </div>
        </section>

        <!-- Contact Information & Form -->
        <section class="section">
            <div class="container">
                <?php if ($success_message): ?>
                <div class="card glass-panel" style="background: rgba(46, 204, 113, 0.1); border-color: rgba(46, 204, 113, 0.3); margin-bottom: 2rem; text-align: center;">
                    <div style="font-size: 3rem; color: #27ae60; margin-bottom: 1rem;">✅</div>
                    <h3 style="color: #27ae60;">Message Sent Successfully!</h3>
                    <p style="color: #27ae60;"><?php echo htmlspecialchars($success_message); ?></p>
                </div>
                <?php endif; ?>

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
                    <!-- Contact Information -->
                    <div class="col-2">
                        <div class="card glass-panel">
                            <h2>Contact Information</h2>
                            
                            <div style="margin-bottom: 2rem;">
                                <h4 style="color: #3498db;">📍 Our Location</h4>
                                <p><strong>Kala-Klub Design Education</strong><br>
                                175, Sonagiri<br>
                                Bhopal, MP<br>
                                India</p>
                            </div>
                            
                            <div style="margin-bottom: 2rem;">
                                <h4 style="color: #3498db;">📧 Email Us</h4>
                                <p><strong>General Inquiries:</strong><br>
                                <a href="mailto:info@kala-klub.com" style="color: #3498db;">info@kala-klub.com</a></p>
                                
                                <p><strong>Admissions:</strong><br>
                                <a href="mailto:admissions@kala-klub.com" style="color: #3498db;">admissions@kala-klub.com</a></p>
                                
                                <p><strong>Instructor Contact:</strong><br>
                                <a href="mailto:gaurav@kala-klub.com" style="color: #3498db;">gaurav@kala-klub.com</a><br>
                                <a href="mailto:kaushikkishor@kala-klub.com" style="color: #3498db;">kaushikkishor@kala-klub.com</a></p>
                            </div>
                            
                            <div style="margin-bottom: 2rem;">
                                <h4 style="color: #3498db;">🔗 Connect With Us</h4>
                                <p><strong>LinkedIn:</strong><br>
                                <a href="https://linkedin.com/in/aazsocial" target="_blank" rel="noopener" style="color: #3498db;">Gaurav Kumar</a><br>
                                <a href="https://linkedin.com/in/kaushikkishor" target="_blank" rel="noopener" style="color: #3498db;">Kaushik Kishore</a></p>
                            </div>
                            
                            <div style="background: rgba(52, 152, 219, 0.1); padding: 1.5rem; border-radius: 8px;">
                                <h4 style="color: #2c3e50;">⏰ Response Time</h4>
                                <p style="margin: 0; font-size: 0.9rem;">We typically respond to all inquiries within 24 hours during business days.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Form -->
                    <div class="col-2">
                        <div class="card glass-panel">
                            <h2>Send Us a Message</h2>
                            
                            <form method="post" action="contact.php">
                                <!-- Honeypot field -->
                                <input type="text" name="<?php echo $config['honeypot_field']; ?>" style="display: none;" tabindex="-1" autocomplete="off">
                                
                                <div class="form-group">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" id="name" name="name" class="form-input" 
                                           value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>" 
                                           required maxlength="100">
                                </div>
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" id="email" name="email" class="form-input" 
                                           value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" 
                                           required maxlength="100">
                                </div>
                                
                                <div class="form-group">
                                    <label for="subject" class="form-label">Subject *</label>
                                    <select id="subject" name="subject" class="form-select" required>
                                        <option value="">Select a subject</option>
                                        <option value="General Inquiry" <?php echo ($form_data['subject'] ?? '') === 'General Inquiry' ? 'selected' : ''; ?>>General Inquiry</option>
                                        <option value="Program Information" <?php echo ($form_data['subject'] ?? '') === 'Program Information' ? 'selected' : ''; ?>>Program Information</option>
                                        <option value="Application Questions" <?php echo ($form_data['subject'] ?? '') === 'Application Questions' ? 'selected' : ''; ?>>Application Questions</option>
                                        <option value="Technical Support" <?php echo ($form_data['subject'] ?? '') === 'Technical Support' ? 'selected' : ''; ?>>Technical Support</option>
                                        <option value="Partnership/Collaboration" <?php echo ($form_data['subject'] ?? '') === 'Partnership/Collaboration' ? 'selected' : ''; ?>>Partnership/Collaboration</option>
                                        <option value="Media/Press Inquiry" <?php echo ($form_data['subject'] ?? '') === 'Media/Press Inquiry' ? 'selected' : ''; ?>>Media/Press Inquiry</option>
                                        <option value="Other" <?php echo ($form_data['subject'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="message" class="form-label">Message *</label>
                                    <textarea id="message" name="message" class="form-textarea" 
                                              required minlength="10" maxlength="1000" rows="6" 
                                              placeholder="Please describe your question or inquiry in detail..."><?php echo htmlspecialchars($form_data['message'] ?? ''); ?></textarea>
                                    <small style="color: #666; font-size: 0.9rem;">Minimum 10 characters</small>
                                </div>
                                
                                <div style="text-align: center; margin-top: 2rem;">
                                    <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem;">
                                        Send Message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Map Section -->
        <section class="section" style="background: rgba(52, 152, 219, 0.05);">
            <div class="container">
                <h2 style="text-align: center; margin-bottom: 3rem;">Visit Our Location</h2>
                
                <div class="card glass-panel">
                    <div class="row">
                        <div class="col-2">
                            <!-- Map Placeholder -->
                            <div style="background: linear-gradient(135deg, #3498db, #2980b9); border-radius: 8px; height: 300px; display: flex; align-items: center; justify-content: center; color: white; text-align: center;">
                                <div>
                                    <div style="font-size: 3rem; margin-bottom: 1rem;">🗺️</div>
                                    <h3 style="color: white;">Interactive Map</h3>
                                    <p style="margin: 0; opacity: 0.9;">175, Sonagiri, Bhopal, MP</p>
                                    <a href="https://maps.google.com/?q=175+Sonagiri+Bhopal+MP" 
                                       target="_blank" rel="noopener" 
                                       style="display: inline-block; margin-top: 1rem; padding: 0.5rem 1rem; background: rgba(255, 255, 255, 0.2); color: white; text-decoration: none; border-radius: 5px;">
                                        Open in Google Maps
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-2">
                            <h3>Getting Here</h3>
                            
                            <div style="margin-bottom: 1.5rem;">
                                <h4 style="color: #3498db;">🚗 By Car</h4>
                                <p>Free parking available on-site. Located in the heart of Sonagiri with easy access from major roads.</p>
                            </div>
                            
                            <div style="margin-bottom: 1.5rem;">
                                <h4 style="color: #3498db;">🚌 Public Transport</h4>
                                <p>Well connected by local bus routes. The nearest bus stop is just 200 meters away.</p>
                            </div>
                            
                            <div style="margin-bottom: 1.5rem;">
                                <h4 style="color: #3498db;">🚂 By Train</h4>
                                <p>Bhopal Junction is the nearest railway station, approximately 15 km from our location.</p>
                            </div>
                            
                            <div>
                                <h4 style="color: #3498db;">✈️ By Air</h4>
                                <p>Raja Bhoj Airport (BHO) is about 25 km away, with regular flights to major Indian cities.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="section">
            <div class="container">
                <h2 style="text-align: center; margin-bottom: 3rem;">Frequently Asked Questions</h2>
                
                <div class="row">
                    <div class="col-2">
                        <div class="card glass-panel">
                            <h4 style="color: #3498db;">How can I visit your campus?</h4>
                            <p>While most of our program is delivered online, we welcome visitors by appointment. Please contact us at least 24 hours in advance to schedule a visit.</p>
                            
                            <h4 style="color: #3498db; margin-top: 2rem;">Do you offer in-person sessions?</h4>
                            <p>Currently, our bootcamp is designed as a remote-first program. However, we occasionally host local meetups and workshops in Bhopal for nearby students.</p>
                            
                            <h4 style="color: #3498db; margin-top: 2rem;">What's the best way to reach you?</h4>
                            <p>Email is typically the fastest way to reach us. For urgent matters, you can also connect with our instructors directly via LinkedIn.</p>
                        </div>
                    </div>
                    
                    <div class="col-2">
                        <div class="card glass-panel">
                            <h4 style="color: #27ae60;">Can I speak with current students?</h4>
                            <p>We can connect prospective students with current students or recent graduates. Please mention this in your inquiry and we'll arrange a brief conversation.</p>
                            
                            <h4 style="color: #27ae60; margin-top: 2rem;">Do you provide career counseling?</h4>
                            <p>Yes! Career guidance is an integral part of our program. We offer one-on-one sessions, portfolio reviews, and job search assistance to all graduates.</p>
                            
                            <h4 style="color: #27ae60; margin-top: 2rem;">How do I report technical issues?</h4>
                            <p>For any technical problems with our website, application system, or learning platform, please email us with "Technical Support" as the subject line.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="section">
            <div class="container text-center">
                <div class="card glass-panel" style="max-width: 600px; margin: 0 auto;">
                    <h2>Ready to Start Your Journey?</h2>
                    <p>Don't wait—our Winter 2026 cohort is filling up quickly. Contact us today or submit your application to secure your spot.</p>
                    <div style="margin-top: 2rem;">
                        <a href="apply.php" class="btn btn-primary" style="margin-right: 1rem;">Apply Now</a>
                        <a href="downloadables/download.php?file=syllabus" class="btn btn-secondary">Download Syllabus</a>
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