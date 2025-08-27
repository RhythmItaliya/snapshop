<?php
// Product Categories and Utilities - Converted from React utils.js

// Product categories constants
$PRODUCT_CATEGORIES = [
    'ALL' => 'all',
    'MEN' => 'men',
    'WOMEN' => 'women',
    'UNISEX' => 'unisex',
    'T_SHIRTS' => 't-shirts',
    'SHIRTS' => 'shirts',
    'PANTS' => 'pants',
    'JEANS' => 'jeans',
    'DRESSES' => 'dresses',
    'SKIRTS' => 'skirts',
    'JACKETS' => 'jackets',
    'HOODIES' => 'hoodies',
    'SWEATERS' => 'sweaters',
    'SHORTS' => 'shorts',
    'SALE' => 'sale',
];

// Category labels for display
$CATEGORY_LABELS = [
    'all' => 'All',
    'men' => 'Men',
    'women' => 'Women',
    'unisex' => 'Unisex',
    't-shirts' => 'T-Shirts',
    'shirts' => 'Shirts',
    'pants' => 'Pants',
    'jeans' => 'Jeans',
    'dresses' => 'Dresses',
    'skirts' => 'Skirts',
    'jackets' => 'Jackets',
    'hoodies' => 'Hoodies',
    'sweaters' => 'Sweaters',
    'shorts' => 'Shorts',
    'sale' => 'Sale',
];

// Sort options
$SORT_OPTIONS = [
    'FEATURED' => 'featured',
    'PRICE_LOW_TO_HIGH' => 'price_low_to_high',
    'PRICE_HIGH_TO_LOW' => 'price_high_to_low',
    'NEWEST' => 'newest',
    'NAME_A_TO_Z' => 'name_a_to_z'
];

// Sort labels
$SORT_LABELS = [
    'featured' => 'Featured',
    'price_low_to_high' => 'Price: Low to High',
    'price_high_to_low' => 'Price: High to Low',
    'newest' => 'Newest',
    'name_a_to_z' => 'Name: A to Z'
];

// Price ranges
$PRICE_RANGES = [
    'ALL' => [0, 10000]
];

/**
 * Get product category based on product data
 * @param array $product Product data array
 * @return string Category key
 */
function getProductCategory($product) {
    if (isset($product['discount']) && $product['discount'] > 0) {
        return $PRODUCT_CATEGORIES['SALE'];
    }
    return $product['category'] ?? $PRODUCT_CATEGORIES['ALL'];
}

/**
 * Filter products by category
 * @param array $products Array of products
 * @param string $category Category to filter by
 * @return array Filtered products
 */
function filterProductsByCategory($products, $category) {
    if ($category === $PRODUCT_CATEGORIES['ALL']) {
        return $products;
    }

    switch ($category) {
        case $PRODUCT_CATEGORIES['MEN']:
            return array_filter($products, function($item) {
                return strtolower($item['gender'] ?? '') === 'men';
            });
        case $PRODUCT_CATEGORIES['WOMEN']:
            return array_filter($products, function($item) {
                return strtolower($item['gender'] ?? '') === 'women';
            });
        case $PRODUCT_CATEGORIES['UNISEX']:
            return array_filter($products, function($item) {
                return strtolower($item['gender'] ?? '') === 'unisex';
            });
        case $PRODUCT_CATEGORIES['T_SHIRTS']:
            return array_filter($products, function($item) {
                return strtolower($item['category'] ?? '') === 't-shirts';
            });
        case $PRODUCT_CATEGORIES['SHIRTS']:
            return array_filter($products, function($item) {
                return strtolower($item['category'] ?? '') === 'shirts';
            });
        case $PRODUCT_CATEGORIES['PANTS']:
            return array_filter($products, function($item) {
                return strtolower($item['category'] ?? '') === 'pants';
            });
        case $PRODUCT_CATEGORIES['JEANS']:
            return array_filter($products, function($item) {
                return strtolower($item['category'] ?? '') === 'jeans';
            });
        case $PRODUCT_CATEGORIES['DRESSES']:
            return array_filter($products, function($item) {
                return strtolower($item['category'] ?? '') === 'dresses';
            });
        case $PRODUCT_CATEGORIES['SKIRTS']:
            return array_filter($products, function($item) {
                return strtolower($item['category'] ?? '') === 'skirts';
            });
        case $PRODUCT_CATEGORIES['JACKETS']:
            return array_filter($products, function($item) {
                return strtolower($item['category'] ?? '') === 'jackets';
            });
        case $PRODUCT_CATEGORIES['HOODIES']:
            return array_filter($products, function($item) {
                return strtolower($item['category'] ?? '') === 'hoodies';
            });
        case $PRODUCT_CATEGORIES['SWEATERS']:
            return array_filter($products, function($item) {
                return strtolower($item['category'] ?? '') === 'sweaters';
            });
        case $PRODUCT_CATEGORIES['SHORTS']:
            return array_filter($products, function($item) {
                return strtolower($item['category'] ?? '') === 'shorts';
            });
        case $PRODUCT_CATEGORIES['SALE']:
            return array_filter($products, function($item) {
                return isset($item['discount']) && $item['discount'] > 0;
            });
        default:
            return $products;
    }
}

/**
 * Filter products by price range
 * @param array $products Array of products
 * @param array $priceRange [min, max] price range
 * @return array Filtered products
 */
function filterProductsByPrice($products, $priceRange) {
    return array_filter($products, function($product) use ($priceRange) {
        $price = $product['price'] ?? 0;
        return $price >= $priceRange[0] && $price <= $priceRange[1];
    });
}

/**
 * Sort products by various criteria
 * @param array $products Array of products
 * @param string $sortBy Sort criteria
 * @return array Sorted products
 */
function sortProducts($products, $sortBy) {
    switch ($sortBy) {
        case 'price_low_to_high':
            usort($products, function($a, $b) {
                return ($a['price'] ?? 0) - ($b['price'] ?? 0);
            });
            break;
        case 'price_high_to_low':
            usort($products, function($a, $b) {
                return ($b['price'] ?? 0) - ($a['price'] ?? 0);
            });
            break;
        case 'newest':
            usort($products, function($a, $b) {
                return strtotime($b['created_at'] ?? '') - strtotime($a['created_at'] ?? '');
            });
            break;
        case 'name_a_to_z':
            usort($products, function($a, $b) {
                return strcmp($a['name'] ?? '', $b['name'] ?? '');
            });
            break;
        default: // featured - keep original order
            break;
    }
    return $products;
}
?>
