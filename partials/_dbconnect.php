<?php
$root = $_SERVER['DOCUMENT_ROOT'];

// Include configuration file
require_once($root . "/config.php");
$DB_PATH = $root . "/" . DB_FILE;

// Check if the directory is writable
if (!is_writable($root)) {
    die("The directory '{$root}' is not writable. Please check the directory permissions.");
}

// Check if the database file exists and if it is writable
if (file_exists($DB_PATH) && !is_writable($DB_PATH)) {
    die("The database file '" . $DB_PATH . "' is not writable. Please check the file permissions.");
}

// Establish connection to SQLite database
try {
    $db = new SQLite3($DB_PATH);
    
    // Create the `sites` table if it doesn't exist
    $db->exec('CREATE TABLE IF NOT EXISTS sites (
        id INTEGER PRIMARY KEY,
        name TEXT,
        url TEXT,
        description TEXT,
        category TEXT,
        last_status TEXT DEFAULT "unchecked",
        last_check TEXT DEFAULT "unchecked",
        approved INTEGER DEFAULT 0
    )');
    
} catch (Exception $e) {
    die("Unable to open database: " . $e->getMessage());
}

?>
