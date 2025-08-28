<?php
// Admin Products Page
session_start();

// Require admin authentication
require_once 'auth/admin-auth-helper.php';
AdminAuthHelper::requireAdminAuth();

// Get admin data from session
$admin = AdminAuthHelper::getAdminData();

// Handle form submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if image was uploaded
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('No image uploaded or upload error');
        }

        $imageFile = $_FILES['image'];
        
        // Validate image
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($imageFile['type'], $allowedTypes)) {
            throw new Exception('Invalid image type. Only JPG, JPEG, and PNG are allowed.');
        }

        // Upload to Cloudinary
        require_once '../config/Env.php';
        $cloudinaryConfig = Env::cloudinary();
        $cloudName = $cloudinaryConfig['cloud_name'];
        $apiKey = $cloudinaryConfig['api_key'];
        $apiSecret = $cloudinaryConfig['api_secret'];
        $folder = $cloudinaryConfig['folder'];

        // Debug: Check Cloudinary configuration
        if (empty($cloudName) || empty($apiKey) || empty($apiSecret)) {
            throw new Exception('Cloudinary configuration is incomplete. Cloud Name: ' . $cloudName . ', API Key: ' . $apiKey . ', API Secret: ' . (empty($apiSecret) ? 'EMPTY' : 'SET'));
        }

        // Create Cloudinary upload URL
        $uploadUrl = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";
        
        // Prepare upload data - simplified approach
        $uploadData = [
            'file' => new CURLFile($imageFile['tmp_name'], $imageFile['type'], $imageFile['name']),
            'public_id' => uniqid('product_'),
            'folder' => $folder,
            'api_key' => $apiKey,
            'timestamp' => time()
        ];

        // Generate signature for Cloudinary - must be in correct order
        $signatureString = 'folder=' . $uploadData['folder'] . '&public_id=' . $uploadData['public_id'] . '&timestamp=' . $uploadData['timestamp'];
        $signature = sha1($signatureString . $apiSecret);
        $uploadData['signature'] = $signature;

        // Upload to Cloudinary using cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uploadUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $uploadData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('Failed to upload image to Cloudinary. HTTP Code: ' . $httpCode . '. cURL Error: ' . $curlError . '. Response: ' . $response);
        }

        $cloudinaryResult = json_decode($response, true);
        if (!$cloudinaryResult || !isset($cloudinaryResult['secure_url'])) {
            throw new Exception('Invalid response from Cloudinary: ' . $response);
        }

        $imageUrl = $cloudinaryResult['secure_url'];
        $publicId = $cloudinaryResult['public_id'];

        // Get form data
        $name = $_POST['name'] ?? '';
        $category = $_POST['category'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = floatval($_POST['price'] ?? 0);
        $highPrice = floatval($_POST['highPrice'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $gender = $_POST['gender'] ?? '';
        $discount = floatval($_POST['discount'] ?? 0);
        
        // Parse sizes and colors
        $sizes = $_POST['sizes'] ?? [];
        $colors = $_POST['colors'] ?? [];

        // Validate required fields
        if (empty($name) || empty($category) || empty($description) || empty($gender)) {
            throw new Exception('Missing required fields');
        }

        // Connect to database
        require_once '../config/database.php';
        $conn = getDatabaseConnection();
        if (!$conn) {
            throw new Exception('Database connection failed');
        }

        // Start transaction
        $conn->begin_transaction();

        try {
            // Insert product into database
            $sql = "INSERT INTO products (name, category, description, price, high_price, stock, gender, discount, image, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssddisss", 
                $name, $category, $description, $price, $highPrice, $stock, 
                $gender, $discount, $imageUrl
            );

            if (!$stmt->execute()) {
                throw new Exception('Failed to save product to database: ' . $stmt->error);
            }

            $productId = $conn->insert_id;
            $stmt->close();

            // Insert sizes into product_sizes table
            if (!empty($sizes)) {
                $sizeSql = "INSERT INTO product_sizes (product_id, size) VALUES (?, ?)";
                $sizeStmt = $conn->prepare($sizeSql);
                
                foreach ($sizes as $size) {
                    $sizeStmt->bind_param("is", $productId, $size);
                    if (!$sizeStmt->execute()) {
                        throw new Exception('Failed to save size: ' . $size);
                    }
                }
                $sizeStmt->close();
            }

            // Insert colors into product_colors table
            if (!empty($colors)) {
                $colorSql = "INSERT INTO product_colors (product_id, color) VALUES (?, ?)";
                $colorStmt = $conn->prepare($colorSql);
                
                foreach ($colors as $color) {
                    $colorStmt->bind_param("is", $productId, $color);
                    if (!$colorStmt->execute()) {
                        throw new Exception('Failed to save color: ' . $color);
                    }
                }
                $colorStmt->close();
            }

            // Commit transaction
            $conn->commit();
            $conn->close();

            $success = 'Product created successfully!';
            
            // Redirect immediately to prevent form resubmission
            header('Location: /snapshop/admin/index.php?success=product_created');
            exit();

        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $conn->close();
            throw $e;
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - SnapShop Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
</head>
<body class="min-h-screen bg-gray-50">
            <div class="flex">
        <!-- Admin Sidebar -->
        <?php include 'component/sidebar.php'; ?>
        
        <!-- Main Content Area -->
        <main class="flex-1 ml-64 p-8">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-900">Add New Product</h1>
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
            
            <div class="w-full max-w-4xl">
                <div class="bg-white shadow rounded-lg p-6">
                    <form id="addProductForm" method="POST" enctype="multipart/form-data" class="space-y-6">
                        <!-- Product Image -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Product Image
                            </label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                <div id="imagePreview" class="flex justify-center mb-4">
                                    <!-- Single image preview will be shown here -->
                                </div>
                                <input 
                                    type="file" 
                                    id="productImage" 
                                    name="image" 
                                    accept="image/*"
                                    class="hidden"
                                />
                                <button 
                                    type="button" 
                                    onclick="document.getElementById('productImage').click()"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Select Image
                                </button>
                                <p class="mt-2 text-xs text-gray-500">Upload 1 product image (JPG, PNG)</p>
                            </div>
                        </div>

                        <!-- Basic Product Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Product Name *
                                </label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="e.g., Cotton T-Shirt"
                                />
                            </div>
                            
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                    Category *
                                </label>
                                <select 
                                    id="category" 
                                    name="category" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="">Select Category</option>
                                    <option value="t-shirts">T-Shirts</option>
                                    <option value="shirts">Shirts</option>
                                    <option value="pants">Pants</option>
                                    <option value="jeans">Jeans</option>
                                    <option value="dresses">Dresses</option>
                                    <option value="skirts">Skirts</option>
                                    <option value="jackets">Jackets</option>
                                    <option value="hoodies">Hoodies</option>
                                    <option value="sweaters">Sweaters</option>
                                    <option value="shorts">Shorts</option>
                                </select>
                            </div>
                        </div>

                        <!-- Product Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Product Description *
                            </label>
                            <textarea 
                                id="description" 
                                name="description" 
                                rows="4"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Describe the product features, material, style, etc."
                            ></textarea>
                        </div>

                        <!-- Pricing and Stock -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                                    Price (₹) *
                                </label>
                                <input 
                                    type="number" 
                                    id="price" 
                                    name="price" 
                                    step="0.01"
                                    min="0"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="0.00"
                                />
                            </div>
                            
                            <div>
                                <label for="highPrice" class="block text-sm font-medium text-gray-700 mb-2">
                                    High Price (₹) *
                                </label>
                                <input 
                                    type="number" 
                                    id="highPrice" 
                                    name="highPrice" 
                                    step="0.01"
                                    min="0"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="0.00"
                                />
                            </div>
                            
                            <div>
                                <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">
                                    Stock Quantity *
                                </label>
                                <input 
                                    type="number" 
                                    id="stock" 
                                    name="stock" 
                                    min="0"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="0"
                                />
                            </div>
                        </div>

                        <!-- Clothing Specific Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="size" class="block text-sm font-medium text-gray-700 mb-2">
                                    Available Sizes
                                </label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="sizes" value="XS" class="mr-2">
                                        <span class="text-sm">XS</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="sizes" value="S" class="mr-2">
                                        <span class="text-sm">S</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="sizes" value="M" class="mr-2">
                                        <span class="text-sm">M</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="sizes" value="L" class="mr-2">
                                        <span class="text-sm">L</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="sizes" value="XL" class="mr-2">
                                        <span class="text-sm">XL</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="sizes" value="XXL" class="mr-2">
                                        <span class="text-sm">XXL</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div>
                                <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                                    Available Colors
                                </label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="colors" value="Black" class="mr-2">
                                        <span class="text-sm">Black</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="colors" value="White" class="mr-2">
                                        <span class="text-sm">White</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="colors" value="Blue" class="mr-2">
                                        <span class="text-sm">Blue</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="colors" value="Red" class="mr-2">
                                        <span class="text-sm">Red</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="colors" value="Green" class="mr-2">
                                        <span class="text-sm">Green</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="colors" value="Yellow" class="mr-2">
                                        <span class="text-sm">Yellow</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">
                                    Gender *
                                </label>
                                <select 
                                    id="gender" 
                                    name="gender"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="">Select Gender</option>
                                    <option value="men">Men</option>
                                    <option value="women">Women</option>
                                    <option value="unisex">Unisex</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="discount" class="block text-sm font-medium text-gray-700 mb-2">
                                Discount (₹) *
                            </label>
                            <input 
                                type="number" 
                                id="discount" 
                                name="discount" 
                                min="0"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="0"
                            />
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200 mt-4">
                            <button 
                                type="button" 
                                onclick="window.location.href='/snapshop/admin/index.php'"
                                class="px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                id="submitBtn"
                                class="px-6 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                            >
                                <span id="submitText">Create Product</span>
                                <span id="submitLoading" class="hidden">
                                    <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Creating Product...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, setting up image preview...');
        
        const imageInput = document.getElementById('productImage');
        const imagePreview = document.getElementById('imagePreview');
        
        console.log('Image input found:', imageInput);
        console.log('Image preview found:', imagePreview);
        
        if (!imageInput || !imagePreview) {
            console.error('Required elements not found!');
            return;
        }
        
        // Handle image selection and preview
        imageInput.addEventListener('change', function(e) {
            console.log('Image input changed:', e.target.files[0]);
            
            const file = e.target.files[0];
            imagePreview.innerHTML = '';
            
            if (file) {
                console.log('File selected:', file.name, file.type);
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    console.log('File read successfully');
                    
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'relative';
                    previewDiv.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" class="w-32 h-32 object-cover rounded-lg border">
                        <button type="button" onclick="removeImage()" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                            ×
                        </button>
                    `;
                    imagePreview.appendChild(previewDiv);
                    console.log('Preview added to DOM');
                };
                
                reader.onerror = function() {
                    console.error('Error reading file');
                };
                
                reader.readAsDataURL(file);
            }
        });
        
        // Function to remove image
        window.removeImage = function() {
            console.log('Removing image...');
            imagePreview.innerHTML = '';
            imageInput.value = '';
        };
        
        // Handle form submission with loading state
        const form = document.getElementById('addProductForm');
        if (form) {
            console.log('Form found, adding submit listener...');
            form.addEventListener('submit', function(e) {
                console.log('Form submitted, showing loading state...');
                
                const submitBtn = document.getElementById('submitBtn');
                const submitText = document.getElementById('submitText');
                const submitLoading = document.getElementById('submitLoading');
                
                console.log('Submit button:', submitBtn);
                console.log('Submit text:', submitText);
                console.log('Submit loading:', submitLoading);
                
                if (submitBtn && submitText && submitLoading) {
                    // Show loading state
                    submitBtn.disabled = true;
                    submitText.classList.add('hidden');
                    submitLoading.classList.remove('hidden');
                    
                    console.log('Loading state activated');
                    console.log('Button disabled:', submitBtn.disabled);
                    console.log('Text hidden:', submitText.classList.contains('hidden'));
                    console.log('Loading visible:', !submitLoading.classList.contains('hidden'));
                    
                    // Allow form to submit normally
                    // The loading state will remain until page reload
                } else {
                    console.error('Some elements not found for loading state');
                }
            });
        } else {
            console.error('Form not found!');
        }
        
        console.log('Image preview setup complete');
        
        // Test loading state manually
        console.log('Testing loading state elements...');
        const testSubmitBtn = document.getElementById('submitBtn');
        const testSubmitText = document.getElementById('submitText');
        const testSubmitLoading = document.getElementById('submitLoading');
        
        console.log('Test - Submit button:', testSubmitBtn);
        console.log('Test - Submit text:', testSubmitText);
        console.log('Test - Submit loading:', testSubmitLoading);
        
        if (testSubmitBtn && testSubmitText && testSubmitLoading) {
            console.log('All loading state elements found and ready!');
        } else {
            console.error('Missing loading state elements!');
        }
    });
    </script>
</body>
</html>
