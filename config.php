<?php
// config.php

define('DB_FILE', 'directory.db');

// Admin configuration
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'password');
define('ADMIN_EMAIL', 'admin@admin.com');
define('ADMIN_SECRET_KEY', 'mewmew');

// Default categories
define('DEFAULT_CATEGORIES', ['Technology', 'Science', 'Art', 'Lifestyle']);

define("TITLE","Strange's Directory Listing");

define("ITEMS_PER_PAGE",10);

$defaultSorts = [
    ['column' => 'last_status', 'direction' => 'ASC'],  // Show 'down' sites first
    ['column' => 'name', 'direction' => 'ASC']          // Then alphabetically by name
];



?>
