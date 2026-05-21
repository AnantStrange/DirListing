<?php
$root = $_SERVER['DOCUMENT_ROOT'];
require_once($root . "/config.php");
require_once($root . "/partials/_dbconnect.php");

function addSite($name, $url, $description, $categories, $approved = 0)
{
    global $db;

    // Start transaction to ensure both site and categories are inserted together
    $db->beginTransaction();

    // Insert site
    $stmt = $db->prepare('INSERT INTO sites (name, url, description, approved) VALUES (:name, :url, :description, :approved)');
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':url', $url, PDO::PARAM_STR);
    $stmt->bindValue(':description', $description, PDO::PARAM_STR);
    $stmt->bindValue(':approved', $approved, PDO::PARAM_INT);
    $stmt->execute();

    $siteId = $db->lastInsertId();

    // Handle categories: insert if not exists and link to site
    $categories = explode(',', $categories);
    foreach ($categories as $categoryName) {
        $categoryName = trim($categoryName);

        // Insert category if it doesn't exist
        $stmt = $db->prepare('INSERT INTO categories (name) VALUES (:name) ON CONFLICT(name) DO NOTHING');
        $stmt->bindValue(':name', $categoryName, PDO::PARAM_STR);
        $stmt->execute();

        // Get the category id
        $stmt = $db->prepare('SELECT id FROM categories WHERE name = :name');
        $stmt->bindValue(':name', $categoryName, PDO::PARAM_STR);
        $stmt->execute();
        $categoryId = $stmt->fetchColumn();

        // Insert into site_categories
        $stmt = $db->prepare('INSERT INTO site_categories (site_id, category_id) VALUES (:site_id, :category_id)');
        $stmt->bindValue(':site_id', $siteId, PDO::PARAM_INT);
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();
    }

    $db->commit();
}


function editSite($id, $name, $url, $description, $category)
{
    global $db;  // Use PDO connection
    $stmt = $db->prepare('UPDATE sites SET name = :name, url = :url, description = :description, category = :category WHERE id = :id');

    $stmt->execute([
        'name' => $name,
        'url' => $url,
        'description' => $description,
        'category' => $category,
        'id' => $id
    ]);
}

function approveSite($id)
{
    global $db;
    $stmt = $db->prepare('UPDATE sites SET approved = 1 WHERE id = :id');
    $stmt->execute(['id' => $id]);
}

function approveAll()
{
    global $db;
    $db->exec('UPDATE sites SET approved = 1 WHERE approved = 0');
}

function deleteSite($id)
{
    global $db;
    $stmt = $db->prepare('DELETE FROM sites WHERE id = :id');
    $stmt->execute(['id' => $id]);
}


// Admin authentication
function isAdmin()
{
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}

function getCategories(): array
{
    global $db;
    $stmt = $db->query("select name from categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return array_map(function ($category) {
        return $category['name'];
    }, $categories);
}

/**
 * Parses a search query string into filter criteria for site search.
 * 
 * Supports two syntax formats:
 * 1. General search terms: "php tutorial" - searches across name, url, description, category
 * 2. Field-specific filters: "category:backend, status:up" - filters by specific fields
 * 
 * Aliases supported:
 * - 'cat' or 'category' -> 'category'
 * - 'desc' or 'description' -> 'description'  
 * - 'last_status' or 'status' -> 'last_status'
 *
 * @param string $query The search query string (e.g., "php tutorial, category:backend, status:up")
 * @return array<string, string> Associative array of filters:
 *                               - 'search' => string   General search term
 *                               - 'name' => string     Filter by site name
 *                               - 'url' => string      Filter by URL
 *                               - 'description' => string Filter by description
 *                               - 'category' => string Filter by category name
 *                               - 'last_status' => string Filter by last status (up/down/error)
 * @example
 *   parseSearchQuery("php, category:backend") 
 *   -> ['search' => 'php', 'category' => 'backend']
 *   
 *   parseSearchQuery("name:Google, status:up")
 *   -> ['name' => 'Google', 'last_status' => 'up']
 */
