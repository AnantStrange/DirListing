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
            $category = $_POST['new_category'];  // Use new category if provided
        }
        addSite($_POST['name'], $_POST['url'], $_POST['description'], $category, 1);
    } elseif (isset($_POST['check'])) {
        checkSite($_POST['check']);
    }
}

// Get lists of pending and already added sites
$pendingSites = getSites(0);
$approvedSites = getSites(1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="/css/dark-theme.css">
</head>
<body>
    <h1>Admin Panel</h1>

    <!-- Pending Sites Section -->
    <h2>Pending Sites</h2>
    <?php if (empty($pendingSites)): ?>
        <p>No pending sites to review.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>URL</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Last Status</th>
                    <th>Last Check</th>
                    <th>Check Now</th>
                    <th>Edit</th>
                    <th>Approve</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingSites as $site): ?>
                    <tr>
                        <td><?= htmlspecialchars($site['name']) ?></td>
                        <td><a href="<?= htmlspecialchars($site['url']) ?>"><?= htmlspecialchars($site['url']) ?></a></td>
                        <td><?= htmlspecialchars($site['description']) ?></td>
                        <td><?= htmlspecialchars($site['category']) ?></td>
                        <td><?= htmlspecialchars($site['last_status']) ?></td>
                        <td><?= htmlspecialchars($site['last_check']) ?></td>
                        <td>
                            <form action="admin.php" method="post" style="display: inline;">
                                <input type="hidden" name="check" value="<?= $site['id'] ?>">
                                <button type="submit">Check Now</button>
                            </form>
                        </td>
                        <td><a href="index.php?action=edit&id=<?= $site['id'] ?>">Edit</a></td>
                        <td>
                            <form action="admin.php" method="post" style="display: inline;">
                                <input type="hidden" name="approve" value="<?= $site['id'] ?>">
                                <button type="submit">Approve</button>
                            </form>
                        </td>
                        <td>
                            <form action="admin.php" method="post" style="display: inline;">
                                <input type="hidden" name="delete" value="<?= $site['id'] ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Add New Site Section -->
    <h2>Add New Site</h2>
    <form action="admin.php" method="post">
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

    <!-- Already Added Sites Section -->
    <h2>Already Added Sites</h2>
    <?php if (empty($approvedSites)): ?>
        <p>No sites have been added yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>URL</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Last Status</th>
                    <th>Last Check</th>
                    <th>Check Now</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($approvedSites as $site): ?>
                    <tr>
                        <td><?= htmlspecialchars($site['name']) ?></td>
                        <td><a href="<?= htmlspecialchars($site['url']) ?>"><?= htmlspecialchars($site['url']) ?></a></td>
                        <td><?= htmlspecialchars($site['description']) ?></td>
                        <td><?= htmlspecialchars($site['category']) ?></td>
                        <td><?= htmlspecialchars($site['last_status']) ?></td>
                        <td><?= htmlspecialchars($site['last_check']) ?></td>
                        <td>
                            <form action="admin.php" method="post" style="display: inline;">
                                <input type="hidden" name="check" value="<?= $site['id'] ?>">
                                <button type="submit">Check Now</button>
                            </form>
                        </td>
                        <td><a href="index.php?action=edit&id=<?= $site['id'] ?>">Edit</a></td>
                        <td>
                            <form action="admin.php" method="post" style="display: inline;">
                                <input type="hidden" name="delete" value="<?= $site['id'] ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="index.php">Back to Home</a>
    <a href="index.php?action=logout">Logout</a>
</body>
</html>

