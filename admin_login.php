<?php
$root = $_SERVER['DOCUMENT_ROOT'];
session_start();
require_once($root . "/config.php");

// Check if the correct key is present for GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['key']) || $_GET['key'] !== ADMIN_SECRET_KEY) {
        header("Location: /index.php");
        exit;
    }
}

// Handle POST request for login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $key = $_POST['key'] ?? '';

    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        // Display an error message if login failed
        echo '<p style="color:red;">Invalid username or password!</p>';
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="/css/dark-theme.css">
</head>
<body>
    <h1>Admin Login</h1>
    <form action="admin_login.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        
        <button type="submit">Login</button>
    </form>
</body>
</html>

