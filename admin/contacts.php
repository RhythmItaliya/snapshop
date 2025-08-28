<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Admin Contacts Management Page
session_start();

// Require admin authentication
require_once 'auth/admin-auth-helper.php';
AdminAuthHelper::requireAdminAuth();

// Get admin data from session
$admin = AdminAuthHelper::getAdminData();

// Include required files
require_once '../config/database.php';
require_once '../modal/contact.model.php';

$contactModel = new Contact(getDatabaseConnection());

try {
    $contacts = $contactModel->getAllContacts();
} catch (Exception $e) {
    $error = "Error loading contacts: " . $e->getMessage();
    $contacts = [];
}

$success = '';
$error = '';

// Handle contact status update
if (isset($_POST['update_status']) && isset($_POST['contact_id']) && isset($_POST['new_status'])) {
    $contactId = $_POST['contact_id'];
    $newStatus = $_POST['new_status'];
    
    try {
        if ($contactModel->updateContactStatus($contactId, $newStatus)) {
            $success = "Contact status updated successfully!";
            $contacts = $contactModel->getAllContacts(); // Refresh the list
        } else {
            $error = "Failed to update contact status.";
        }
    } catch (Exception $e) {
        $error = "Error updating contact status: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - SnapShop Admin</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css">
    <link rel="stylesheet" href="../node_modules/aos/dist/aos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gray-50">
    <div class="flex">
        <!-- Admin Sidebar -->
        <?php include 'component/sidebar.php'; ?>
        
        <!-- Main Content Area -->
        <main class="flex-1 ml-64 p-8">
            <div class="w-full">
                <div class="p-8">
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-900">Contact Us</h1>
                        <p class="text-gray-600">View all contact form submissions from customers - Total Contacts: <span id="contactCount"><?php echo count($contacts); ?></span></p>
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
                    
                    <?php if (!empty($contacts)): ?>
                        <div class="bg-white shadow rounded-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">All Contact Submissions (<?php echo count($contacts); ?>)</h3>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer Info</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($contacts as $contact): ?>
                                            <tr class="hover:bg-gray-50">
                                                <!-- Customer Info Column -->
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($contact['name'] ?? 'N/A'); ?></div>
                                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($contact['email'] ?? 'N/A'); ?></div>
                                                </td>
                                                
                                                <!-- Message Column -->
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-900 max-w-xs truncate" title="<?php echo htmlspecialchars($contact['message'] ?? ''); ?>">
                                                        <?php 
                                                        $message = $contact['message'] ?? '';
                                                        if (strlen($message) > 100) {
                                                            echo htmlspecialchars(substr($message, 0, 100)) . '...';
                                                        } else {
                                                            echo htmlspecialchars($message);
                                                        }
                                                        ?>
                                                    </div>
                                                    <?php if (strlen($message) > 100): ?>
                                                        <button onclick="showFullMessage(<?php echo $contact['id']; ?>)" class="text-xs text-blue-600 hover:text-blue-800 mt-1">
                                                            Read More
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                                
                                                <!-- Status Column -->
                                                <td class="px-6 py-4">
                                                    <?php 
                                                    $status = $contact['status'] ?? 'pending';
                                                    $statusClass = '';
                                                    if ($status === 'pending') {
                                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                                    } elseif ($status === 'read') {
                                                        $statusClass = 'bg-blue-100 text-blue-800';
                                                    } else {
                                                        $statusClass = 'bg-green-100 text-green-800';
                                                    }
                                                    ?>
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusClass; ?>">
                                                        <?php echo ucfirst($status); ?>
                                                    </span>
                                                </td>
                                                
                                                <!-- Date Column -->
                                                <td class="px-6 py-4 text-sm text-gray-500">
                                                    <div><?php echo date('m/d/Y', strtotime($contact['created_at'] ?? 'now')); ?></div>
                                                    <div class="text-xs text-gray-400"><?php echo date('h:i:s A', strtotime($contact['created_at'] ?? 'now')); ?></div>
                                                </td>
                                                
                                                <!-- Actions Column -->
                                                <td class="px-6 py-4 text-sm font-medium">
                                                    <div class="flex space-x-2">
                                                        <form method="POST" class="inline">
                                                            <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                                                            <input type="hidden" name="new_status" value="read">
                                                            <button type="submit" 
                                                                    name="update_status"
                                                                    class="text-blue-600 hover:text-blue-900 <?php echo $status === 'read' ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                                                                    <?php echo $status === 'read' ? 'disabled' : ''; ?>>
                                                                Mark as Read
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <div class="mx-auto h-12 w-12 text-gray-400">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No contact submissions found</h3>
                            <p class="mt-1 text-sm text-gray-500">Contact form submissions will appear here when customers submit them.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Full Message Modal -->
    <div id="messageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Full Message</h3>
                <div id="modalMessage" class="text-sm text-gray-700 mb-4"></div>
                <div class="flex justify-end">
                    <button onclick="closeMessageModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Toast Component -->
    <?php include 'component/ui/toast.php'; ?>
    
    <!-- Include Admin Auth JavaScript -->
    <script src="assets/js/admin-auth.js"></script>

    <script>
    // Store contact data for modal
    const contactsData = <?php echo json_encode($contacts); ?>;

    function showFullMessage(contactId) {
        const contact = contactsData.find(c => c.id == contactId);
        if (contact) {
            document.getElementById('modalMessage').textContent = contact.message;
            document.getElementById('messageModal').classList.remove('hidden');
        }
    }

    function closeMessageModal() {
        document.getElementById('messageModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('messageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeMessageModal();
        }
    });
    </script>
</body>
</html>
