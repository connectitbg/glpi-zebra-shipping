# GLPI Zebra Shipping 0.2.1 — Test Report

**Date:** 2026-11-18  
**Environment:** Supplied GLPI 11.0.9 test clone; isolated Print Service 0.2.0  
**Printer:** Zebra ZD421, identified by the printer as 203 dpi

## Corrections made

- Replaced direct GLPI 11 `front/` settings and print actions with GLPI controller routes. The old settings URL redirects to the controller; old print forms preserve POST when redirected.
- Removed the assumption that `inc/includes.php` initializes GLPI. In GLPI 11, initialization is automatic through the core request entry point; `inc/includes.php` remains only for compatibility.
- Corrected the test clone's Apache environment selection. The old cloned Zebra configuration continued to point to its previous service, so it was disabled in the test clone and the plugin now reaches the isolated service.
- Upgraded the installed plugin to 0.2.1. No production server or physical production printer was changed.

## Verification

- PHP syntax check passed for all plugin PHP files.
- GLPI reports the plugin installed, enabled, and at version 0.2.1.
- The old settings URL redirects to `/plugins/zebrashipping/Config`; the controller route is recognized and enforces GLPI login.
- The GLPI PHP runtime successfully connected to the isolated Print Service. The service currently has the supplied test printer configured.
- The printer answered Zebra's host-identification query as `ZD421-200dpi`, confirming 203 dpi.
- Printer connectivity check passed on raw TCP port 9100.
- One synthetic Cyrillic DEMO label was sent. The service returned job status `sent`; no GLPI asset, ticket, recipient, or customer data was used.
- The earlier functional checks also passed for the 14-day/50-job/2-printer demo, idempotency, and not consuming print allowance on rejected or unreachable jobs.
- Source and release archive scans found no supplied printer address, SSH credential, production database dump, API token, or private signing key.

## Physical output still needs visual confirmation

The service's `sent` result means the ZPL bytes were accepted by the printer's TCP socket; it does not confirm the resulting paper label. Please check the printed DEMO label for readable Cyrillic, correct orientation, clipping, and fit on the loaded media. The test used a 100 × 150 mm label definition at 203 dpi.

## Test deployment state

The 0.2.1 plugin is enabled in the test clone. Its isolated Print Service remains configured with the supplied test printer so the GLPI setup page can be reviewed. The printer address exists only in the test server's runtime configuration, not in source or release archives.
