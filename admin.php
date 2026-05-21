<!-- views/admin.php -->
<?php
session_start();

// Include configuration file and database connection
$root = $_SERVER['DOCUMENT_ROOT'];
require_once($root . "/config.php");
require_once($root . "/partials/_dbconnect.php");
require_once($root . "/partials/_functions.php");

// Check if user is admin
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

// Handle POST requests directly in this file
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve'])) {
        approveSite($_POST['approve']);
    } elseif (isset($_POST['delete'])) {
        deleteSite($_POST['delete']);
    } elseif (isset($_POST['add'])) {
        $category = $_POST['category'];
        if (!empty($_POST['new_category'])) {
            // Use new category if provided
            $category = $_POST['new_category'];
        }
        addSite($_POST['name'], $_POST['url'], $_POST['description'], $category, 1);
    } elseif (isset($_POST['check'])) {
        checkSite($_POST['check']);
    }
}

$arePendingSite = false;
$areAppropvedSite = false;
$pendingSites = getSites(["approved" => 0]);
$approvedSites = getSites(["approved" => 1]);
if ($pendingSites["total"] != 0) {
    $arePendingSite = true;
    $pendingSites = $pendingSites["sites"];
}
if ($approvedSites["total"] != 0) {
    $areAppropvedSite = true;
    $approvedSites = $approvedSites["sites"];
}
/* $areAppropvedSite = false; */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="/css/dark-theme.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>

<body>
    <h1 id="admin_panel_title">Admin Panel</h1>

    <!-- Pending Sites Section -->
    <?php if ($arePendingSite): ?>
        <div id="admin_pending_header">
            <h2>Pending Sites</h2>
            <form method="POST" action="admin.php">
                <button type="submit" name="approve_all">Approve All</button>
            </form>
        </div>
        <?php
        $showActions = true;
        $actionType = "pending";
        $sites = $pendingSites;
        include 'partials/_table.php';
        $showActions = false;
        ?>
    <?php endif; ?>

    <!-- Add New Site Section -->
    <h2>Add New Site</h2>
    <form id="new_site_form" action="admin.php" method="post">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="url">URL:</label>
        <input type="text" id="url" name="url" required>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea>

        <label for="category">Category:</label>
        <select id="category" name="category">
            <?php foreach (DEFAULT_CATEGORIES as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" id="new_category" name="new_category" placeholder="Or enter a new category">

        <input type="hidden" name="add" value="1">
        <button type="submit">Add Site</button>
    </form>

    <!-- Approved Sites Section -->
    <?php if ($areAppropvedSite): ?>
        <div id="admin_approved_header">
            <h2>Approved Sites</h2>
        </div>
        <?php
        $showActions = true;
        $actionType = "approved";
        $sites = $approvedSites;
        include 'partials/_table.php';
        $showActions = false;
        ?>
    <?php endif; ?>

    <a href="index.php">Back to Home</a>
    <a href="index.php?action=logout">Logout</a>
</body>

</html>