function parseSearchQuery($query)
{
    // Define aliases mapping
    $aliasMap = [
        'cat' => 'category',
        'category' => 'category',
        'desc' => 'description',
        'description' => 'description',
        'last_status' => 'last_status',
        'status' => 'last_status'
    ];

    $filters = [];
    $query = trim($query ?? '');
    $query = strtolower($query); // Convert everything to lowercase

    if ($query === '') {
        return $filters;
    }

    $parts = array_map('trim', explode(',', $query));
    $generalTerms = [];

    foreach ($parts as $part) {
        if (str_contains($part, ':')) {
            [$key, $value] = array_map('trim', explode(':', $part, 2));
            $key = strtolower($key); // Ensure key is lowercase
            $value = strtolower(trim($value, '"\'')); // Remove quotes and lowercase

            // Map alias to proper field name
            if (isset($aliasMap[$key])) {
                $properKey = $aliasMap[$key];
            } else {
                $properKey = $key; // Use as-is if not in alias map
            }

            // Validate value is not empty
            if ($value !== '') {
                $filters[$properKey] = $value;
            }
        } else {
            // General search terms (no colon)
            if ($part !== '') {
                $generalTerms[] = $part;
            }
        }
    }

    // Handle general search (combine all general terms with spaces)
    if (!empty($generalTerms)) {
        $filters['search'] = implode(' ', $generalTerms);
    }

    return $filters;
}

/**
 * Fetches a paginated list of sites based on various filters.
 *
 * @param array $filters Filter criteria:
 *                       - string 'name'         Partial match for site name.
 *                       - string 'url'          Partial match for site URL.
 *                       - string 'description'  Partial match for site description.
 *                       - int    'approved'     Filter by approval status (0 or 1).
 *                       - string 'last_status'  Match exact last status value.
 *                       - string 'start_date'   Start date for last_check (YYYY-MM-DD).
 *                       - string 'end_date'     End date for last_check (YYYY-MM-DD).
 *                       - string 'active_start' Start date for last_active (YYYY-MM-DD).
 *                       - string 'active_end'   End date for last_active (YYYY-MM-DD).
 *                       - string 'category'     Partial match for category name.
 *                       - string 'search'       Search across name, URL, description, category.
 * @param int   $offset  Offset for pagination.
 * @param int   $limit   Number of items per page.
 *
 * @return array{
 *     sites: array,  // List of matching site entries.
 *     total: int     // Total count of matching sites.
 * }
 */
