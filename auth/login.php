<?php
// Login Modal Component - to be included in home page
require_once __DIR__ . '/auth-helper.php';
startSessionIfNotStarted();

// Include UI components
require_once __DIR__ . '/../component/ui/input.php';
require_once __DIR__ . '/../component/ui/button.php';

// Handle form submission
$error = '';
$success = '';
$loading = false;

// Only process form submission if it's a POST request with login_form
// Also check if it's an AJAX request to prevent unnecessary processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_form']) && !empty($_POST['username'])) {
    $loading = true;
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($username)) {
        echo json_encode(['success' => false, 'message' => 'Username is required']);
        exit;
    } elseif (empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Password is required']);
        exit;
    } else {
        try {
            // Include database connection
            require_once __DIR__ . '/../config/database.php';
            require_once __DIR__ . '/../modal/user.model.php';
            
            $conn = getDatabaseConnection();
            
            if ($conn) {
                $userModel = new User($conn);
                
                // Debug: Check if user exists first
                $userExists = $userModel->usernameExists($username);
                
                // Attempt to authenticate user
                $user = $userModel->verifyCredentials($username, $password);
        
                        if ($user) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'] ?? '';
                    
                    // Generate a simple token (in production, use proper JWT)
                    $token = bin2hex(random_bytes(32));
                    $_SESSION['token'] = $token;
                    
                    // Return success response for JavaScript handling
                    echo json_encode(['success' => true, 'token' => $token, 'user' => $user]);
                    exit;
                } else {
                    // Return error response
                    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
                    exit;
                }
                
                $conn->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Database connection failed']);
                exit;
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'An error occurred during login']);
            exit;
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
            <h2 class="text-2xl font-bold text-primary mb-6">Login</h2>
        
            <!-- Login Form -->
            <form id="loginForm" class="space-y-6">
                <input type="hidden" name="login_form" value="1">
                <!-- Username Field -->
                <div>
                    <?php echo renderInput([
                        'label' => 'Username',
                        'type' => 'text',
                        'name' => 'username',
                        'value' => htmlspecialchars($_POST['username'] ?? ''),
                        'required' => true,
                        'disabled' => $loading,
                        'placeholder' => 'Enter your username'
                    ]); ?>
                </div>
                
                <!-- Password Field -->
                <div>
                    <?php echo renderInput([
                        'label' => 'Password',
                        'type' => 'password',
                        'name' => 'password',
                        'required' => true,
                        'disabled' => $loading,
                        'placeholder' => 'Enter your password',
                        'iconRight' => '<button type="button" id="passwordToggle" onclick="togglePasswordVisibility()" class="text-neutral hover:text-primary transition-colors" ' . ($loading ? 'disabled' : '') . '>Show</button>'
                    ]); ?>
                </div>
                
                <!-- Submit Button -->
                <div class="flex items-center justify-between">
                    <?php echo renderButton([
                        'type' => 'submit',
                        'variant' => 'primary',
                        'size' => 'lg',
                        'className' => 'w-full',
                        'disabled' => $loading,
                        'loading' => $loading,
                        'children' => $loading ? 'Logging in...' : 'Login',
                        'id' => 'loginSubmitBtn'
                    ]); ?>
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
            const passwordInput = document.querySelector('#loginForm input[name="password"]');
            const passwordToggle = document.getElementById('passwordToggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                passwordToggle.textContent = 'Show';
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
        
        // Handle login form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const username = formData.get('username');
            const password = formData.get('password');
            
            // Show loading state
            const submitBtn = document.getElementById('loginSubmitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>Logging in...';
            submitBtn.disabled = true;
            
            // Send login request
            fetch('/snapshop/auth/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `login_form=1&username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Store token in localStorage
                    localStorage.setItem('token', data.token);
                    localStorage.setItem('user_id', data.user.id);
                    localStorage.setItem('username', data.user.username);
                    localStorage.setItem('email', data.user.email || '');
                    
                    // Show success message
                    if (typeof showToast === 'function') {
                        showToast('Login successful! Welcome back!', 'success', 3000);
                    }
                    
                    // Close modal and refresh page to update header
                    setTimeout(() => {
                        closeLoginModal();
                        window.location.reload();
                    }, 1000);
                } else {
                    // Show error message
                    if (typeof showToast === 'function') {
                        showToast(data.message || 'Login failed', 'error', 5000);
                    }
                    
                    // Reset button
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Login error:', error);
                if (typeof showToast === 'function') {
                    showToast('An error occurred during login', 'error', 5000);
                }
                
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    </script>
