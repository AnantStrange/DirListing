<?php
$root = $_SERVER['DOCUMENT_ROOT'];
require_once($root . "/partials/_functions.php");
$approvedSites = getSites(1);
?>

<head>
    <link rel="stylesheet" href="/css/table.css">
</head>

<table>
    <thead>
        <tr>
            <th><a href="index.php?sort=name">Name</a></th>
            <th><a href="index.php?sort=url">URL</a></th>
            <th>Category</th>
            <th>Last Status</th>
            <th>Last Check</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($approvedSites as $site): ?>
            <tr>
                <td>
                    <details>
                        <summary><?= htmlspecialchars($site['name']) ?></summary>
                        <p><?= htmlspecialchars($site['description']) ?></p>
                    </details>
                </td>
                <td><a href="<?= htmlspecialchars($site['url']) ?>"><?= htmlspecialchars($site['url']) ?></a></td>
                <td><?= htmlspecialchars($site['category']) ?></td>
                <td><?= htmlspecialchars($site['last_status']) ?></td>
                <td><?= htmlspecialchars($site['last_check']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>




