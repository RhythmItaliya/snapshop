<?php
// FilterSidebar Component - Converted from React
// This component provides filtering and sorting controls for products

// Include categories utils
require_once __DIR__ . '/../../utils/categories.php';

// Get current filter values from URL parameters
$selectedCategory = $_GET['category'] ?? $PRODUCT_CATEGORIES['ALL'];
$priceRange = [
    $_GET['min_price'] ?? 0,
    $_GET['max_price'] ?? 1000
];
$sortBy = $_GET['sort'] ?? $SORT_OPTIONS['FEATURED'];

// Validate category parameter
if (!in_array($selectedCategory, array_values($PRODUCT_CATEGORIES))) {
    $selectedCategory = $PRODUCT_CATEGORIES['ALL'];
}

// Validate price range
$priceRange[0] = max(0, min($priceRange[0], $priceRange[1]));
$priceRange[1] = min(10000, max($priceRange[0], $priceRange[1]));

// Check if price range is valid
$isPriceRangeValid = $priceRange[0] <= $priceRange[1] && $priceRange[0] >= 0 && $priceRange[1] <= 10000;

// Function to update URL parameters
function updateFilterUrl($type, $value) {
    $urlParams = $_GET;
    
    if ($type === 'category') {
        if ($value === 'all') {
            unset($urlParams['category']);
        } else {
            $urlParams['category'] = $value;
        }
    } elseif ($type === 'min_price') {
        $urlParams['min_price'] = $value;
    } elseif ($type === 'max_price') {
        $urlParams['max_price'] = $value;
    } elseif ($type === 'sort') {
        $urlParams['sort'] = $value;
    }
    
    $queryString = http_build_query($urlParams);
    return '?' . $queryString;
}

// Function to clear all filters
function clearFiltersUrl() {
    return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
}
?>

<div class="w-full bg-white border-b border-gray-200 py-6">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <!-- Category Filter -->
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-sm font-medium text-blue-600">Category:</span>
                <?php foreach ($PRODUCT_CATEGORIES as $key => $category): ?>
                    <a href="<?php echo updateFilterUrl('category', $category); ?>"
                       class="px-4 py-2 text-sm font-medium rounded-lg transition-colors <?php echo $selectedCategory === $category 
                           ? 'bg-blue-600 text-white shadow-lg' 
                           : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300'; ?> capitalize">
                        <?php echo $CATEGORY_LABELS[$category]; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Price and Sort Controls -->
            <div class="flex flex-wrap items-center gap-4">
                <!-- Price Range Filter -->
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-blue-600">Price:</span>
                    <form method="GET" class="flex items-center gap-2" onchange="this.submit()">
                        <!-- Preserve other filter parameters -->
                        <?php if (isset($_GET['category']) && $_GET['category'] !== 'all'): ?>
                            <input type="hidden" name="category" value="<?php echo htmlspecialchars($_GET['category']); ?>">
                        <?php endif; ?>
                        <?php if (isset($_GET['sort'])): ?>
                            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($_GET['sort']); ?>">
                        <?php endif; ?>
                        
                        <input type="number" 
                               name="min_price" 
                               value="<?php echo $priceRange[0]; ?>"
                               class="w-24 px-3 py-2 border-2 border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors <?php echo !$isPriceRangeValid ? 'border-red-300' : ''; ?>"
                               placeholder="Min"
                               min="0"
                               max="<?php echo $priceRange[1]; ?>"
                               step="1">
                        
                        <span class="text-gray-500">-</span>
                        
                        <input type="number" 
                               name="max_price" 
                               value="<?php echo $priceRange[1]; ?>"
                               class="w-24 px-3 py-2 border-2 border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors <?php echo !$isPriceRangeValid ? 'border-red-300' : ''; ?>"
                               placeholder="Max"
                               min="<?php echo $priceRange[0]; ?>"
                               max="10000"
                               step="1">
                    </form>
                    
                    <?php if (!$isPriceRangeValid): ?>
                        <span class="text-xs text-red-500">Invalid price range</span>
                    <?php endif; ?>
                </div>

                <!-- Sort Options -->
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-blue-600">Sort:</span>
                    <form method="GET" class="flex items-center gap-2" onchange="this.submit()">
                        <!-- Preserve other filter parameters -->
                        <?php if (isset($_GET['category']) && $_GET['category'] !== 'all'): ?>
                            <input type="hidden" name="category" value="<?php echo htmlspecialchars($_GET['category']); ?>">
                        <?php endif; ?>
                        <?php if (isset($_GET['min_price'])): ?>
                            <input type="hidden" name="min_price" value="<?php echo htmlspecialchars($_GET['min_price']); ?>">
                        <?php endif; ?>
                        <?php if (isset($_GET['max_price'])): ?>
                            <input type="hidden" name="max_price" value="<?php echo htmlspecialchars($_GET['max_price']); ?>">
                        <?php endif; ?>
                        
                        <select name="sort"
                                class="px-3 py-2 border-2 border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors">
                            <?php foreach ($SORT_OPTIONS as $option): ?>
                                <option value="<?php echo $option; ?>" <?php echo $sortBy === $option ? 'selected' : ''; ?>>
                                    <?php echo $SORT_LABELS[$option]; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <!-- Clear All Filters -->
                <a href="<?php echo clearFiltersUrl(); ?>"
                   class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors bg-gray-100 hover:bg-gray-200 rounded-lg border border-gray-300">
                    Clear All
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript for real-time price validation
document.addEventListener('DOMContentLoaded', function() {
    const minPriceInput = document.querySelector('input[name="min_price"]');
    const maxPriceInput = document.querySelector('input[name="max_price"]');
    
    if (minPriceInput && maxPriceInput) {
        // Update max price min value when min price changes
        minPriceInput.addEventListener('input', function() {
            const minValue = Math.max(0, Number(this.value) || 0);
            maxPriceInput.min = minValue;
            
            // Validate and adjust if needed
            if (minValue > Number(maxPriceInput.value)) {
                maxPriceInput.value = minValue;
            }
        });
        
        // Update min price max value when max price changes
        maxPriceInput.addEventListener('input', function() {
            const maxValue = Math.min(10000, Math.max(Number(minPriceInput.value), Number(this.value) || 1000));
            minPriceInput.max = maxValue;
            
            // Validate and adjust if needed
            if (maxValue < Number(minPriceInput.value)) {
                minPriceInput.value = maxValue;
            }
        });
        
        // Add visual feedback for invalid ranges
        function validatePriceRange() {
            const min = Number(minPriceInput.value);
            const max = Number(maxPriceInput.value);
            const isValid = min <= max && min >= 0 && max <= 10000;
            
            minPriceInput.classList.toggle('border-red-300', !isValid);
            maxPriceInput.classList.toggle('border-red-300', !isValid);
            
            // Show/hide error message
            let errorSpan = document.querySelector('.price-error-message');
            if (!errorSpan) {
                errorSpan = document.createElement('span');
                errorSpan.className = 'price-error-message text-xs text-red-500 ml-2';
                maxPriceInput.parentNode.appendChild(errorSpan);
            }
            
            if (!isValid) {
                errorSpan.textContent = 'Invalid price range';
            } else {
                errorSpan.textContent = '';
            }
        }
        
        minPriceInput.addEventListener('input', validatePriceRange);
        maxPriceInput.addEventListener('input', validatePriceRange);
        
        // Initial validation
        validatePriceRange();
    }
});
</script>
