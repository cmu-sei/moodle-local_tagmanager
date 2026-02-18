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

The actions column of the table has been extended to include a download icon that enables the user to export a csv file containing the tags within each collection.

### Importing tag collections

On the tag management page there is now a Tag Manager section with an upload form on the bottom of the page. This form uses the Moodle file manager to upload a csv file to an existing tag collection.

Inside each collection's edit page, there is now a button that allows the user to "Import standard tags". This will take the user to a new page that also renders a form that uses the Moodle file manager to upload a csv file.

Existing tags will not be overwritten.

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



