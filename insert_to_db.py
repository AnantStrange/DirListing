#! /usr/bin/env python3

import csv
import sqlite3

# Connect to the database
conn = sqlite3.connect('directory.db')
cursor = conn.cursor()

# Read the CSV file
with open('site.csv', 'r') as file:
    csv_reader = csv.DictReader(file)
    
    for row in csv_reader:
        # Extract data from CSV row
        url = row['URL']
        name = row['Title']
        description = row['Description']
        categories = row['Categories'].split(',')
        
        # Insert into the 'sites' table (default values for missing columns)
        cursor.execute('''
            INSERT INTO sites (name, url, description)
            VALUES (?, ?, ?)
        ''', (name, url, description))
        
        # Get the site_id of the newly inserted site
        site_id = cursor.lastrowid
        
        # Insert categories into the 'categories' table if they don't already exist
        for category in categories:
            cursor.execute('''
                INSERT OR IGNORE INTO categories (name)
                VALUES (?)
            ''', (category,))
            
            # Get the category_id (either from existing or newly inserted category)
            cursor.execute('SELECT id FROM categories WHERE name = ?', (category,))
            category_id = cursor.fetchone()[0]
            
            # Insert into the 'site_categories' table (many-to-many relationship)
            cursor.execute('''
                INSERT INTO site_categories (site_id, category_id)
                VALUES (?, ?)
            ''', (site_id, category_id))

# Commit and close the connection
conn.commit()
conn.close()

