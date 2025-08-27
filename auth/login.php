<?php
// Login Modal Component - to be included in home page
session_start();

// Handle form submission
$error = '';
$success = '';
$loading = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_form'])) {
    $loading = true;
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($username)) {
        $error = 'Username is required';
    } elseif (empty($password)) {
        $error = 'Password is required';
    } else {
        try {
            // Include database connection
            require_once __DIR__ . '/../config/database.php';
            require_once __DIR__ . '/../modal/user.model.php';
            
            $conn = getDatabaseConnection();
            
            if ($conn) {
                $userModel = new User($conn);
                
                // Attempt to authenticate user
                $user = $userModel->authenticateUser($username, $password);
                
                if ($user) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'] ?? '';
                    
                    $success = 'Login successful! Welcome back!';
                    
                    // Redirect after a short delay
                    header("Refresh: 2; url=/snapshop/");
                } else {
                    $error = 'Invalid username or password';
                }
                
                $conn->close();
            } else {
                $error = 'Database connection failed';
            }
        } catch (Exception $e) {
            $error = 'An error occurred during login';
        }
    }
    
    $loading = false;
}
?>

<!-- Login Modal -->
<div id="loginModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-8 rounded-xl shadow-2xl max-w-md w-full relative mx-4">
            <!-- Close Button -->
            <button
                type="button"
                onclick="closeLoginModal()"
                class="absolute top-5 right-5 text-gray-500 hover:text-gray-700 transition-colors"
            >
                <i class="fas fa-times text-xl"></i>
            </button>
            
            <!-- Header -->
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Login</h2>
            
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
            
            <!-- Login Form -->
            <form method="POST" class="space-y-6">
                <input type="hidden" name="login_form" value="1">
                <!-- Username Field -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        Username
                    </label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        required
                        disabled="<?php echo $loading ? 'disabled' : ''; ?>"
                        autocomplete="username"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors <?php echo $loading ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'; ?>"
                        placeholder="Enter your username"
                    />
                </div>
                
                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            disabled="<?php echo $loading ? 'disabled' : ''; ?>"
                            autocomplete="current-password"
                            class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors <?php echo $loading ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'; ?>"
                            placeholder="Enter your password"
                        />
                        <button
                            type="button"
                            onclick="togglePasswordVisibility()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors"
                            disabled="<?php echo $loading ? 'disabled' : ''; ?>"
                        >
                            <i class="fas fa-eye" id="passwordIcon"></i>
                        </button>
                    </div>
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
                            Logging in...
                        <?php else: ?>
                            Login
                        <?php endif; ?>
                    </button>
                </div>
                
                <!-- Register Link -->
                <div class="text-sm font-medium text-gray-600 text-center">
                    Not registered? 
                    <button
                        type="button"
                        onclick="switchToRegister()"
                        class="text-blue-600 hover:text-blue-700 transition-colors"
                    >
                        Create account
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Login Modal Functions
        function openLoginModal() {
            document.getElementById('loginModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeLoginModal() {
            document.getElementById('loginModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        function switchToRegister() {
            closeLoginModal();
            openRegisterModal();
        }
        
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('passwordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'fas fa-eye';
            }
        }
        
        // Close modal on background click
        document.addEventListener('click', function(e) {
            if (e.target.id === 'loginModal') {
                closeLoginModal();
            }
        });
        
        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLoginModal();
            }
        });
    </script>
