# Smart Cleaner

Keep your WordPress database lean by finding and removing orphaned `postmeta` rows safely from the admin panel.

## Why Smart Cleaner?

Over time, plugins, imports, and deleted content can leave behind metadata that no longer belongs to any post. This plugin helps you:

- Detect orphaned rows in `wp_postmeta`.
- Review entries before deletion.
- Delete in small batches (safer on large sites).
- Remove all orphaned rows in one action when needed.

## Features

- Admin page under **Tools -> Orphaned Postmeta**.
- Paginated orphaned rows view (50 rows per page).
- Batch delete for current page.
- Full delete for all orphaned rows.
- Nonce-protected destructive actions.
- GitHub-based plugin update checks via `plugin-update-checker`.

## Requirements

- WordPress with admin access.
- PHP and MySQL versions supported by your WordPress installation.

## Installation

### Standard

1. Copy the plugin folder into `wp-content/plugins/smartcleaner`.
2. Activate **Smart Cleaner** from the WordPress Plugins screen.
3. Open **Tools -> Orphaned Postmeta**.

### Development (Composer)

```bash
composer install
```

This installs `yahnis-elsts/plugin-update-checker` used for update checks.

## Usage

1. Go to **Tools -> Orphaned Postmeta**.
2. Review the table of orphaned meta rows.
3. Choose one of the cleanup actions:
	- **Delete This Batch**: deletes only the currently listed page.
	- **Delete All Orphaned Postmeta**: deletes all orphaned rows.

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
