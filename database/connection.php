<?php
require_once __DIR__ . '/../config/index.php';

// Connect and create database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
$conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
$conn->select_db(DB_NAME);

return $conn;
?>
