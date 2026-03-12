# Smart Cleaner

Keep your WordPress database lean by finding and removing orphaned `postmeta` rows safely from the admin panel.

## Why Smart Cleaner?

Over time, plugins, imports, and deleted content can leave behind metadata that no longer belongs to any post. This plugin helps you:

- Detect orphaned rows in `wp_postmeta`.
- Review entries before deletion.
- Delete in small batches (safer on large sites).
- Remove all orphaned rows in one action when needed.

## Features

- Top-level admin menu under **Smart Cleaner**.
- Overview page under **Smart Cleaner -> Overview**.
- Orphaned postmeta cleanup page under **Smart Cleaner -> Orphaned Postmeta**.
- Paginated orphaned rows view (50 rows per page).
- Filter orphaned rows by `meta_key` with per-key counts.
- Batch delete for current page.
- Batch delete for the current page and selected `meta_key`.
- Full delete for all orphaned rows.
- Full delete for only the selected orphaned `meta_key`.
- Nonce-protected destructive actions.
- GitHub-based plugin update checks via `plugin-update-checker`.

## Requirements

- WordPress with admin access.
- PHP and MySQL versions supported by your WordPress installation.

## Installation

### Standard

1. Copy the plugin folder into `wp-content/plugins/smartcleaner`.
2. Activate **Smart Cleaner** from the WordPress Plugins screen.
3. Open **Smart Cleaner -> Orphaned Postmeta**.

### Development (Composer)

```bash
composer install
```

This installs `yahnis-elsts/plugin-update-checker` used for update checks.

## Usage

1. Go to **Smart Cleaner -> Orphaned Postmeta**.
2. Optionally filter the results by `meta_key` using the select field.
3. Review the table of orphaned meta rows.
4. Choose one of the cleanup actions:
	- **Delete This Batch**: deletes only the currently listed page.
	- **Delete All Orphaned Postmeta**: deletes all orphaned rows.

When a `meta_key` filter is selected, both delete actions apply only to that selected orphaned key.

## Safety Notes

- Start with batch deletion on production sites.
- Take a database backup before using full deletion.
- Test first on staging if the site has custom metadata-heavy plugins.

## How It Works

The plugin checks rows where `postmeta.post_id` no longer matches an existing `posts.ID` using SQL joins.

Core logic lives in:

- `includes/orphaned-postmeta.php`
- `smartcleaner.php`

## Updates

The plugin can check for updates from this repository:

- https://github.com/dragipostolovski/smartcleaner

Configured branch: `master`

## Changelog

### 1.0.2 (12.03.2026)

- Added a top-level **Smart Cleaner** admin menu with an overview page.
- Moved the orphaned postmeta screen under **Smart Cleaner -> Orphaned Postmeta**.
- Added `meta_key` filtering with per-key counts.
- Added filtered batch and full deletion support for a selected orphaned `meta_key`.
- Preserved active filters across pagination and reset actions.

### 1.0.1 (12.03.2026)

- Improved orphaned postmeta cleanup workflow.
- Added batch deletion support in admin.
- Added GitHub-based update checking.

## Contributing

Issues and pull requests are welcome.

If you open a PR, include:

- What changed
- Why it changed
- Any admin/UI impact
- Testing notes

## License

GPL-2.0-or-later
