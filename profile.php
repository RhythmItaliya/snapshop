<?php
// User Profile Page - Complete profile management system

// Start session for user authentication
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: /snapshop/auth/login.php');
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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_profile') {
            try {
                $conn = getDatabaseConnection();
                if ($conn) {
                    $userModel = new User($conn);
                    
                    $updateData = [
                        'username' => $_POST['username'] ?? '',
                        'first_name' => $_POST['first_name'] ?? '',
                        'last_name' => $_POST['last_name'] ?? '',
                        'email' => $_POST['email'] ?? '',
                        'phone' => $_POST['phone'] ?? '',
                        'address' => [
                            'street_address' => $_POST['street_address'] ?? '',
                            'apartment' => $_POST['apartment'] ?? '',
                            'city' => $_POST['city'] ?? '',
                            'state' => $_POST['state'] ?? '',
                            'country' => $_POST['country'] ?? '',
                            'zip_code' => $_POST['zip_code'] ?? ''
                        ]
                    ];
                    
                    $result = $userModel->updateProfile($_SESSION['user_id'], $updateData);
                    if ($result) {
                        // Refresh user data
                        $user = $userModel->getUserById($_SESSION['user_id']);
                        echo "<script>if (typeof showToast === 'function') { showToast('Profile updated successfully!', 'success', 3000); }</script>";
                    } else {
                        $error = 'Failed to update profile';
                        echo "<script>if (typeof showToast === 'function') { showToast('Failed to update profile', 'error', 3000); }</script>";
                    }
                    $conn->close();
                }
            } catch (Exception $e) {
                $error = 'Error updating profile: ' . $e->getMessage();
                echo "<script>if (typeof showToast === 'function') { showToast('" . addslashes($e->getMessage()) . "', 'error', 4000); }</script>";
            }
        } elseif ($_POST['action'] === 'change_password') {
            try {
                $conn = getDatabaseConnection();
                if ($conn) {
                    $userModel = new User($conn);
                    
                    $currentPassword = $_POST['current_password'] ?? '';
                    $newPassword = $_POST['new_password'] ?? '';
                    $confirmPassword = $_POST['confirm_password'] ?? '';
                    
                    // Validation
                    if ($newPassword !== $confirmPassword) {
                        $error = 'New passwords do not match';
                    } elseif (strlen($newPassword) < 6) {
                        $error = 'New password must be at least 6 characters long';
                    } else {
                        $result = $userModel->changePassword($_SESSION['user_id'], $currentPassword, $newPassword);
                        if ($result) {
                            // Show success toast
                            echo "<script>if (typeof showToast === 'function') { showToast('Password updated successfully!', 'success', 3000); }</script>";
                        } else {
                            $error = 'Failed to update password. Please check your current password.';
                        }
                    }
                    $conn->close();
                }
            } catch (Exception $e) {
                $error = 'Error changing password: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - SnapShop</title>
    <link href="assets/css/tailwind.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Include Header -->
    <?php include 'component/header.php'; ?>
    
    <div class="pt-20">
        <?php if ($loading): ?>
            <!-- Loading State -->
            <div class="text-center py-20">
                <?php echo renderLoadingSpinner(['size' => 'lg', 'variant' => 'primary']); ?>
                <p class="mt-4 text-gray-600">Loading profile...</p>
            </div>
        <?php elseif ($error): ?>
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
                    $pageTitle = 'My Profile';
                    $pageDescription = 'Manage your account settings and preferences';
                    $showBackButton = false; // No back button on main profile page
                    
                    // Include common profile header
                    include 'component/profile-header.php';
                    ?>

                    <!-- Profile Content -->
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                        <!-- Profile Sidebar -->
                        <div class="lg:col-span-1">
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-24">
                                <div class="text-center">
                                    <h2 class="text-xl font-bold text-primary mb-2">
                                        <?php 
                                        if (!empty($user['first_name']) && !empty($user['last_name'])) {
                                            echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
                                        } elseif (!empty($user['username'])) {
                                            echo htmlspecialchars($user['username']);
                                        } else {
                                            echo 'Welcome!';
                                        }
                                        ?>
                                    </h2>
                                    <p class="text-neutral text-sm mb-4">
                                        <?php 
                                        if (!empty($user['email'])) {
                                            echo htmlspecialchars($user['email']);
                                        } else {
                                            echo 'Complete your profile';
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Form -->
                        <div class="lg:col-span-3">
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <?php 
                                // Check if user has incomplete profile
                                $hasIncompleteProfile = empty($user['first_name']) || empty($user['last_name']) || empty($user['phone']) || 
                                                     empty($user['address']['street_address']) || empty($user['address']['city']) || 
                                                     empty($user['address']['state']) || empty($user['address']['country']);
                                
                                if ($hasIncompleteProfile): ?>
                                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                        <div class="flex items-center">
                                            <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                                            <div>
                                                <p class="text-blue-800 font-medium">Complete Your Profile</p>
                                                <p class="text-blue-700 text-sm">Please fill in your personal and address information to complete your profile.</p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <form id="profileForm" method="POST" class="space-y-6">
                                    <input type="hidden" name="action" value="update_profile">
                                    
                                    <!-- Personal Information -->
                                    <div class="space-y-4">
                                        <h4 class="text-lg font-semibold text-blue-600 border-b border-gray-200 pb-2">
                                            Personal Information
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    <i class="fas fa-user mr-2 text-blue-500"></i>
                                                    First Name
                                                </label>
                                                <input type="text" name="first_name" 
                                                       value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>"
                                                       placeholder="Enter your first name"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    <i class="fas fa-user mr-2 text-blue-500"></i>
                                                    Last Name
                                                </label>
                                                <input type="text" name="last_name" 
                                                       value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>"
                                                       placeholder="Enter your last name"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    <i class="fas fa-user mr-2 text-blue-500"></i>
                                                    Username
                                                </label>
                                                <input type="text" name="username" 
                                                       value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>"
                                                       placeholder="Enter your username"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    <i class="fas fa-envelope mr-2 text-blue-500"></i>
                                                    Email Address
                                                </label>
                                                <input type="email" name="email" 
                                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                                                       placeholder="Enter your email address"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    <i class="fas fa-phone mr-2 text-blue-500"></i>
                                                    Phone Number
                                                </label>
                                                <input type="tel" name="phone" 
                                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                                       placeholder="Enter your phone number"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Address Information -->
                                    <div class="space-y-4">
                                        <h4 class="text-lg font-semibold text-blue-600 border-b border-gray-200 pb-2">
                                            Address Information
                                        </h4>

                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    <i class="fas fa-home mr-2 text-blue-500"></i>
                                                    Street Address
                                                </label>
                                                <input type="text" name="street_address" 
                                                       value="<?php echo htmlspecialchars($user['address']['street_address'] ?? ''); ?>"
                                                       placeholder="Enter your street address"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    <i class="fas fa-building mr-2 text-blue-500"></i>
                                                    Apartment, suite, etc. (optional)
                                                </label>
                                                <input type="text" name="apartment" 
                                                       value="<?php echo htmlspecialchars($user['address']['apartment'] ?? ''); ?>"
                                                       placeholder="Apartment, suite, etc. (optional)"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                                                    <input type="text" name="city" 
                                                           value="<?php echo htmlspecialchars($user['address']['city'] ?? ''); ?>"
                                                           placeholder="Enter your city"
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">State/Province</label>
                                                    <input type="text" name="state" 
                                                           value="<?php echo htmlspecialchars($user['address']['state'] ?? ''); ?>"
                                                           placeholder="Enter your state/province"
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                                        <i class="fas fa-globe mr-2 text-blue-500"></i>
                                                        Country
                                                    </label>
                                                    <input type="text" name="country" 
                                                           value="<?php echo htmlspecialchars($user['address']['country'] ?? ''); ?>"
                                                           placeholder="Enter your country"
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">ZIP/Postal Code</label>
                                                <input type="text" name="zip_code" 
                                                       value="<?php echo htmlspecialchars($user['address']['zip_code'] ?? ''); ?>"
                                                       placeholder="Enter your ZIP/postal code"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Save Button -->
                                    <div class="pt-6 border-t border-gray-200">
                                        <button type="submit" 
                                                class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                                            <i class="fas fa-save mr-2"></i>
                                            Save Profile
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
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

    <script>
        // Profile editing functions
        // The edit mode toggle functionality is removed, so these functions are no longer needed.
        // The form fields are now directly editable.

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing profile page...');
            
            // Add event listeners for profile editing buttons
            // No specific buttons to listen for as fields are directly editable.
            
            console.log('Profile page initialization complete');
        });
    </script>
</body>
</html>
