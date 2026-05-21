<?php
if (!isset($sites) || !is_array($sites)) {
    echo "<p>No Site in Database or None that match the search.</p>";
    return;
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/partials/_functions.php");
// Check if we should show action buttons
$showActions = $showActions ?? false;
$actionType = $actionType ?? 'approved'; // 'pending' or 'approved'
$paginationLinks = paginate($pg_no, $totalCount);

?>

<head>
    <link rel="stylesheet" href="/css/table.css">
</head>


<table>
    <thead>
        <tr>
            <th>Name</a></th>
            <th>URL</a></th>
            <th>Category</th>
            <th>Last Status</th>
            <th>Last Check</th>
            <th>Last Active</th>
            <?php if ($showActions): ?>
                <th>Actions</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($sites as $site): ?>
            <tr>
                <td>
                    <details>
                        <summary><?= htmlspecialchars($site['name']) ?></summary>
                        <p><?= htmlspecialchars($site['description']) ?></p>
                        <?php if (!empty($site['additional_info'])): ?>
                            <p><strong>Note:</strong> <?= htmlspecialchars($site['additional_info']) ?></p>
                        <?php endif; ?>
                    </details>
                </td>
                <td><a href="<?= htmlspecialchars($site['url']) ?>"><?= htmlspecialchars($site['url']) ?></a></td>
                <td><?= htmlspecialchars(implode(', ', $site['categories'])) ?></td>
                <td><?= htmlspecialchars($site['last_status']) ?></td>
                <td><?= htmlspecialchars($site['last_check']) ?></td>
                <td><?= htmlspecialchars($site['last_active']) ?></td>
                <?php if ($showActions): ?>
                    <td class="action-buttons">
                        <?php if ($actionType === 'pending'): ?>
                            <!-- Pending sites actions (4 buttons) -->
                            <div class="action-group top-row">
                                <form action="admin.php" method="post" class="action-form">
                                    <input type="hidden" name="check" value="<?= $site['id'] ?>">
                                    <button type="submit" id="btn-check">Check Now</button>
                                </form>
                                <form action="admin.php" method="post" class="action-form">
                                    <input type="hidden" name="edit" value="<?= $site['id'] ?>">
                                    <button type="submit" id="btn-edit">Edit</button>
                                </form>
                            </div>
                            <div class="action-group bottom-row">
                                <form action="admin.php" method="post" class="action-form">
                                    <input type="hidden" name="approve" value="<?= $site['id'] ?>">
                                    <button type="submit" id="btn-approve">Approve</button>
                                </form>
                                <form action="admin.php" method="post" class="action-form">
                                    <input type="hidden" name="delete" value="<?= $site['id'] ?>">
                                    <button type="submit" id="btn-delete" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <!-- Approved sites actions (3 buttons - first row has 2, second row has 1 centered) -->
                            <div class="action-group top-row">
                                <form action="admin.php" method="post" class="action-form">
                                    <input type="hidden" name="check" value="<?= $site['id'] ?>">
                                    <button type="submit" id="btn-check">Check Now</button>
                                </form>
                                <form action="admin.php" method="post" class="action-form">
                                    <input type="hidden" name="edit" value="<?= $site['id'] ?>">
                                    <button type="submit" id="btn-edit">Edit</button>
                                </form>
                            </div>
                            <div class="action-group bottom-row single">
                                <form action="admin.php" method="post" class="action-form">
                                    <input type="hidden" name="delete" value="<?= $site['id'] ?>">
                                    <button type="submit" id="btn-delete" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!--Output pagination links -->
<div class="pagination_container">
    <div class="pagination">
        <?= $paginationLinks ?>
    </div>
</div>
