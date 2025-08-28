<?php
// Admin Login Page
session_start();

// Check if admin is already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: /snapshop/admin/index.php');
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Show what we received
    error_log("POST data received: " . print_r($_POST, true));
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    error_log("Email: '$email', Password: '$password'");
    error_log("Email length: " . strlen($email) . ", Password length: " . strlen($password));
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
        error_log("Validation failed - Email empty: " . (empty($email) ? 'YES' : 'NO') . ", Password empty: " . (empty($password) ? 'YES' : 'NO'));
    } else {
        // Connect to database and verify credentials
        require_once '../../config/database.php';
        require_once '../../modal/admin.model.php';
        require_once 'admin-auth-helper.php';
        
        $conn = getDatabaseConnection();
        if ($conn) {
            $adminModel = new Admin($conn);
            
            // Debug: Log what we're trying
            error_log("Login attempt - Email: $email, Password: $password");
            
            // Try to authenticate with email
            $admin = $adminModel->verifyCredentials($email, $password);
            
            if ($admin) {
                error_log("Login successful for admin: " . $admin['username']);
                // Login successful - set admin session with token
                $token = AdminAuthHelper::setAdminSession($admin);
                $success = 'Login successful! Redirecting...';
                
                // Store token in localStorage and redirect
                echo "<script>
                    localStorage.setItem('admin_token', '" . $token . "');
                    localStorage.setItem('admin_login_time', '" . time() . "');
                    
                    setTimeout(function() {
                        window.location.href = '/snapshop/admin/index.php';
                    }, 1500);
                </script>";
            } else {
                error_log("Login failed - Invalid credentials for email: $email");
                $error = 'Invalid email or password';
                
                // Check what's in the database
                $check_sql = "SELECT id, username, email, is_active FROM admins WHERE email = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("s", $email);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    error_log("Admin found in DB: " . json_encode($row));
                } else {
                    error_log("No admin found with email: $email");
                }
            }
            
            $conn->close();
        } else {
            error_log("Database connection failed in admin login");
            $error = 'Database connection failed';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - SnapShop</title>
    <link rel="stylesheet" href="../../assets/css/tailwind.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Admin Login</h1>
                <p class="text-gray-600">Welcome back</p>
            </div>

            <!-- Success Message -->
            <?php if ($success): ?>
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm text-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" class="space-y-6">
                <!-- Email Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input 
                        type="email" 
                        name="email" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                        placeholder="Enter your email"
                        required
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    >
                </div>

                <!-- Password Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input 
                        type="password" 
                        name="password" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <!-- Login Button -->
                <button 
                    type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Sign In
                </button>
            </form>
        </div>
    </div>
</body>
</html>
