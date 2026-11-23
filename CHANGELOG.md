# Changelog

## 0.2.12 — 2026-11-23

- Fixed a confirmed bug: the Notes field was accepted and stored by GLPI but omitted from the built-in ZPL label renderer. Notes now print in a bounded, wrapped section; oversized notes receive a clear fit error.
- Added a label quantity selector (1–100) and service-side validation. The Print Service sends the selected complete ZPL label format repeatedly in one raw TCP job.
- Bumped the plugin to 0.2.12 and Print Service to 0.2.5.

## 0.2.11 — 2026-11-22

- Declared GLPI 11.x and 12.x compatibility (`^11.0 || ^12.0`) and verified installation/use on GLPI 12.0.2.
- Recorded the latest test print job and its limits in [test report 0.2.11](docs/TEST_REPORT_0.2.11.md).

## Earlier releases

See [test and release reports](docs/) and the version history in `zebrashipping.xml`.
