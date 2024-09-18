<?php
$root = $_SERVER['DOCUMENT_ROOT'];
require_once($root . "/config.php");

function addSite($name, $url, $description, $category, $approved = 0) {
    global $db;  // Assuming $db is your PDO connection
    $stmt = $db->prepare('INSERT INTO sites (name, url, description, category, approved) VALUES (:name, :url, :description, :category, :approved)');
    
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':url', $url, PDO::PARAM_STR);
    $stmt->bindValue(':description', $description, PDO::PARAM_STR);
    $stmt->bindValue(':category', $category, PDO::PARAM_STR);
    $stmt->bindValue(':approved', $approved, PDO::PARAM_INT);
    
    $stmt->execute();
}

function editSite($id, $name, $url, $description, $category) {
    global $db;  // Use PDO connection
    $stmt = $db->prepare('UPDATE sites SET name = :name, url = :url, description = :description, category = :category WHERE id = :id');
    
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':url', $url, PDO::PARAM_STR);
    $stmt->bindValue(':description', $description, PDO::PARAM_STR);
    $stmt->bindValue(':category', $category, PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    
    $stmt->execute();
}

function checkSite($id) {
    global $db;  // Use PDO connection

    // Get the URL for the site by its ID
    $stmt = $db->prepare('SELECT url FROM sites WHERE id = :id');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $url = $result['url'];
    
    // Assuming the site is active (replace this with actual logic for checking site status)
    $status = 'active';  
    
    // Update the site's last status and last check time
    $stmt = $db->prepare('UPDATE sites SET last_status = :status, last_check = :last_check WHERE id = :id');
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);
    $stmt->bindValue(':last_check', date('Y-m-d H:i:s'), PDO::PARAM_STR);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    
    $stmt->execute();
}


function approveSite($id) {
    global $db;
    $db->exec('UPDATE sites SET approved = 1 WHERE id = ' . $id);
}

function deleteSite($id) {
    global $db;
    $db->exec('DELETE FROM sites WHERE id = ' . $id);
}

// Admin authentication
function isAdmin() {
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}

function getSites($approved = 1, $offset = 0, $limit = ITEMS_PER_PAGE) {
    global $db;

    $query = 'SELECT * FROM sites WHERE approved = :approved LIMIT :limit OFFSET :offset';

    $stmt = $db->prepare($query);
    $stmt->bindValue(':approved', $approved, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function countSites($approved = 1) {
    global $db;  // Assuming you're using a database connection named $db

    $query = "SELECT COUNT(*) FROM sites WHERE approved = :approved";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':approved', $approved, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchColumn();
}

function paginate($currentPage = 1) {
    // Ensure current page is a positive integer
    $currentPage = max(1, (int)$currentPage);

    // Get total entries and calculate total pages
    $totalEntries = countSites();
    $totalPages = ceil($totalEntries / ITEMS_PER_PAGE);

    // Ensure current page is within bounds
    $currentPage = min($currentPage, $totalPages);

    // Generate pagination links
    $paginationLinks = [];

    // Previous page link
    if ($currentPage > 1) {
        $paginationLinks[] = '<a href="index.php?page=' . ($currentPage - 1) . '">Prev</a>';
    }

    // Determine start and end page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);

    // Adjust start and end to ensure 5 pages are visible
    if ($currentPage - 2 < 1) {
        $end = min(5, $totalPages);
    }
    if ($currentPage + 2 > $totalPages) {
        $start = max(1, $totalPages - 4);
    }

    // Add the first page link with ellipsis if needed
    if ($start > 1) {
        $paginationLinks[] = '<a href="index.php?page=1">1</a>';
        if ($start > 2) {
            $paginationLinks[] = '...';
        }
    }

    // Add page number links
    for ($i = $start; $i <= $end; $i++) {
        $paginationLinks[] = ($i == $currentPage) ? '<strong>' . $i . '</strong>' : '<a href="index.php?page=' . $i . '">' . $i . '</a>';
    }

    // Add the last page link with ellipsis if needed
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $paginationLinks[] = '...';
        }
        $paginationLinks[] = '<a href="index.php?page=' . $totalPages . '">' . $totalPages . '</a>';
    }

    // Next page link
    if ($currentPage < $totalPages) {
        $paginationLinks[] = '<a href="index.php?page=' . ($currentPage + 1) . '">Next</a>';
    }

    return implode(' ', $paginationLinks);
}



?>
