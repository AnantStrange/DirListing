<!-- views/home.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directory Listing</title>
    <link rel="stylesheet" href="/css/dark-theme.css">
</head>
<body>
    <h1>Directory Listing</h1>
    
    <form method="get" action="index.php">
        <input type="text" name="search" placeholder="Search by Name, URL, Description, or Category">
        <button type="submit">Search</button>
    </form>

    <table>
        <thead>
            <tr>
                <th><a href="index.php?sort=name">Name</a></th>
                <th><a href="index.php?sort=url">URL</a></th>
                <th><a href="index.php?sort=description">Description</a></th>
                <th><a href="index.php?sort=category">Category</a></th>
                <th>Last Status</th>
                <th>Last Check</th>
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
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="index.php?action=submit">Submit a site</a>
</body>
</html>

