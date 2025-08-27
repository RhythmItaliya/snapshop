<?php
// Test file to check if mod_rewrite is working
echo "Mod_rewrite test successful!";
echo "<br>Current URL: " . $_SERVER['REQUEST_URI'];
echo "<br>Script Name: " . $_SERVER['SCRIPT_NAME'];
echo "<br>PHP Self: " . $_SERVER['PHP_SELF'];
?>
