<?php
// Web-based test for session handling
// This should be accessed via browser, not CLI

require_once __DIR__ . '/auth/auth-helper.php';

// Test multiple calls to startSessionIfNotStarted
startSessionIfNotStarted();
startSessionIfNotStarted();
startSessionIfNotStarted();

// Set some session data to verify it works
$_SESSION['test_data'] = 'Session is working correctly!';
$_SESSION['test_time'] = time();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Session Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { color: green; }
        .info { color: blue; }
    </style>
</head>
<body>
    <h1>Session Test Results</h1>
    
    <div class="success">
        <h2>✅ Success!</h2>
        <p>If you can see this page without any PHP notices about "session already active", then the fix is working correctly!</p>
    </div>
    
    <div class="info">
        <h3>Session Data:</h3>
        <p><strong>Test Data:</strong> <?php echo $_SESSION['test_data'] ?? 'Not set'; ?></p>
        <p><strong>Test Time:</strong> <?php echo isset($_SESSION['test_time']) ? date('Y-m-d H:i:s', $_SESSION['test_time']) : 'Not set'; ?></p>
        <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
    </div>
    
    <p><em>Note: Check your browser's developer console and server error logs for any PHP notices.</em></p>
</body>
</html>
