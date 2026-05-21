-- Create the main sites table
CREATE TABLE IF NOT EXISTS sites (
    id INTEGER PRIMARY KEY,
    name TEXT,
    url TEXT,
    description TEXT,
    last_status TEXT DEFAULT 'unchecked',
    last_check TEXT DEFAULT 'unchecked',
    last_active TEXT DEFAULT 'unchecked',
    approved INTEGER DEFAULT 0,
    click_count INTEGER DEFAULT 0
);

-- Create the categories table
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY,
    name TEXT UNIQUE COLLATE NOCASE
);

-- Create the site_categories table to manage many-to-many relationship
CREATE TABLE IF NOT EXISTS site_categories (
    site_id INTEGER,
    category_id INTEGER,
    FOREIGN KEY (site_id) REFERENCES sites(id),
    FOREIGN KEY (category_id) REFERENCES categories(id),
    PRIMARY KEY (site_id, category_id)
);

-- Create the additional_info table for notes about sites
CREATE TABLE IF NOT EXISTS additional_info (
    id INTEGER PRIMARY KEY,
    site_id INTEGER,
    info TEXT,
    FOREIGN KEY (site_id) REFERENCES sites(id)
);

