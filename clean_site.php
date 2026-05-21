<?php
$root = $_SERVER['DOCUMENT_ROOT'];
require_once($root . "/partials/_functions.php");
require_once($root . "/partials/_dbconnect.php");

$sites = $db->query("SELECT id, last_status, last_check, last_active FROM sites WHERE approved = 1")->fetchAll(PDO::FETCH_ASSOC);

// Ensure categories exist
$categoryMap = ['yellow' => null, 'red' => null, 'archive' => null];
foreach ($categoryMap as $name => $_) {
    $stmt = $db->prepare("INSERT OR IGNORE INTO categories (name) VALUES (?)");
    $stmt->execute([$name]);

    $stmt = $db->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute([$name]);
    $categoryMap[$name] = $stmt->fetchColumn();
}

foreach ($sites as $site) {
    if ($site['last_status'] === 'active') continue;

    $lastActive = strtotime($site['last_active']);
    if (!$lastActive) continue;

    $now = time();
    $diff = $now - $lastCheck;

    $newCategory = null;
    if ($diff > 60 * 60 * 24 * 90) {
        $newCategory = 'archive';
    } elseif ($diff > 60 * 60 * 24 * 60) {
        $newCategory = 'red';
    } elseif ($diff > 60 * 60 * 24 * 14) {
        $newCategory = 'yellow';
    }

    if ($newCategory) {
        // Remove old status categories
        $stmt = $db->prepare("
            DELETE FROM site_categories 
            WHERE site_id = ? AND category_id IN (" . implode(",", $categoryMap) . ")
        ");
        $stmt->execute([$site['id']]);

        // Insert new
        $stmt = $db->prepare("INSERT OR IGNORE INTO site_categories (site_id, category_id) VALUES (?, ?)");
        $stmt->execute([$site['id'], $categoryMap[$newCategory]]);
    }
}

