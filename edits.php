<!-- views/edit.php -->
<form action="index.php?action=edit&id=<?= $site['id'] ?>" method="post">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" value="<?= htmlspecialchars($site['name']) ?>" required>

    <label for="url">URL:</label>
    <input type="text" id="url" name="url" value="<?= htmlspecialchars($site['url']) ?>" required>

    <label for="description">Description:</label>
    <textarea id="description" name="description" required><?= htmlspecialchars($site['description']) ?></textarea>

    <label for="category">Category:</label>
    <input type="text" id="category" name="category" value="<?= htmlspecialchars($site['category']) ?>" required>

    <button type="submit">Update</button>
</form>

