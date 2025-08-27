<?php
// Debug routing information
echo "<h2>Routing Debug Information</h2>";
echo "<h3>Server Variables:</h3>";
echo "<strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "<br>";
echo "<strong>SCRIPT_NAME:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'Not set') . "<br>";
echo "<strong>PHP_SELF:</strong> " . ($_SERVER['PHP_SELF'] ?? 'Not set') . "<br>";
echo "<strong>PATH_INFO:</strong> " . ($_SERVER['PATH_INFO'] ?? 'Not set') . "<br>";
echo "<strong>QUERY_STRING:</strong> " . ($_SERVER['QUERY_STRING'] ?? 'Not set') . "<br>";

echo "<h3>GET Parameters:</h3>";
echo "<pre>" . print_r($_GET, true) . "</pre>";

echo "<h3>URL Path Analysis:</h3>";
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$pathInfo = parse_url($requestUri, PHP_URL_PATH);
echo "<strong>Parsed Path:</strong> " . $pathInfo . "<br>";

echo "<h3>Mod_rewrite Test:</h3>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "<strong>mod_rewrite enabled:</strong> " . (in_array('mod_rewrite', $modules) ? 'Yes' : 'No') . "<br>";
} else {
    echo "<strong>mod_rewrite check:</strong> Cannot determine (function not available)<br>";
}

echo "<h3>File Existence Test:</h3>";
echo "<strong>product.php exists:</strong> " . (file_exists('product.php') ? 'Yes' : 'No') . "<br>";
echo "<strong>products.php exists:</strong> " . (file_exists('products.php') ? 'Yes' : 'No') . "<br>";
echo "<strong>.htaccess exists:</strong> " . (file_exists('.htaccess') ? 'Yes' : 'No') . "<br>";

echo "<h3>Current Directory:</h3>";
echo "<strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "<br>";
echo "<strong>Script Path:</strong> " . __FILE__ . "<br>";
?>
