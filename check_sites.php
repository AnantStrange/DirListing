<?php
$root = $_SERVER['DOCUMENT_ROOT'];
require_once($root . "/partials/_functions.php");
require_once($root . "/partials/_dbconnect.php");

function checkSiteStatus($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $ok = ($httpCode >= 200 && $httpCode < 400);
    curl_close($ch);
    return $ok ? "active" : "inactive";
}

function updateSiteStatus($siteId, $status) {
    global $db;
    if ($status === 'active') {
        $stmt = $db->prepare("UPDATE sites SET last_status = ?, last_check = datetime('now'), last_active = datetime('now') WHERE id = ?");
    } else {
        $stmt = $db->prepare("UPDATE sites SET last_status = ?, last_check = datetime('now') WHERE id = ?");
    }
    $stmt->execute([$status, $siteId]);
}

// Get specific site(s) from GET or CLI args
$ids = [];
if (php_sapi_name() === "cli") {
    $ids = array_slice($argv, 1);
} elseif (isset($_GET['site_id'])) {
    $ids = [$_GET['site_id']];
}

if (!empty($ids)) {
    $placeholders = implode(",", array_fill(0, count($ids), "?"));
    $stmt = $db->prepare("SELECT * FROM sites WHERE id IN ($placeholders)");
    $stmt->execute($ids);
} else {
    $stmt = $db->query("SELECT * FROM sites");
}

$sites = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($sites as $site) {
    $status = checkSiteStatus($site['url']);
    updateSiteStatus($site['id'], $status);
    echo "Checked {$site['name']}: $status\n";
}

