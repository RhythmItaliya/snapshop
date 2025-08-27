<?php
// ErrorState Component
// PHP equivalent of the React ErrorState component

class ErrorState {
    private $error;
    private $onRetry;
    private $className;

    public function __construct($props = []) {
        $this->error = $props['error'] ?? '';
        $this->onRetry = $props['onRetry'] ?? '';
        $this->className = $props['className'] ?? '';
    }

    public function render() {
        $className = trim("text-center py-12 {$this->className}");
        
        return "
        <div class=\"{$className}\">
            <h3 class=\"text-xl font-semibold text-primary mb-2\">Something Went Wrong</h3>
            " . ($this->error ? "<p class=\"text-neutral mb-6 max-w-md mx-auto\">{$this->error}</p>" : '') . "
            " . ($this->onRetry ? "
            <button onclick=\"{$this->onRetry}\" class=\"bg-danger text-white px-4 py-2 rounded-lg font-medium hover:bg-danger/90 transition-colors\">
                Try Again
            </button>
            " : '') . "
        </div>
        ";
    }
}

// Helper function to render error state
function renderErrorState($props = []) {
    $errorState = new ErrorState($props);
    return $errorState->render();
}
?>
