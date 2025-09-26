<?php
require __DIR__.'/includes/auth.php';

echo "<h1>Debug Test Email</h1>";

// Check if Email class exists
echo "<h2>Email Class Check</h2>";
$email_path1 = __DIR__ . '/lib/Email.php';
$email_path2 = __DIR__ . '/../lib/Email.php';
echo "<p>Email.php path 1: $email_path1 - " . (file_exists($email_path1) ? 'EXISTS' : 'NOT FOUND') . "</p>";
echo "<p>Email.php path 2: $email_path2 - " . (file_exists($email_path2) ? 'EXISTS' : 'NOT FOUND') . "</p>";

$email_loaded = false;
if (file_exists($email_path1)) {
    try {
        require_once $email_path1;
        echo "<p>✅ Loaded from: $email_path1</p>";
        $email_loaded = true;
    } catch (Exception $e) {
        echo "<p>❌ Error loading from path 1: " . $e->getMessage() . "</p>";
    }
} elseif (file_exists($email_path2)) {
    try {
        require_once $email_path2;
        echo "<p>✅ Loaded from: $email_path2</p>";
        $email_loaded = true;
    } catch (Exception $e) {
        echo "<p>❌ Error loading from path 2: " . $e->getMessage() . "</p>";
    }
}

if ($email_loaded) {
    try {
        echo "<p>Email.php loaded successfully</p>";
        
        $email = new Email();
        echo "<p>Email object created successfully</p>";
        
        // Test basic email sending
        echo "<h2>Test Email Send</h2>";
        $result = $email->send_smtp_email('test@example.com', 'Test Subject', 'Test Body');
        echo "<p>Email send result: " . ($result ? 'SUCCESS' : 'FAILED') . "</p>";
        
    } catch (Exception $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ Email.php could not be loaded from any path</p>";
}

// Check POST data if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>POST Data Received</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Test the actual email sending process
    if (isset($_POST['action']) && $_POST['action'] === 'test_email') {
        echo "<h2>Testing Email Send Process</h2>";
        try {
            $location = $_POST['location'] ?? '';
            $test_email = $_POST['test_email'] ?? '';
            
            echo "<p>Location: " . htmlspecialchars($location) . "</p>";
            echo "<p>Email: " . htmlspecialchars($test_email) . "</p>";
            
            if (empty($test_email)) {
                throw new Exception("Email is required");
            }
            
            if (!filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }
            
            // Load Email class
            if (file_exists(__DIR__ . '/lib/Email.php')) {
                require_once __DIR__ . '/lib/Email.php';
                echo "<p>✅ Email class loaded</p>";
            } else {
                throw new Exception("Email class not found");
            }
            
            $email = new Email();
            echo "<p>✅ Email object created</p>";
            
            $subject = "Test Email - Portal Settings";
            $body = "<h1>Test Email</h1><p>This is a test email from the portal settings page.</p><p>Location: $location</p>";
            
            echo "<p>Attempting to send email...</p>";
            $result = $email->send_smtp_email($test_email, $subject, $body);
            
            if ($result) {
                echo "<p style='color: green;'>✅ EMAIL SENT SUCCESSFULLY!</p>";
            } else {
                echo "<p style='color: red;'>❌ EMAIL FAILED TO SEND</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}

// Simple test form
echo "<h2>Test Form</h2>";
?>
<form method="POST">
    <input type="hidden" name="action" value="test_email">
    <p>Location: <input type="text" name="location" value="Test Location"></p>
    <p>Email: <input type="email" name="test_email" value="your-email@example.com"></p>
    <button type="submit">Test Submit</button>
</form>
