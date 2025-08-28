<?php
// API Directory - Access Denied
header('HTTP/1.1 403 Forbidden');
header('Content-Type: application/json');
echo json_encode([
    'error' => 'Access denied',
    'message' => 'Direct access to API directory is not allowed'
]);
exit;
?>
