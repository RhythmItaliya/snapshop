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

// Initialize variables
$user = null;
$loading = true;
$error = null;
$success = '';
$activeTab = $_GET['tab'] ?? 'profile';

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
                        $success = 'Profile updated successfully!';
                        // Refresh user data
                        $user = $userModel->getUserById($_SESSION['user_id']);
                    } else {
                        $error = 'Failed to update profile';
                    }
                    $conn->close();
                }
            } catch (Exception $e) {
                $error = 'Error updating profile: ' . $e->getMessage();
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
                            $success = 'Password updated successfully!';
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
            <div class="min-h-screen bg-gray-50 flex items-center justify-center">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="mt-4 text-gray-600 text-lg">Loading profile...</p>
                </div>
            </div>
        <?php elseif ($error): ?>
            <div class="min-h-screen bg-gray-50 flex items-center justify-center">
                <div class="text-center">
                    <div class="text-red-500 text-6xl mb-4">⚠️</div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Error</h2>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($error); ?></p>
                    <a href="/snapshop/" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        Back to Home
                    </a>
                </div>
            </div>
        <?php elseif ($user): ?>
            <div class="container mx-auto px-4 py-6">
                <div class="max-w-5xl mx-auto">
                    <!-- Profile Header -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-blue-600">My Profile</h1>
                                <p class="text-gray-600 text-sm mt-1">
                                    Manage your account settings and preferences
                                </p>
                            </div>
                            <button onclick="handleLogout()" 
                                    class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors flex items-center space-x-2">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Sign Out</span>
                            </button>
                        </div>
                    </div>

                    <!-- Tab Navigation -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                        <div class="flex space-x-1">
                            <button onclick="setActiveTab('profile')" 
                                    class="tab-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $activeTab === 'profile' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100'; ?>">
                                <i class="fas fa-user mr-2"></i>
                                Profile
                            </button>
                            <button onclick="setActiveTab('orders')" 
                                    class="tab-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $activeTab === 'orders' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100'; ?>">
                                <i class="fas fa-shopping-bag mr-2"></i>
                                My Orders
                            </button>
                            <button onclick="setActiveTab('password')" 
                                    class="tab-btn px-4 py-2 rounded-lg text-sm font-medium transition-colors <?php echo $activeTab === 'password' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100'; ?>">
                                <i class="fas fa-lock mr-2"></i>
                                Change Password
                            </button>
                        </div>
                    </div>

                    <!-- Success/Error Messages -->
                    <?php if ($success): ?>
                        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Profile Tab -->
                    <div id="profileTab" class="tab-content <?php echo $activeTab === 'profile' ? '' : 'hidden'; ?>">
                        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                            <!-- Profile Sidebar -->
                            <div class="lg:col-span-1">
                                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-24">
                                    <div class="text-center">
                                        <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-blue-600 flex items-center justify-center text-white text-3xl font-bold">
                                            <?php echo strtoupper(substr($user['first_name'] ?? $user['username'] ?? 'U', 0, 1)); ?>
                                        </div>
                                        <h2 class="text-xl font-bold text-blue-600 mb-2">
                                            <?php 
                                            if (!empty($user['first_name']) && !empty($user['last_name'])) {
                                                echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
                                            } else {
                                                echo htmlspecialchars($user['username'] ?? 'User');
                                            }
                                            ?>
                                        </h2>
                                        <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars($user['email'] ?? 'user@example.com'); ?></p>

                                        <button onclick="toggleEditMode()" 
                                                class="edit-profile-btn bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors w-full">
                                            <i class="fas fa-edit mr-2"></i>
                                            Edit Profile
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Profile Form -->
                            <div class="lg:col-span-3">
                                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                    <div class="flex items-center justify-between mb-6">
                                        <h3 class="text-xl font-bold text-blue-600">Profile Information</h3>
                                        <div id="editActions" class="hidden flex space-x-3">
                                            <button onclick="cancelEdit()" 
                                                    class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                                                Cancel
                                            </button>
                                            <button onclick="saveProfile()" 
                                                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                                Save Changes
                                            </button>
                                        </div>
                                    </div>

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
                                                           class="profile-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                           disabled>
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                                        <i class="fas fa-user mr-2 text-blue-500"></i>
                                                        Last Name
                                                    </label>
                                                    <input type="text" name="last_name" 
                                                           value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>"
                                                           class="profile-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                           disabled>
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                                        <i class="fas fa-user mr-2 text-blue-500"></i>
                                                        Username
                                                    </label>
                                                    <input type="text" name="username" 
                                                           value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>"
                                                           class="profile-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                           disabled>
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                                        <i class="fas fa-envelope mr-2 text-blue-500"></i>
                                                        Email Address
                                                    </label>
                                                    <input type="email" name="email" 
                                                           value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                                                           class="profile-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                           disabled>
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                                        <i class="fas fa-phone mr-2 text-blue-500"></i>
                                                        Phone Number
                                                    </label>
                                                    <input type="tel" name="phone" 
                                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                                           class="profile-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                           disabled>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Address Information -->
                                        <div class="space-y-4">
                                            <h4 class="text-lg font-semibold text-blue-600 border-b border-gray-200 pb-2">
                                                <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
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
                                                           class="profile-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                           disabled>
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                                        <i class="fas fa-building mr-2 text-blue-500"></i>
                                                        Apartment, suite, etc. (optional)
                                                    </label>
                                                    <input type="text" name="apartment" 
                                                           value="<?php echo htmlspecialchars($user['address']['apartment'] ?? ''); ?>"
                                                           class="profile-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                           disabled>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                                                        <input type="text" name="city" 
                                                               value="<?php echo htmlspecialchars($user['address']['city'] ?? ''); ?>"
                                                               class="profile-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                               disabled>
                                                    </div>

                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">State/Province</label>
                                                        <input type="text" name="state" 
                                                               value="<?php echo htmlspecialchars($user['address']['state'] ?? ''); ?>"
                                                               class="profile-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                               disabled>
                                                    </div>

                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                                            <i class="fas fa-globe mr-2 text-blue-500"></i>
                                                            Country
                                                        </label>
                                                        <input type="text" name="country" 
                                                               value="<?php echo htmlspecialchars($user['address']['country'] ?? ''); ?>"
                                                               class="profile-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                               disabled>
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">ZIP/Postal Code</label>
                                                    <input type="text" name="zip_code" 
                                                           value="<?php echo htmlspecialchars($user['address']['zip_code'] ?? ''); ?>"
                                                           class="profile-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                           disabled>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Password Tab -->
                    <div id="passwordTab" class="tab-content <?php echo $activeTab === 'password' ? '' : 'hidden'; ?>">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xl font-bold text-blue-600">Change Password</h3>
                            </div>

                            <form method="POST" class="space-y-6 max-w-md">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                    <input type="password" name="current_password" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Enter your current password">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                    <input type="password" name="new_password" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Enter your new password">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                    <input type="password" name="confirm_password" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Confirm your new password">
                                </div>

                                <div class="pt-4">
                                    <button type="submit" 
                                            class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Orders Tab -->
                    <div id="ordersTab" class="tab-content <?php echo $activeTab === 'orders' ? '' : 'hidden'; ?>">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-xl font-bold text-blue-600 mb-6">My Orders</h3>
                            <div class="text-center py-12">
                                <i class="fas fa-shopping-bag text-4xl text-gray-400 mb-4"></i>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Order History Coming Soon</h3>
                                <p class="text-gray-600">We're working on bringing you a complete order history feature.</p>
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

    <script>
        // Tab functionality
        function setActiveTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });
            
            // Remove active state from all tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('text-gray-600', 'hover:bg-gray-100');
            });
            
            // Show selected tab content
            document.getElementById(tabName + 'Tab').classList.remove('hidden');
            
            // Add active state to selected tab button
            event.target.classList.remove('text-gray-600', 'hover:bg-gray-100');
            event.target.classList.add('bg-blue-600', 'text-white');
            
            // Update URL without page reload
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.pushState({}, '', url);
        }

        // Profile editing functionality
        function toggleEditMode() {
            const inputs = document.querySelectorAll('.profile-input');
            const editBtn = document.querySelector('.edit-profile-btn');
            const editActions = document.getElementById('editActions');
            
            inputs.forEach(input => {
                input.disabled = !input.disabled;
                if (!input.disabled) {
                    input.classList.add('bg-white');
                } else {
                    input.classList.remove('bg-white');
                }
            });
            
            editBtn.classList.toggle('hidden');
            editActions.classList.toggle('hidden');
        }

        function cancelEdit() {
            // Reset form to original values
            location.reload();
        }

        function saveProfile() {
            document.getElementById('profileForm').submit();
        }

        // Logout functionality
        function handleLogout() {
            if (confirm('Are you sure you want to logout?')) {
                fetch('/snapshop/auth/logout.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Clear localStorage
                            localStorage.removeItem('token');
                            localStorage.removeItem('user_id');
                            localStorage.removeItem('username');
                            localStorage.removeItem('email');
                            
                            // Show success message
                            if (typeof showToast === 'function') {
                                showToast('Logged out successfully', 'success', 3000);
                            }
                            
                            // Redirect to homepage
                            setTimeout(() => {
                                window.location.href = '/snapshop/';
                            }, 1000);
                        }
                    })
                    .catch(error => {
                        console.error('Logout error:', error);
                        // Force logout by clearing localStorage and redirecting
                        localStorage.clear();
                        window.location.href = '/snapshop/';
                    });
            }
        }

        // Initialize active tab from URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab') || 'profile';
            setActiveTab(activeTab);
        });
    </script>
</body>
</html>
