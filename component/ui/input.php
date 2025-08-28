<?php
// Input Component
// PHP equivalent of the React Input component

class Input {
    private $label;
    private $type;
    private $name;
    private $value;
    private $onChange;
    private $error;
    private $success;
    private $disabled;
    private $required;
    private $icon;
    private $iconRight;
    private $placeholder;
    private $className;
    private $props;

    public function __construct($props = []) {
        $this->label = $props['label'] ?? '';
        $this->type = $props['type'] ?? 'text';
        $this->name = $props['name'] ?? '';
        $this->value = $props['value'] ?? '';
        $this->onChange = $props['onChange'] ?? '';
        $this->error = $props['error'] ?? '';
        $this->success = $props['success'] ?? '';
        $this->disabled = $props['disabled'] ?? false;
        $this->required = $props['required'] ?? false;
        $this->icon = $props['icon'] ?? '';
        $this->iconRight = $props['iconRight'] ?? '';
        $this->placeholder = $props['placeholder'] ?? '';
        $this->className = $props['className'] ?? '';
        $this->props = $props;
    }

    public function render() {
        $baseClasses = 'w-full px-4 py-3 border-2 rounded-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:border-2 disabled:opacity-50 disabled:cursor-not-allowed';
        
        $inputClasses = $baseClasses;
        
        if ($this->error) {
            $inputClasses .= ' border-danger focus:ring-danger/50 focus:border-danger focus:border-2';
        } elseif ($this->success) {
            $inputClasses .= ' border-danger focus:ring-success/50 focus:border-success focus:border-2';
        } else {
            $inputClasses .= ' border-gray-300 focus:ring-accent/50 focus:border-accent focus:border-2 hover:border-gray-400';
        }
        
        $inputClasses .= " {$this->className}";
        
        // Add padding for icons
        if ($this->icon) {
            $inputClasses .= ' pl-10';
        }
        if ($this->iconRight) {
            $inputClasses .= ' pr-10';
        }
        
        // Build additional attributes
        $additionalAttrs = '';
        foreach ($this->props as $key => $value) {
            if (!in_array($key, ['label', 'type', 'name', 'value', 'onChange', 'error', 'success', 'disabled', 'required', 'icon', 'iconRight', 'placeholder', 'className'])) {
                $additionalAttrs .= " {$key}=\"{$value}\"";
            }
        }
        
        $requiredAttr = $this->required ? 'required' : '';
        $disabledAttr = $this->disabled ? 'disabled' : '';
        $placeholderAttr = $this->placeholder ? "placeholder=\"{$this->placeholder}\"" : '';
        
        $html = '<div class="w-full">';
        
        // Label
        if ($this->label) {
            $requiredMark = $this->required ? '<span class="text-danger ml-1">*</span>' : '';
            $html .= "<label class=\"block text-sm font-medium text-neutral mb-2\">{$this->label}{$requiredMark}</label>";
        }
        
        // Input container
        $html .= '<div class="relative">';
        
        // Left icon
        if ($this->icon) {
            $html .= "<div class=\"absolute left-3 top-1/2 -translate-y-1/2 text-neutral\">{$this->icon}</div>";
        }
        
        // Input field
        $html .= "<input
            type=\"{$this->type}\"
            name=\"{$this->name}\"
            placeholder=\"{$this->placeholder}\"
            value=\"{$this->value}\"
            {$requiredAttr}
            {$disabledAttr}
            {$placeholderAttr}
            class=\"{$inputClasses}\"
            {$additionalAttrs}
        />";
        
        // Right icon
        if ($this->iconRight) {
            $html .= "<div class=\"absolute right-3 top-1/2 -translate-y-1/2 text-neutral\">{$this->iconRight}</div>";
        }
        
        $html .= '</div>';
        
        // Error message
        if ($this->error) {
            $html .= "<p class=\"mt-1 text-sm text-danger\">{$this->error}</p>";
        }
        
        // Success message
        if ($this->success) {
            $html .= "<p class=\"mt-1 text-sm text-success\">{$this->success}</p>";
        }
        
        $html .= '</div>';
        
        return $html;
    }
}

// Helper function to render input
function renderInput($props = []) {
    $input = new Input($props);
    return $input->render();
}
?>
