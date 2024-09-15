<?php

function addSite($name, $url, $description, $category, $approved = 0) {
    global $db;
    $stmt = $db->prepare('INSERT INTO sites (name, url, description, category, approved) VALUES (:name, :url, :description, :category, :approved)');
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':url', $url, SQLITE3_TEXT);
    $stmt->bindValue(':description', $description, SQLITE3_TEXT);
    $stmt->bindValue(':category', $category, SQLITE3_TEXT);
    $stmt->bindValue(':approved', $approved, SQLITE3_INTEGER);
    $stmt->execute();
}

function editSite($id, $name, $url, $description, $category) {
    global $db;
    $stmt = $db->prepare('UPDATE sites SET name = :name, url = :url, description = :description, category = :category WHERE id = :id');
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':url', $url, SQLITE3_TEXT);
    $stmt->bindValue(':description', $description, SQLITE3_TEXT);
    $stmt->bindValue(':category', $category, SQLITE3_TEXT);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();
}

function checkSite($id) {
    global $db;
    
    // Simple check for demonstration (you can replace this with more advanced logic)
    $stmt = $db->prepare('SELECT url FROM sites WHERE id = :id');
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    $url = $result['url'];
    $status = 'active';  // Assume the site is active (replace this with actual checking logic)

    $stmt = $db->prepare('UPDATE sites SET last_status = :status, last_check = :last_check WHERE id = :id');
    $stmt->bindValue(':status', $status, SQLITE3_TEXT);
    $stmt->bindValue(':last_check', date('Y-m-d H:i:s'), SQLITE3_TEXT);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
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

function getSites($approved = 1, $search = '', $sort = 'name') {
    global $db;
    $query = 'SELECT * FROM sites WHERE approved = :approved';
    
    // Add search functionality
    if (!empty($search)) {
        $query .= ' AND (name LIKE :search OR url LIKE :search OR description LIKE :search OR category LIKE :search)';
    }
    
    // Add sorting functionality
    $allowedSorts = ['name', 'url', 'description', 'category'];
    if (in_array($sort, $allowedSorts)) {
        $query .= ' ORDER BY ' . $sort;
    } else {
        $query .= ' ORDER BY name';
    }

    $stmt = $db->prepare($query);
    $stmt->bindValue(':approved', $approved, SQLITE3_INTEGER);
    if (!empty($search)) {
        $stmt->bindValue(':search', '%' . $search . '%', SQLITE3_TEXT);
    }

    $result = $stmt->execute();
    $sites = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $sites[] = $row;
    }
    return $sites;
}

?>