function getSites($filters = [], $offset = 0, $limit = ITEMS_PER_PAGE)
{
    global $db;

    $where = [];
    $params = [];

    // GENERAL SEARCH (across name, url, description, category)
    if (!empty($filters['search'])) {
        $where[] = '(s.name LIKE :search OR s.url LIKE :search OR s.description LIKE :search OR c.name LIKE :search)';
        $params[':search'] = '%' . $filters['search'] . '%';
    }

    // SPECIFIC FIELD FILTERS (always applied if present)
    if (isset($filters['name'])) {
        $where[] = 's.name LIKE :name';
        $params[':name'] = '%' . $filters['name'] . '%';
    }

    if (isset($filters['url'])) {
        $where[] = 's.url LIKE :url';
        $params[':url'] = '%' . $filters['url'] . '%';
    }

    if (isset($filters['description'])) {
        $where[] = 's.description LIKE :description';
        $params[':description'] = '%' . $filters['description'] . '%';
    }

    if (!empty($filters['category'])) {
        $where[] = 'c.name LIKE :category';
        $params[':category'] = '%' . $filters['category'] . '%';
    }

    // EXACT MATCH FILTERS
    if (isset($filters['approved'])) {
        $where[] = 's.approved = :approved';
        $params[':approved'] = $filters['approved'];
    }

    if (isset($filters['last_status'])) {
        $where[] = 's.last_status = :last_status';
        $params[':last_status'] = $filters['last_status'];
    }

    // DATE RANGE FILTERS
    if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
        $where[] = 's.last_check BETWEEN :start_date AND :end_date';
        $params[':start_date'] = $filters['start_date'];
        $params[':end_date'] = $filters['end_date'];
    }

    if (!empty($filters['active_start']) && !empty($filters['active_end'])) {
        $where[] = 's.last_active BETWEEN :active_start AND :active_end';
        $params[':active_start'] = $filters['active_start'];
        $params[':active_end'] = $filters['active_end'];
    }

    $whereSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    // Handle multi-column sorting
    $sortColumns = $_GET['sort'] ?? [];
    $sortDirections = $_GET['dir'] ?? [];

    // Default sort priorities if no sort specified
    if (empty($sortColumns)) {
        $sortColumns = ['last_status', 'name'];
        $sortDirections = ['ASC', 'ASC'];
    }

    // Ensure arrays
    if (!is_array($sortColumns)) {
        $sortColumns = [$sortColumns];
        $sortDirections = [$sortDirections];
    }

    // Allowed sort columns (whitelist for security)
    $allowedSorts = ['name', 'url', 'last_status', 'last_check', 'last_active', 'description'];

    // Build ORDER BY clause
    $orderByClauses = [];
    for ($i = 0; $i < count($sortColumns); $i++) {
        $column = $sortColumns[$i];
        $direction = isset($sortDirections[$i]) ? strtoupper($sortDirections[$i]) : 'ASC';
        $direction = ($direction === 'DESC') ? 'DESC' : 'ASC';

        // Only allow whitelisted columns
        if (in_array($column, $allowedSorts)) {
            $orderByClauses[] = "s.$column $direction";
        }
    }

    // Fallback if no valid sorts
    if (empty($orderByClauses)) {
        $orderByClauses[] = "s.name ASC";
    }

    $orderBySQL = 'ORDER BY ' . implode(', ', $orderByClauses);

    // Main query
    $query = "
        SELECT 
            s.*, 
            GROUP_CONCAT(DISTINCT c.name) AS categories,
            ai.info AS additional_info
        FROM sites s
        LEFT JOIN site_categories sc ON s.id = sc.site_id
        LEFT JOIN categories c ON sc.category_id = c.id
        LEFT JOIN additional_info ai ON s.id = ai.site_id
        $whereSQL
        GROUP BY s.id
        $orderBySQL
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $db->prepare($query);

    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($sites as &$site) {
        $site['categories'] = $site['categories'] ? explode(',', $site['categories']) : [];
    }

    // Count query
    $countQuery = "
        SELECT COUNT(DISTINCT s.id) as count
        FROM sites s
        LEFT JOIN site_categories sc ON s.id = sc.site_id
        LEFT JOIN categories c ON sc.category_id = c.id
        $whereSQL
    ";

    $countStmt = $db->prepare($countQuery);
    foreach ($params as $key => $val) {
        $countStmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $countStmt->execute();
    $totalCount = (int) $countStmt->fetchColumn();

    return [
        'sites' => $sites,
        'total' => $totalCount
    ];
}

/**
 * Builds a sort link that supports multi-column sorting
 * 
 * @param string $newColumn The column to add/change in sort
 * @param string $newDirection The direction for the new column
 * @return string Complete URL with sort parameters
 */
