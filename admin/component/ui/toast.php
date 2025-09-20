<?php
// Simple Toast Component for Admin Panel
?>
<div id="toastContainer" class="fixed top-20 right-4 z-50 max-w-sm w-full hidden">
    <div id="toastMessage" class="flex items-center justify-between p-4 mb-3 rounded-lg shadow-lg bg-blue-500 text-white border border-blue-200">
        <div class="flex items-center">
            <i id="toastIcon" class="fas fa-info-circle mr-2"></i>
            <span id="toastText">Toast message</span>
        </div>
        <button onclick="hideToast()" class="ml-4 text-white hover:text-blue-200">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<script>
function showToast(message, type = 'info', duration = 3000) {
    const container = document.getElementById('toastContainer');
    const messageEl = document.getElementById('toastMessage');
    const iconEl = document.getElementById('toastIcon');
    const textEl = document.getElementById('toastText');
    
    // Set message
    textEl.textContent = message;
    
    // Set icon and colors based on type
    const config = {
        'success': {
            icon: 'fas fa-check-circle',
            bg: 'bg-green-500',
            border: 'border-green-200'
        },
        'error': {
            icon: 'fas fa-exclamation-circle',
            bg: 'bg-red-500',
            border: 'border-red-200'
        },
        'warning': {
            icon: 'fas fa-exclamation-triangle',
            bg: 'bg-yellow-500',
            border: 'border-yellow-200'
        },
        'info': {
            icon: 'fas fa-info-circle',
            bg: 'bg-blue-500',
            border: 'border-blue-200'
        }
    };
    
    const toastConfig = config[type] || config['info'];
    
    // Update classes
    messageEl.className = `flex items-center justify-between p-4 mb-3 rounded-lg shadow-lg ${toastConfig.bg} text-white border ${toastConfig.border}`;
    iconEl.className = `${toastConfig.icon} mr-2`;
    
    // Show toast
    container.classList.remove('hidden');
    
    // Auto-hide after duration
    setTimeout(() => {
        hideToast();
    }, duration);
}

function hideToast() {
    const container = document.getElementById('toastContainer');
    container.classList.add('hidden');
}

// Global function for other scripts to use
window.showToast = showToast;
window.hideToast = hideToast;
</script>
