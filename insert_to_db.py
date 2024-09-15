#! /usr/bin/env python3

import sqlite3
import csv

# Define the path to your CSV file and SQLite database
csv_file = 'site.csv'
db_file = 'directory.db'

# Connect to the SQLite database
conn = sqlite3.connect(db_file)
cursor = conn.cursor()

# Open the CSV file and read data
with open(csv_file, 'r', newline='', encoding='utf-8') as infile:
    reader = csv.DictReader(infile)

    # Prepare the insert query, skipping last_status and approved
    insert_query = '''
    INSERT INTO sites (name, url, description, category, approved)
    VALUES (?, ?, ?, ?, ?)
    '''

    for row in reader:
        # Extract relevant fields and ignore UUID
        name = row['Title']
        url = row['URL']
        description = row['Description']
        category = row['Categories']

        # Execute the insert query with the extracted data
        cursor.execute(insert_query, (name, url, description, category, 1))

    # Commit the changes
    conn.commit()

# Close the database connection
conn.close()

print(f"Data imported from {csv_file} to {db_file} successfully.")
