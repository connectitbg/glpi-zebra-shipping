# GLPI Zebra Shipping 0.2.2 — Follow-up Test Report

**Date:** 2026-11-18  
**Environment:** Supplied GLPI 11.0.9 test clone

## Follow-up changes

- Replaced the printer setup notice with text that remains accurate after printers have been added.
- Added normalization for GLPI placeholder values such as `N/A`; those values now render as blank inventory/serial metadata in the form and label payload.
- Installed and enabled plugin version 0.2.2 on the test clone. The previously configured test printer remains in the isolated runtime registry.

## Checks

- All plugin PHP files passed `php -l`.
- Confirmed `MetadataValue::display()` converts `N/A` and empty values to blank while preserving an actual serial string.
- GLPI reports Zebra Shipping 0.2.2 as enabled.
- Existing user screenshots confirm the settings page and computer/ticket shipping forms render; the label's physical layout remains for the user's visual check tomorrow.

No printer job was sent during this follow-up.
