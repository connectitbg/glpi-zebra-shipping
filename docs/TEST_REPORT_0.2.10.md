# GLPI Zebra Shipping 0.2.10 — Test Report

Date: 2026-11-21

## Scope

This update adds a bilingual guide for the Professional custom ZPL template feature, exposes the guide from the GLPI plugin settings page, and includes the guide in the release bundle. The guide documents every placeholder accepted by the current implementation and explains how to save, test, and reset a custom template.

## Verification

- The documented placeholder set exactly matches the 12 tokens accepted by `ZplTemplate`.
- `git diff --check` passed.
- PHP syntax checks passed locally and on the isolated Debian GLPI 11.0.9 test host for the changed plugin files.
- All five focused Print Service tests passed on the Debian test host.
- The Print Service health endpoint returned `ok` with version 0.2.4.
- The GLPI and Print Service ZIP archives were built; the custom ZPL guide is included in the GLPI plugin archive and release bundle. Archive scans found none of the known test or production IP addresses or supplied test password.
- The plugin settings controller and version 0.2.10 were deployed to `/opt/glpi/plugins/zebrashipping`; the guide was copied into the plugin's `docs` directory.
- GLPI cache was cleared successfully as `www-data` after the deployment.

## Not covered

- No physical label was printed for this documentation and settings-link update.
- The new link was confirmed in the deployed controller source; it was not visually checked in an authenticated browser session during this run.
