<!-- views/submit.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit a Site</title>
    <link rel="stylesheet" href="dark-theme.css">
</head>
<body>
    <h1>Submit a Site</h1>
    <?php if (isset($message)): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <form action="index.php?action=submit" method="post">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        
        <label for="url">URL:</label>
        <input type="text" id="url" name="url" required>
        
        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea>
        
        <label for="category">Category:</label>
        <select id="category" name="category">
            <?php foreach (DEFAULT_CATEGORIES as $category): ?>
                <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" id="new_category" name="new_category" placeholder="Or enter a new category">
        
        <button type="submit">Submit</button>
    </form>
    <a href="index.php">Back to Home</a>
</body>
</html>

