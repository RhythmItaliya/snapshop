<?php
// Button Component
// PHP equivalent of the React Button component

class Button {
    private $children;
    private $variant;
    private $size;
    private $disabled;
    private $loading;
    private $icon;
    private $iconRight;
    private $className;
    private $onClick;
    private $type;
    private $id;
    private $props;

    public function __construct($props = []) {
        $this->children = $props['children'] ?? '';
        $this->variant = $props['variant'] ?? 'primary';
        $this->size = $props['size'] ?? 'md';
        $this->disabled = $props['disabled'] ?? false;
        $this->loading = $props['loading'] ?? false;
        $this->icon = $props['icon'] ?? '';
        $this->iconRight = $props['iconRight'] ?? '';
        $this->className = $props['className'] ?? '';
        $this->onClick = $props['onClick'] ?? '';
        $this->type = $props['type'] ?? 'button';
        $this->id = $props['id'] ?? '';
        $this->props = $props;
    }

    public function render() {
        $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

        $variants = [
            'primary' => 'bg-primary text-white hover:bg-primary/90 focus:ring-primary/50',
            'secondary' => 'bg-secondary text-white hover:bg-secondary/90 focus:ring-secondary/50',
            'accent' => 'bg-accent text-white hover:bg-accent/90 focus:ring-accent/50',
            'success' => 'bg-success text-white hover:bg-success/90 focus:ring-success/50',
            'danger' => 'bg-danger text-white hover:bg-danger/90 focus:ring-danger/50',
            'outline' => 'border-2 border-primary text-primary hover:bg-primary hover:text-white focus:ring-primary/50',
            'ghost' => 'text-primary hover:bg-primary/10 focus:ring-primary/50',
            'light' => 'bg-light text-neutral hover:bg-gray-200 focus:ring-neutral/50',
        ];

        $sizes = [
            'sm' => 'px-3 py-2 text-sm',
            'md' => 'px-4 py-2 text-sm',
            'lg' => 'px-6 py-3 text-base',
            'xl' => 'px-8 py-4 text-lg',
        ];

        $variantClass = $variants[$this->variant] ?? $variants['primary'];
        $sizeClass = $sizes[$this->size] ?? $sizes['md'];
        $classes = trim("$baseClasses $variantClass $sizeClass {$this->className}");

        $disabledAttr = ($this->disabled || $this->loading) ? 'disabled' : '';
        $onClickAttr = $this->onClick ? "onclick=\"{$this->onClick}\"" : '';
        $idAttr = $this->id ? "id=\"{$this->id}\"" : '';

        return "
        <button type=\"{$this->type}\" class=\"{$classes}\" {$disabledAttr} {$onClickAttr} {$idAttr}>
            " . ($this->loading ? "
            <div class=\"w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2\"></div>
            " : '') . "
            
            " . ($this->icon && !$this->loading ? "<span class=\"mr-2\">{$this->icon}</span>" : '') . "
            
            {$this->children}
            
            " . ($this->iconRight && !$this->loading ? "<span class=\"ml-2\">{$this->iconRight}</span>" : '') . "
        </button>
        ";
    }
}

// Helper function to render button
function renderButton($props = []) {
    $button = new Button($props);
    return $button->render();
}
?>
