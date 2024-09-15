<?php
$root = $_SERVER['DOCUMENT_ROOT'];
session_start();

require_once($root . "/config.php");
require_once($root . "/partials/_dbconnect.php");
require_once($root . "/partials/_functions.php");

// Routing
$action = $_GET['action'] ?? 'home';

switch ($action) {
    case 'home':
        $approvedSites = getSites(1);
        include 'views/home.php';
        break;
    case 'submit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $category = $_POST['category'];
            if (!empty($_POST['new_category'])) {
                $category = $_POST['new_category'];
            }
            addSite($_POST['name'], $_POST['url'], $_POST['description'], $category);
            $message = "Thank you for your submission. It will be reviewed soon.";
            include 'views/submit.php';
        } else {
            include 'views/submit.php';
        }
        break;
    case 'admin_login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['username'] === ADMIN_USERNAME && $_POST['password'] === ADMIN_PASSWORD){
                $_SESSION['admin'] = true;
                header('Location: views/admin.php');
                exit;
            }
        }
        include 'views/admin_login.php';
        break;
    case 'logout':
        unset($_SESSION['admin']);
        header('Location: index.php');
        break;
    default:
        header('Location: index.php');
        break;
}
?>
