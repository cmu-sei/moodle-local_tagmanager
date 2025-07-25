# Tag Builder (local_tagbuilder) Moodle Plugin

A lightweight Moodle admin plugin for managing tags in bulk. Supports uploading tags with descriptions, exporting all existing tags, and listing tag metadata in a clean, Bootstrap-styled interface.

---

## Features

- 📥 **Upload Tags from CSV**
  - Format: `tagname,description`
  - Creates new tags (ignores duplicates)
  - Supports optional descriptions

- 📄 **List All Tags**
  - Displays tag name, ID, and description
  - Styled using Moodle’s standard `html_table` API

---

## UI Location

> **Site administration → Appearance → Tag Builder**

Or go directly to: `/local/tagbuilder/index.php`

---

## CSV Format for Upload

Each line should follow this format:

```csv
tagname,optional description
security,General security-related tag
compliance,For governance or regulatory content
```

- Commas inside the description are supported (CSV-safe)
- Description is optional
- Duplicate tag names are skipped

---

## Installation

1. Drop this folder into `local/tagbuilder`
2. Visit **Site administration → Notifications** to install
3. Navigate to **Appearance → Tag Builder** to start using it

---

## Future Ideas

- Bulk delete or merge tags
- Tag grouping or categories
- Pagination for tag list
- Drag-and-drop CSV sample template
