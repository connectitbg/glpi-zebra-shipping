# GLPI Zebra Shipping 0.2.11 — Test Report

**Date:** 2026-11-23 (Europe/Sofia)
**Environment:** Connect IT GLPI test instance, upgraded to GLPI 12.0.2; associated local Print Service. The deployed plugin version during the recorded print test was 0.2.10. Version 0.2.11 adds the compatibility declaration and fixes the ZPL placeholder validator defect identified in the catalogue review.

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

## Catalogue review correction

- Finding: the final placeholder check searched the original template for `{{` and `}}`, so it rejected templates even when all placeholders were valid and supported.
- Correction: validate each placeholder against the allow-list, remove valid placeholder tokens for the malformed-delimiter check, and reject only leftover unmatched delimiters.
- Regression cases cover accepted supported tokens and rejection of unsupported tokens, malformed delimiters, and invalid ZPL framing.
- The exact release ZIP passed `unzip -t` on the Debian 12 GLPI 12.0.2 test host. All packaged plugin PHP files passed `php -l` under PHP 8.3.37.
- The regression script passed against the `ZplTemplate.php` extracted from the release ZIP.
- The archive was installed and enabled on the GLPI test instance. `plugin:list` reported GLPI Zebra Shipping 0.2.11 as `Enabled`.
- No physical label was printed for this validator-only correction.
