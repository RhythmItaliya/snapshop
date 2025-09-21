<?php
// Change Password Page

// Start session for user authentication
require_once __DIR__ . '/auth/auth-helper.php';
startSessionIfNotStarted();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: /snapshop/');
    exit;
}

// Include necessary files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/modal/user.model.php';
require_once __DIR__ . '/component/ui/loading-spinner.php';
require_once __DIR__ . '/component/ui/error-state.php';
require_once __DIR__ . '/component/ui/input.php';
require_once __DIR__ . '/component/ui/button.php';
require_once __DIR__ . '/component/ui/toast.php';

// Initialize variables
$user = null;
$loading = true;
$error = null;
$success = null;

// Fetch user data
try {
    $conn = getDatabaseConnection();
    if ($conn) {
        $userModel = new User($conn);
        $user = $userModel->getUserById($_SESSION['user_id']);
        $loading = false;
        $conn->close();
    } else {
        throw new Exception("Database connection failed");
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    $loading = false;
}

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = getDatabaseConnection();
        if ($conn) {
            $userModel = new User($conn);
            
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validation
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $error = 'All fields are required';
                echo "<script>if (typeof showToast === 'function') { showToast('All fields are required', 'error', 3000); }</script>";
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'New passwords do not match';
                echo "<script>if (typeof showToast === 'function') { showToast('New passwords do not match', 'error', 3000); }</script>";
            } elseif (strlen($newPassword) < 6) {
                $error = 'New password must be at least 6 characters long';
                echo "<script>if (typeof showToast === 'function') { showToast('New password must be at least 6 characters long', 'error', 3000); }</script>";
            } else {
                $result = $userModel->changePassword($_SESSION['user_id'], $currentPassword, $newPassword);
                if ($result) {
                    $success = 'Password updated successfully!';
                    // Clear form
                    $_POST = array();
                    echo "<script>if (typeof showToast === 'function') { showToast('Password updated successfully!', 'success', 3000); }</script>";
                } else {
                    $error = 'Failed to update password. Please check your current password.';
                    echo "<script>if (typeof showToast === 'function') { showToast('Failed to update password. Please check your current password.', 'error', 4000); }</script>";
                }
            }
            $conn->close();
        }
    } catch (Exception $e) {
        $error = 'Error changing password: ' . $e->getMessage();
        echo "<script>if (typeof showToast === 'function') { showToast('" . addslashes($e->getMessage()) . "', 'error', 4000); }</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - SnapShop</title>
    <link href="assets/css/tailwind.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Include Header -->
    <?php include 'component/header.php'; ?>
    
    <div class="pt-20">
        <?php if ($loading): ?>
            <!-- Loading State -->
            <div class="text-center py-20">
                <?php echo renderLoadingSpinner(['size' => 'lg', 'variant' => 'primary']); ?>
                <p class="mt-4 text-gray-600">Loading...</p>
            </div>
        <?php elseif ($error && !$user): ?>
            <!-- Error State -->
            <div class="text-center py-20">
                <?php echo renderErrorState([
                    'error' => $error,
                    'onRetry' => 'window.location.reload()'
                ]); ?>
            </div>
        <?php elseif ($user): ?>
            <div class="container mx-auto px-4 py-6">
                <div class="max-w-5xl mx-auto">
                    <?php 
                    // Set page variables for common header
                    $pageTitle = 'Change Password';
                    $pageDescription = 'Update your account password to keep it secure';
                    $showBackButton = true;
                    $backUrl = '/snapshop/profile.php';
                    $backText = 'Back to Profile';
                    
                    // Include common profile header
                    include 'component/profile-header.php';
                    ?>

                    <!-- Change Password Form -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <form method="POST" class="space-y-6 max-w-md">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-lock mr-2 text-blue-500"></i>
                                    Current Password
                                </label>
                                <input type="password" name="current_password" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Enter your current password"
                                       value="<?php echo htmlspecialchars($_POST['current_password'] ?? ''); ?>">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-key mr-2 text-blue-500"></i>
                                    New Password
                                </label>
                                <input type="password" name="new_password" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Enter your new password"
                                       value="<?php echo htmlspecialchars($_POST['new_password'] ?? ''); ?>">
                                <p class="text-sm text-gray-500 mt-1">Password must be at least 6 characters long</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-check mr-2 text-blue-500"></i>
                                    Confirm New Password
                                </label>
                                <input type="password" name="confirm_password" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Confirm your new password"
                                       value="<?php echo htmlspecialchars($_POST['confirm_password'] ?? ''); ?>">
                            </div>

                            <div class="pt-4">
                                <button type="submit" 
                                        class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                                    <i class="fas fa-save mr-2"></i>
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Include Footer -->
    <?php include 'component/footer.php'; ?>

    <!-- Include Toast Component -->
    <?php include 'component/ui/toast.php'; ?>

    <!-- Include Auth Modals -->
    <?php include 'auth/login.php'; ?>
    <?php include 'auth/register.php'; ?>
</body>
</html>
