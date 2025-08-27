<?php
// LoadingSpinner Component
// PHP equivalent of the React LoadingSpinner component

class LoadingSpinner {
    private $size;
    private $variant;
    private $text;
    private $className;

    public function __construct($props = []) {
        $this->size = $props['size'] ?? 'md';
        $this->variant = $props['variant'] ?? 'primary';
        $this->text = $props['text'] ?? '';
        $this->className = $props['className'] ?? '';
    }

    public function render() {
        $sizes = [
            'xs' => 'w-4 h-4',
            'sm' => 'w-6 h-6',
            'md' => 'w-8 h-8',
            'lg' => 'w-12 h-12',
            'xl' => 'w-16 h-16',
        ];

        $variants = [
            'primary' => 'border-primary/30 border-t-primary',
            'secondary' => 'border-secondary/30 border-t-secondary',
            'accent' => 'border-accent/30 border-t-accent',
            'success' => 'border-success/30 border-t-success',
            'danger' => 'border-danger/30 border-t-danger',
            'white' => 'border-white/30 border-t-white',
            'gray' => 'border-gray-300 border-t-gray-600',
        ];

        $sizeClass = $sizes[$this->size] ?? $sizes['md'];
        $variantClass = $variants[$this->variant] ?? $variants['primary'];
        $spinnerClasses = trim("$sizeClass border-2 rounded-full animate-spin $variantClass {$this->className}");

        return "
        <div class=\"flex flex-col items-center justify-center\">
            <div class=\"{$spinnerClasses}\"></div>
            " . ($this->text ? "<p class=\"mt-3 text-sm text-neutral text-center\">{$this->text}</p>" : '') . "
        </div>
        ";
    }
}

// Helper function to render loading spinner
function renderLoadingSpinner($props = []) {
    $spinner = new LoadingSpinner($props);
    return $spinner->render();
}
?>
