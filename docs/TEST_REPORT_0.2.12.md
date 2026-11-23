# Test and deployment report 0.2.12

**Date:** 2026-11-23  
**Plugin:** GLPI Zebra Shipping 0.2.12  
**Print Service:** 0.2.5

## Reported issue and change

The Notes field was accepted by the GLPI form and stored in job history, but the built-in ZPL renderer did not include it on the printed label. The renderer now prints sanitized, wrapped notes when they fit the configured media. If notes cannot fit, the service returns a clear error instead of silently omitting them. Professional custom templates continue to use `{{notes}}`.

The GLPI form now accepts a quantity from 1 to 100. The Print Service validates that range and sends the complete ZPL format repeatedly over the same raw TCP connection as one print job.

## Verification

- Eight Print Service unit tests passed on the Debian test host, including Notes rendering, empty Notes, quantity bounds, and repeated complete ZPL formats.
- Python bytecode compilation passed for the Print Service and its tests.
- PHP syntax checks passed for `setup.php`, `ShippingTab.php`, and `PrintController.php`.
- The test Print Service health endpoint returned `ok`, version `0.2.5`; the user confirmed the test workflow behaved correctly.
- Production GLPI CLI reports plugin version `0.2.12` as **Enabled**. The production Print Service health endpoint returned `ok`, version `0.2.5`.

## Production log review

- No error or alert entries were present in the `zebrashipping.service` journal for the deployment window.
- No error or alert entries were present in the Apache journal for the deployment window.
- GLPI `php-errors.log` and `sql-errors.log` were both empty at review time.

## Notes

Notes are limited to 500 characters in the form, but the built-in label has finite space. Very long notes can still exceed the configured media; shorten them or use a Professional custom ZPL template. The demo quota continues to count successful print jobs, so a batch of copies is one job.
