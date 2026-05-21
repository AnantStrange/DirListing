<!-- views/submit_site.php -->
<!DOCTYPE html>
<html lang="en">

<?php
$root = $_SERVER['DOCUMENT_ROOT'];
require_once($root . "/partials/_functions.php");
$categories = getCategories();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit a Site</title>
    <link rel="stylesheet" href="css/submit_site.css">
</head>

<body>
    <h1>Submit a Site</h1>
    <?php if (isset($message)): ?>
        <p><?= $message ?></p>
    <?php endif; ?>
    <form action="submit_site.php?action=submit_site" method="post">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="url">URL:</label>
        <input type="text" id="url" name="url" required>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea>

        <label for="category">Category:</label>
        <select id="category" name="category">
            <?php foreach ($categories as $category): ?>
                <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" id="new_category" name="new_category" placeholder="Or enter a new category(ies seperated by commas)">

        <button type="submit">Submit</button>
    </form>
    <a href="index.php">Back to Home</a>
</body>

</html>

<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'submit_site') {

        $category = $_POST['category'];
        if (!empty($_POST['new_category'])) {
            $category = $_POST['new_category'];
        }
        addSite($_POST['name'], $_POST['url'], $_POST['description'], $category);
        $message = "Thank you for your submission. It will be reviewed soon.";
    }
}

?>
