<?php
require __DIR__.'/includes/auth.php';

echo "<h1>Debug Test Email</h1>";

// Check if Email class exists
echo "<h2>Email Class Check</h2>";
$email_path = __DIR__ . '/../lib/Email.php';
echo "<p>Email.php path: $email_path</p>";
echo "<p>Email.php exists: " . (file_exists($email_path) ? 'Yes' : 'No') . "</p>";

if (file_exists($email_path)) {
    try {
        require_once $email_path;
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
}

// Check POST data if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>POST Data Received</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
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
