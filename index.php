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
        <div id="header-links">
            <?php
            if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
                echo '<a href="index.php?action=logout">Logout</a>';
                echo '<a href="/admin.php">Admin Panel</a>';
            } else {
                echo '<a href="submit_site.php">Submit a site</a>';
            }
            ?>
        </div>
    </div>

    <div class="search-container">
        <form id="search_bar" method="get" action="index.php">
            <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                placeholder="Search by Name, URL, Description, or Category">
            <button type="submit">Search</button>
        </form>

        <div class="sort-bar">
            <span class="sort-label">Sort by:</span>
            <div class="sort-options">
                <?php
                $sortOptions = [
                    'name' => 'Name',
                    'url' => 'URL',
                    'last_status' => 'Status',
                    'last_check' => 'Last Check',
                    'last_active' => 'Last Active'
                ];

                // Get current sorts
                $currentSorts = $_GET['sort'] ?? [];
                $currentDirs = $_GET['dir'] ?? [];

                if (!is_array($currentSorts)) {
                    $currentSorts = [$currentSorts];
                    $currentDirs = [$currentDirs];
                }

                foreach ($sortOptions as $key => $label):
                    $columnIndex = array_search($key, $currentSorts);
                    $isActive = ($columnIndex !== false);
                    $currentDir = $isActive ? $currentDirs[$columnIndex] : 'ASC';
                    $newDir = ($isActive && $currentDir === 'ASC') ? 'DESC' : 'ASC';
                    $sortIndicator = '';

                    if ($isActive) {
                        $sortIndicator = ($currentDir === 'ASC') ? ' ↑' : ' ↓';
                        $priority = $columnIndex + 1;
                        $sortIndicator .= " [{$priority}]";
                    }
                ?>
                    <a href="<?= buildSortLink($key, $newDir) ?>" class="sort-option <?= $isActive ? 'active' : '' ?>">
                        <?= $label . $sortIndicator ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($currentSorts)): ?>
                <a href="?<?= http_build_query(array_diff_key($_GET, ['sort' => [], 'dir' => [], 'page' => 1])) ?>" class="clear-sort">
                    Clear
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php
    $pg_no = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
    $offset = ($pg_no - 1) * ITEMS_PER_PAGE;

    if (!empty($_GET['q'])) {
        $filters = parseSearchQuery($_GET['q']);
    }
    $filters['approved'] = 1;

    $result = getSites($filters, $offset);
    $sites = $result['sites'];
    $totalCount = $result['total'];

    include 'partials/_table.php';
    ?>


</body>

</html>
