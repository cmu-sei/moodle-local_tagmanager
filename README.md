# Tag Manager Plugin for Moodle

## Table of Contents

1. [Description](#description)
2. [Features](#features)
3. [Requirements](#requirements)
4. [Installation](#installation)
5. [Configuration](#configuration)
6. [Usage](#usage)
7. [License](#license)

## Description

A lightweight Moodle plugin for managing tags in bulk. Supports uploading tags with descriptions and exporting tags to CSV files.

## Features

- **Upload Tags from CSV**
  - Format: `tagname,description`
  - Creates new tags (ignores duplicates)
  - Supports optional descriptions

- **Export Tags to CSV**
  - Export all tags in a collection or selected tags
  - Includes tag names and descriptions

## Requirements

Moodle 4.0 or later (requires: 2022041900)

## Installation

1. Drop this folder into `local/tagmanager`
2. Navigate to **Appearance → Manage Tags** to start using it

## Configuration

N/A

## Usage

### UI Location

The functionality of the tag management page has been extended by this plugin. It adds an upload form to that main page, and also inserts a button in each collection's edit page that allows import.

> **Site administration → Appearance → Manage Tags**

Or go directly to: `/tag/manage.php`

### Exporting tag collections

The actions column of the collections table includes a download icon (⬇) for each collection. Click it to export a CSV file containing all tags in that collection.

On a collection's edit page, you can also export selected tags using the "Export selected" button.

### Importing tag collections

**From the main tag management page:**
- Each collection row has an import icon (⬆) in the actions column
- Click the import icon to go to the import page for that collection

**From a collection's edit page:**
- Click the "Import standard tags" button at the top
- This takes you to the same import page

**On the import page:**
- Use the file picker to select your CSV file
- Click "Upload tags" to import
- The page will redirect back to the tag management page with success/info messages

**Important:** Existing tags will not be overwritten. Duplicate tag names are skipped.

### CSV Format for Uploading tag collections

Each line should follow this format:

```csv
tagname,optional description
security,General security-related tag
compliance,For governance or regulatory content
```

- Commas inside the description are supported (CSV-safe)
- Description is optional
- Duplicate tag names are skipped

### Future Ideas

- Bulk delete or merge tags
- Tag grouping or categories
- Pagination for tag list
- Drag-and-drop CSV sample template

## License

Tag Manager Plugin for Moodle

Copyright 2026 Carnegie Mellon University.

NO WARRANTY. THIS CARNEGIE MELLON UNIVERSITY AND SOFTWARE ENGINEERING INSTITUTE MATERIAL IS FURNISHED ON AN "AS-IS" BASIS.
CARNEGIE MELLON UNIVERSITY MAKES NO WARRANTIES OF ANY KIND, EITHER EXPRESSED OR IMPLIED, AS TO ANY MATTER INCLUDING, BUT NOT LIMITED TO,
WARRANTY OF FITNESS FOR PURPOSE OR MERCHANTABILITY, EXCLUSIVITY, OR RESULTS OBTAINED FROM USE OF THE MATERIAL. CARNEGIE MELLON UNIVERSITY
DOES NOT MAKE ANY WARRANTY OF ANY KIND WITH RESPECT TO FREEDOM FROM PATENT, TRADEMARK, OR COPYRIGHT INFRINGEMENT.

Licensed under a GNU GENERAL PUBLIC LICENSE - Version 3, 29 June 2007-style license, please see license.txt or contact permission@sei.cmu.edu for full terms.

[DISTRIBUTION STATEMENT A] This material has been approved for public release and unlimited distribution. Please see Copyright notice for non-US Government use and distribution.

This Software includes and/or makes use of Third-Party Software each subject to its own license.

DM26-0016



