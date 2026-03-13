== Changelog ==

1.0.3 - 13.03.2026
- Added a custom SVG admin menu icon for the Smart Cleaner top-level menu.
- Added npm/webpack build tooling under `build/` for plugin asset development.

1.0.2 - 12.03.2026
- Added a top-level Smart Cleaner admin menu with Overview and Orphaned Postmeta pages.
- Added orphaned meta key filtering with counts.
- Added filtered batch and full deletion for selected orphaned meta keys.
- Preserved filters across pagination and reset actions.

1.0.1 - 12.03.2026
- Detects orphaned rows in `postmeta` where the related post no longer exists.
- Admin interface shows orphaned postmeta records with IDs, meta keys, and meta values.
- Includes batch and full-delete options for orphaned postmeta cleanup.