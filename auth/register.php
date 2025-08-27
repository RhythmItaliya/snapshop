<?php
// Register Modal Component - to be included in home page
session_start();

// Handle form submission
$error = '';
$success = '';
$loading = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_form'])) {
    $loading = true;
    
    $username = trim($_POST['username'] ?? '');
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($username)) {
        $error = 'Username is required';
    } elseif (empty($firstName)) {
        $error = 'First name is required';
    } elseif (empty($lastName)) {
        $error = 'Last name is required';
    } elseif (empty($email)) {
        $error = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (empty($password)) {
        $error = 'Password is required';
    } elseif (strlen($password) < 5) {
        $error = 'Password must be at least 5 characters long';
    } else {
        try {
            // Include database connection
            require_once __DIR__ . '/../config/database.php';
            require_once __DIR__ . '/../modal/user.model.php';
            
            $conn = getDatabaseConnection();
            
            if ($conn) {
                $userModel = new User($conn);
                
                // Check if username already exists
                if ($userModel->userExists($username)) {
                    $error = 'Username already exists. Please choose a different one.';
                } elseif ($userModel->emailExists($email)) {
                    $error = 'Email already registered. Please use a different email.';
                } else {
                    // Create new user
                    $userData = [
                        'username' => $username,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'password' => password_hash($password, PASSWORD_DEFAULT)
                    ];
                    
                    if ($userModel->createUser($userData)) {
                        $success = 'Registration successful! You can now login.';
                        
                        // Clear form data after successful registration
                        $username = $firstName = $lastName = $email = '';
                    } else {
                        $error = 'Registration failed. Please try again.';
                    }
                }
                
                $conn->close();
            } else {
                $error = 'Database connection failed';
            }
        } catch (Exception $e) {
            $error = 'An error occurred during registration';
        }
    }
    
    $loading = false;
}
?>

<!-- Register Modal -->
<div id="registerModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-8 rounded-xl shadow-2xl max-w-md w-full relative mx-4">
            <!-- Close Button -->
            <button
                type="button"
                onclick="closeRegisterModal()"
                class="absolute top-5 right-5 text-gray-500 hover:text-gray-700 transition-colors"
            >
                <i class="fas fa-times text-xl"></i>
            </button>
            
            <!-- Header -->
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Register</h2>
            
            <!-- Success/Error Messages -->
            <?php if (!empty($success)): ?>
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <!-- Register Form -->
            <form method="POST" class="space-y-6">
                <input type="hidden" name="register_form" value="1">
                <!-- Username Field -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        Username
                    </label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?php echo htmlspecialchars($username ?? ''); ?>"
                        required
                        disabled="<?php echo $loading ? 'disabled' : ''; ?>"
                        autocomplete="username"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors <?php echo $loading ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'; ?>"
                        placeholder="Enter your username"
                    />
                </div>
                
                <!-- First Name Field -->
                <div>
                    <label for="firstName" class="block text-sm font-medium text-gray-700 mb-2">
                        First Name
                    </label>
                    <input
                        type="text"
                        id="firstName"
                        name="firstName"
                        value="<?php echo htmlspecialchars($firstName ?? ''); ?>"
                        required
                        disabled="<?php echo $loading ? 'disabled' : ''; ?>"
                        autocomplete="given-name"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors <?php echo $loading ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'; ?>"
                        placeholder="Enter your first name"
                    />
                </div>
                
                <!-- Last Name Field -->
                <div>
                    <label for="lastName" class="block text-sm font-medium text-gray-700 mb-2">
                        Last Name
                    </label>
                    <input
                        type="text"
                        id="lastName"
                        name="lastName"
                        value="<?php echo htmlspecialchars($lastName ?? ''); ?>"
                        required
                        disabled="<?php echo $loading ? 'disabled' : ''; ?>"
                        autocomplete="family-name"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors <?php echo $loading ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'; ?>"
                        placeholder="Enter your last name"
                    />
                </div>
                
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo htmlspecialchars($email ?? ''); ?>"
                        required
                        disabled="<?php echo $loading ? 'disabled' : ''; ?>"
                        autocomplete="email"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors <?php echo $loading ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'; ?>"
                        placeholder="Enter your email address"
                    />
                </div>
                
                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        disabled="<?php echo $loading ? 'disabled' : ''; ?>"
                        autocomplete="new-password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors <?php echo $loading ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'; ?>"
                        placeholder="Enter your password (min 5 characters)"
                    />
                </div>
                
                <!-- Submit Button -->
                <div class="flex items-center justify-between">
                    <button
                        type="submit"
                        class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center justify-center"
                        disabled="<?php echo $loading ? 'disabled' : ''; ?>"
                    >
                        <?php if ($loading): ?>
                            <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
                            Creating Account...
                        <?php else: ?>
                            Register
                        <?php endif; ?>
                    </button>
                </div>
                
                <!-- Login Link -->
                <div class="text-sm font-medium text-gray-600 text-center">
                    Already registered? 
                    <button
                        type="button"
                        onclick="switchToLogin()"
                        class="text-blue-600 hover:text-blue-700 transition-colors"
                    >
                        Login to your account
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Register Modal Functions
        function openRegisterModal() {
            document.getElementById('registerModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeRegisterModal() {
            document.getElementById('registerModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        function switchToLogin() {
            closeRegisterModal();
            openLoginModal();
        }
        
        // Close modal on background click
        document.addEventListener('click', function(e) {
            if (e.target.id === 'registerModal') {
                closeRegisterModal();
            }
        });
        
        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRegisterModal();
            }
        });
    </script>
