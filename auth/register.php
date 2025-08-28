<?php
// Register Modal Component - to be included in home page
session_start();

// Include UI components
require_once __DIR__ . '/../component/ui/input.php';
require_once __DIR__ . '/../component/ui/button.php';

// Handle form submission
$error = '';
$success = '';
$loading = false;

    // Only process form submission if it's a POST request with register_form
    // Also check if it's an AJAX request to prevent unnecessary processing
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_form']) && !empty($_POST['username'])) {
        $loading = true;
        
        $username = trim($_POST['username'] ?? '');
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName = trim($_POST['lastName'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Debug: Log the received data
        error_log("Register form data - Username: $username, Email: $email, FirstName: $firstName, LastName: $lastName");
        
        // Debug: Check if email is empty
        if (empty($email)) {
            error_log("Email is empty: '$email'");
        }
    
    // Validation
    if (empty($username)) {
        echo json_encode(['success' => false, 'message' => 'Username is required']);
        exit;
    } elseif (empty($firstName)) {
        echo json_encode(['success' => false, 'message' => 'First name is required']);
        exit;
    } elseif (empty($lastName)) {
        echo json_encode(['success' => false, 'message' => 'Last name is required']);
        exit;
    } elseif (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        exit;
    } elseif (empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Password is required']);
        exit;
    } elseif (strlen($password) < 5) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 5 characters long']);
        exit;
    } else {
        try {
            // Include database connection
            require_once __DIR__ . '/../config/database.php';
            require_once __DIR__ . '/../modal/user.model.php';
            
            $conn = getDatabaseConnection();
            
            if ($conn) {
                // Debug: Test database connection
                error_log("Database connection successful");
                
                $userModel = new User($conn);
                
                // Check if username already exists
                if ($userModel->usernameExists($username)) {
                    echo json_encode(['success' => false, 'message' => 'Username already exists. Please choose a different one.']);
                    exit;
                } elseif ($userModel->emailExists($email)) {
                    echo json_encode(['success' => false, 'message' => 'Email already registered. Please use a different email.']);
                    exit;
                } else {
                    // Create new user
                    $userData = [
                        'username' => $username,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'phone' => '', // Default empty phone
                        'address' => [] // Default empty address
                    ];
                    
                    // Debug: Log the user data being sent
                    error_log("Attempting to create user with data: " . json_encode($userData));
                    
                    if ($userModel->createUser($userData)) {
                        // Registration successful - return success response
                        echo json_encode(['success' => true, 'message' => 'Registration successful! You can now login.']);
                        exit;
                    } else {
                        // Registration failed - return error response
                        error_log("User creation failed in User model");
                        echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
                        exit;
                    }
                }
                
                $conn->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Database connection failed']);
                exit;
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            error_log("Registration error trace: " . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'An error occurred during registration: ' . $e->getMessage()]);
            exit;
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
            <h2 class="text-2xl font-bold text-primary mb-6">Register</h2>
            
            <!-- Register Form -->
            <form id="registerForm" class="space-y-6">
                <input type="hidden" name="register_form" value="1">
                <!-- Username Field -->
                <div>
                    <?php echo renderInput([
                        'label' => 'Username',
                        'type' => 'text',
                        'name' => 'username',
                        'value' => htmlspecialchars($username ?? ''),
                        'required' => true,
                        'disabled' => $loading,
                        'placeholder' => 'Enter your username'
                    ]); ?>
                </div>
                
                <!-- First Name Field -->
                <div>
                    <?php echo renderInput([
                        'label' => 'First Name',
                        'type' => 'text',
                        'name' => 'firstName',
                        'value' => htmlspecialchars($firstName ?? ''),
                        'required' => true,
                        'disabled' => $loading,
                        'placeholder' => 'Enter your first name'
                    ]); ?>
                </div>
                
                <!-- Last Name Field -->
                <div>
                    <?php echo renderInput([
                        'label' => 'Last Name',
                        'type' => 'text',
                        'name' => 'lastName',
                        'value' => htmlspecialchars($lastName ?? ''),
                        'required' => true,
                        'disabled' => $loading,
                        'placeholder' => 'Enter your last name'
                    ]); ?>
                </div>
                
                <!-- Email Field -->
                <div>
                    <?php echo renderInput([
                        'label' => 'Email',
                        'type' => 'email',
                        'name' => 'email',
                        'value' => htmlspecialchars($email ?? ''),
                        'required' => true,
                        'disabled' => $loading,
                        'placeholder' => 'Enter your email address'
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
                        'placeholder' => 'Enter your password (min 5 characters)'
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
                        'children' => $loading ? 'Creating Account...' : 'Register',
                        'id' => 'registerSubmitBtn'
                    ]); ?>
                </div>
                
                <!-- Login Link -->
                <div class="text-sm font-medium text-neutral text-center">
                    Already registered? 
                    <button
                        type="button"
                        onclick="switchToLogin()"
                        class="text-accent hover:text-accent/80 transition-colors"
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
        
        // Handle register form submission
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const username = formData.get('username');
            const firstName = formData.get('firstName');
            const lastName = formData.get('lastName');
            const email = formData.get('email');
            const password = formData.get('password');
            
            // Show loading state
            const submitBtn = document.getElementById('registerSubmitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>Creating Account...';
            submitBtn.disabled = true;
            
            // Debug: Log the data being sent
            console.log('Sending registration data:', { username, firstName, lastName, email, password });
            
            // Send registration request
            fetch('/snapshop/auth/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `register_form=1&username=${encodeURIComponent(username)}&firstName=${encodeURIComponent(firstName)}&lastName=${encodeURIComponent(lastName)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    if (typeof showToast === 'function') {
                        showToast(data.message, 'success', 5000);
                    }
                    
                    // Close modal and switch to login
                    setTimeout(() => {
                        closeRegisterModal();
                        openLoginModal();
                    }, 1000);
                } else {
                    // Show error message
                    if (typeof showToast === 'function') {
                        showToast(data.message || 'Registration failed', 'error', 5000);
                    }
                    
                    // Reset button
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Registration error:', error);
                if (typeof showToast === 'function') {
                    showToast('An error occurred during registration', 'error', 5000);
                }
                
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    </script>
