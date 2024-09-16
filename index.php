<?php
$root = $_SERVER['DOCUMENT_ROOT'];
session_start();

require_once($root . "/config.php");
require_once($root . "/partials/_dbconnect.php");
require_once($root . "/partials/_functions.php");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? null;

    switch ($action) {
        case 'admin_login':
            if (isset($_GET['key']) && $_GET['key'] === ADMIN_SECRET_KEY) {
                // Log the successful match
                header("Location: admin_login.php?key=" . urlencode(ADMIN_SECRET_KEY));
                exit;
            } else {
                header("Location: index.php");
                exit;
            }
            break;

        case 'logout':
            unset($_SESSION['admin']);
            header('Location: index.php');
            exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="/css/index.css">
</head>

<body>

    <div id="header">
        <h1><?php echo TITLE; ?></h1>
        <?php
        if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            echo '<a href="index.php?action=logout">Logout</a>';
        } else {
            echo '<a href="submit_site.php">Submit a site</a>';
        }
        ?>
    </div>

    <form method="get" action="index.php">
        <input type="text" name="search" placeholder="Search by Name, URL, Description, or Category">
        <button type="submit">Search</button>
    </form>

    <?php
    include 'partials/_table.php';
    ?>


</body>

</html>
