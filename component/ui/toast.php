<?php
// Toast Notification System - PHP version of React Toast component
require_once __DIR__ . '/../../auth/auth-helper.php';
startSessionIfNotStarted();

// Initialize toast array if not exists
if (!isset($_SESSION['toasts'])) {
    $_SESSION['toasts'] = [];
}

// Handle toast actions
if (isset($_POST['toast_action'])) {
    $action = $_POST['toast_action'];
    $toastId = $_POST['toast_id'] ?? null;
    
    if ($action === 'hide' && $toastId !== null) {
        // Remove specific toast
        $_SESSION['toasts'] = array_filter($_SESSION['toasts'], function($toast) use ($toastId) {
            return $toast['id'] !== $toastId;
        });
    } elseif ($action === 'clear_all') {
        // Clear all toasts
        $_SESSION['toasts'] = [];
    }
}

// Function to add toast
function addToast($message, $type = 'info', $duration = 5000) {
    if (!isset($_SESSION['toasts'])) {
        $_SESSION['toasts'] = [];
    }
    
    $toast = [
        'id' => uniqid(),
        'message' => $message,
        'type' => $type,
        'duration' => $duration,
        'timestamp' => time()
    ];
    
    $_SESSION['toasts'][] = $toast;
}

// Function to get toasts
function getToasts() {
    return $_SESSION['toasts'] ?? [];
}

// Function to clear toasts
function clearToasts() {
    $_SESSION['toasts'] = [];
}

// Auto-clear old toasts (older than 10 seconds)
if (isset($_SESSION['toasts'])) {
    $currentTime = time();
    $_SESSION['toasts'] = array_filter($_SESSION['toasts'], function($toast) use ($currentTime) {
        return ($currentTime - $toast['timestamp']) < 10;
    });
}
?>

<!-- Toast Container -->
<div id="toastContainer" class="fixed top-20 right-4 z-50 max-w-sm w-full">
    <?php foreach (getToasts() as $toast): ?>
        <?php
        // Toast configuration based on type
        $config = [
            'success' => [
                'icon' => 'fas fa-check-circle',
                'bg' => 'bg-green-500',
                'text' => 'text-white',
                'border' => 'border-green-200',
            ],
            'error' => [
                'icon' => 'fas fa-exclamation-circle',
                'bg' => 'bg-red-500',
                'text' => 'text-white',
                'border' => 'border-red-200',
            ],
            'warning' => [
                'icon' => 'fas fa-exclamation-triangle',
                'bg' => 'bg-yellow-500',
                'text' => 'text-white',
                'border' => 'border-yellow-200',
            ],
            'info' => [
                'icon' => 'fas fa-info-circle',
                'bg' => 'bg-blue-500',
                'text' => 'text-white',
                'border' => 'border-blue-200',
            ],
        ];
        
        $toastConfig = $config[$toast['type']] ?? $config['info'];
        ?>
        
        <div 
            id="toast-<?php echo $toast['id']; ?>"
            class="flex items-center justify-between p-4 mb-3 rounded-lg shadow-lg border <?php echo $toastConfig['bg']; ?> <?php echo $toastConfig['text']; ?> <?php echo $toastConfig['border']; ?> backdrop-blur-sm bg-opacity-95 animate-slide-in-right"
            data-toast-id="<?php echo $toast['id']; ?>"
            data-duration="<?php echo $toast['duration']; ?>"
        >
            <div class="flex items-center space-x-3">
                <i class="<?php echo $toastConfig['icon']; ?> w-5 h-5"></i>
                <span class="font-medium"><?php echo htmlspecialchars($toast['message']); ?></span>
            </div>
            <button
                onclick="hideToast('<?php echo $toast['id']; ?>')"
                class="hover:opacity-80 transition-opacity duration-200 p-1 rounded-full hover:bg-white/20"
            >
                <i class="fas fa-times w-4 h-4"></i>
            </button>
        </div>
    <?php endforeach; ?>
</div>

<!-- Toast JavaScript -->
<script>
// Toast functions
function showToast(message, type = 'info', duration = 5000) {
    // Create toast element
    const toastContainer = document.getElementById('toastContainer');
    const toastId = 'toast-' + Date.now();
    
    const config = {
        success: {
            icon: 'fas fa-check-circle',
            bg: 'bg-green-500',
            text: 'text-white',
            border: 'border-green-200',
        },
        error: {
            icon: 'fas fa-exclamation-circle',
            bg: 'bg-red-500',
            text: 'text-white',
            border: 'border-red-200',
        },
        warning: {
            icon: 'fas fa-exclamation-triangle',
            bg: 'bg-yellow-500',
            text: 'text-white',
            border: 'border-yellow-200',
        },
        info: {
            icon: 'fas fa-info-circle',
            bg: 'bg-blue-500',
            text: 'text-white',
            border: 'border-blue-200',
        },
    };
    
    const toastConfig = config[type] || config.info;
    
    const toastHTML = `
        <div 
            id="${toastId}"
            class="flex items-center justify-between p-4 mb-3 rounded-lg shadow-lg border ${toastConfig.bg} ${toastConfig.text} ${toastConfig.border} backdrop-blur-sm bg-opacity-95 animate-slide-in-right"
            data-toast-id="${toastId.replace('toast-', '')}"
            data-duration="${duration}"
        >
            <div class="flex items-center space-x-3">
                <i class="${toastConfig.icon} w-5 h-5"></i>
                <span class="font-medium">${message}</span>
            </div>
            <button
                onclick="hideToast('${toastId.replace('toast-', '')}')"
                class="hover:opacity-80 transition-opacity duration-200 p-1 rounded-full hover:bg-white/20"
            >
                <i class="fas fa-times w-4 h-4"></i>
            </button>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    // Auto-hide after duration
    setTimeout(() => {
        hideToast(toastId.replace('toast-', ''));
    }, duration);
}

function hideToast(toastId) {
    const toastElement = document.getElementById('toast-' + toastId);
    if (toastElement) {
        // Add slide-out animation
        toastElement.classList.add('animate-slide-out-right');
        
        // Remove after animation
        setTimeout(() => {
            if (toastElement.parentNode) {
                toastElement.parentNode.removeChild(toastElement);
            }
        }, 300);
    }
}

function clearAllToasts() {
    const toasts = document.querySelectorAll('[id^="toast-"]');
    toasts.forEach(toast => {
        toast.classList.add('animate-slide-out-right');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    });
}

// Auto-hide existing toasts
document.addEventListener('DOMContentLoaded', function() {
    const toasts = document.querySelectorAll('[id^="toast-"]');
    toasts.forEach(toast => {
        const duration = parseInt(toast.dataset.duration) || 5000;
        const toastId = toast.dataset.toastId;
        
        setTimeout(() => {
            hideToast(toastId);
        }, duration);
    });
});
</script>

<!-- Toast CSS Animations -->
<style>
@keyframes slide-in-right {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slide-out-right {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

.animate-slide-in-right {
    animation: slide-in-right 0.3s ease-out;
}

.animate-slide-out-right {
    animation: slide-out-right 0.3s ease-out;
}
</style>