function buildSortLink(string $newColumn, string $newDirection = 'ASC'): string
{
    $query = $_GET;

    // Get existing sorts
    $existingColumns = $query['sort'] ?? [];
    $existingDirections = $query['dir'] ?? [];

    // Ensure they're arrays
    if (!is_array($existingColumns)) {
        $existingColumns = [$existingColumns];
        $existingDirections = [$existingDirections];
    }

    // Check if this column is already in the sort list
    $columnIndex = array_search($newColumn, $existingColumns);

    if ($columnIndex !== false) {
        // Column exists - toggle its direction
        $existingDirections[$columnIndex] = $newDirection;

        // Remove subsequent sorts (clear secondary sorts when changing primary)
        $existingColumns = array_slice($existingColumns, 0, $columnIndex + 1);
        $existingDirections = array_slice($existingDirections, 0, $columnIndex + 1);
    } else {
        // New column - add as secondary sort
        $existingColumns[] = $newColumn;
        $existingDirections[] = $newDirection;

        // Limit to 3 sorts max to keep URL clean
        if (count($existingColumns) > 3) {
            array_shift($existingColumns);
            array_shift($existingDirections);
        }
    }

    $query['sort'] = $existingColumns;
    $query['dir'] = $existingDirections;
    unset($query['page']); // Reset to page 1 when sorting

    $baseUrl = strtok($_SERVER["REQUEST_URI"], '?');
    return $baseUrl . '?' . http_build_query($query);
}

function paginate($currentPage = 1, $totalSites)
{
    $currentPage = max(1, (int)$currentPage);
    $totalPages = ceil($totalSites / ITEMS_PER_PAGE);
    $currentPage = min($currentPage, $totalPages);

    $url = strtok($_SERVER['REQUEST_URI'], '?');
    $queryArray = $_GET;

    $paginationLinks = [];

    if ($currentPage > 1) {
        $queryArray['page'] = $currentPage - 1;
        $paginationLinks[] = '<a href="' . $url . '?' . http_build_query($queryArray) . '">Prev</a>';
    }

    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);

    if ($currentPage + 2 > $totalPages) {
        $start = max(1, $totalPages - 4);
    }
    if ($currentPage - 2 < 1) {
        $end = min(5, $totalPages);
    }

    if ($start > 1) {
        $queryArray['page'] = 1;
        $paginationLinks[] = '<a href="' . $url . '?' . http_build_query($queryArray) . '">1</a>';
        if ($start > 2) {
            $paginationLinks[] = '...';
        }
    }

    for ($i = $start; $i <= $end; $i++) {
        if ($i == $currentPage) {
            /* $paginationLinks[] = '<strong>' . $i . '</strong>'; */
            // Preserve all existing GET parameters except 'page'
            $hiddenInputs = '';
            foreach ($queryArray as $key => $value) {
                if ($key != 'page') {
                    if (is_array($value)) {
                        // Handle array values (like sort[]=name&sort[]=url)
                        foreach ($value as $arrayValue) {
                            $hiddenInputs .= '<input type="hidden" name="' . htmlspecialchars($key) . '[]" value="' . htmlspecialchars($arrayValue) . '">';
                        }
                    } else {
                        // Handle normal string values
                        $hiddenInputs .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                    }
                }
            }

            // Create a form that submits on Enter
            $paginationLinks[] = '<form method="get" action="' . $url . '" style="display: inline-block; margin: 0; padding: 0;background-color:inherit">
            ' . $hiddenInputs . '
            <input type="number" name="page" min="1" max="' . $totalPages . '" value="' . $i . '" class="page-input">
            </form>';
        } else {
            $queryArray['page'] = $i;
            $paginationLinks[] = '<a href="' . $url . '?' . http_build_query($queryArray) . '">' . $i . '</a>';
        }
    }

    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $paginationLinks[] = '...';
        }
        $queryArray['page'] = $totalPages;
        $paginationLinks[] = '<a href="' . $url . '?' . http_build_query($queryArray) . '">' . $totalPages . '</a>';
    }

    if ($currentPage < $totalPages) {
        $queryArray['page'] = $currentPage + 1;
        $paginationLinks[] = '<a href="' . $url . '?' . http_build_query($queryArray) . '">Next</a>';
    }

    return implode(' ', $paginationLinks);
}
