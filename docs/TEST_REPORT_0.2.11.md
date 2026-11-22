# GLPI Zebra Shipping 0.2.11 — Test Report

**Date:** 2026-11-22 (Europe/Sofia)
**Environment:** Connect IT GLPI test instance, upgraded to GLPI 12.0.2; associated local Print Service. The deployed plugin version during the print test was 0.2.10. Version 0.2.11 changes the version/compatibility metadata and documentation only.

## GLPI 12 compatibility

- The plugin was exercised in the upgraded GLPI 12.0.2 test environment and the user reported the plugin looked correct and submitted a label.
- GLPI recorded the latest print request as `sent`.
- The Print Service has a matching job record with status `sent`, no `error_code`, and no error response. Its successful path records `sent` after the TCP connection and socket `sendall` return successfully.
- The physical label output was not confirmed because the user was away from the printer. `sent` is service-level evidence, not a printer acknowledgement or visual confirmation.

## Compatibility declaration

- Declared: GLPI 11.x and 12.x (`^11.0 || ^12.0`).
- Tested for this release: GLPI 12.0.2. Earlier GLPI 11.0.9 testing remains documented in prior reports.

## Scope

This report covers the GLPI plugin and Print Service job status only. It does not certify that the label physically printed, and it includes no printer-side configuration changes.