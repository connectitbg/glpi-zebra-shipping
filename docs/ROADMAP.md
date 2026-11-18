# Rebuild plan

## Stage 1 — Foundation complete; hardware validation pending

- [x] Set a stable plugin name and repository structure.
- [x] Add service token authentication, an empty printer registry, raw TCP output, job metadata, and ZPL input escaping.
- [x] Add Professional Ed25519 signing/verifying utilities and persistent 14-day first-install state.
- [x] Validate Python dependencies and GLPI 11.0.9 installation/migration in the supplied test clone.
- [x] Validate demo limits, printer configuration, mock TCP delivery, idempotency, and failed-job accounting without contacting a physical printer.
- [x] Verify network connectivity and send one synthetic Cyrillic DEMO label to the supplied ZD421 test printer (identified as 203 dpi).
- [ ] Visually confirm the physical print's Cyrillic glyphs, fit, and media size.

## Stage 2 — GLPI integration implemented

- [x] Implement supported asset and ticket workflows using GLPI 11 APIs.
- [x] Add printer selection, destination defaults from entity hierarchy, authorization, CSRF-compliant requests, and history.
- [x] Use GLPI database abstractions for reads/writes; install the plugin and validate its migration on the test clone.
- [x] Confirm through the supplied administrator screenshots that settings and computer/ticket shipping forms render.
- [x] Upgrade the test clone from 0.2.1 to 0.2.2 and normalize missing inventory/serial placeholders.
- [ ] Validate upgrades from every previously released schema.

## Stage 3 — Commercial readiness

- Confirm code/dependency ownership and commercial distribution rights.
- Keep the license signing key in a private operator environment.
- [x] Add basic license administration, professional activation, demo restrictions, and trial persistence.
- [x] Document offline signing-key operations, licensing, installation, and secret handling.
- [ ] Finalize transfer/reissue policy and test upgrade behavior across all release schemas.
- [ ] Resolve the community-catalog open-source requirement versus the chosen proprietary license.
- [ ] Review dependency ownership and commercial distribution rights.
- [x] Build a clean versioned plugin archive (0.2.2) and Print Service archive (0.2.0).

## Stage 4 — Validation and publication

- [x] Verify installation on the supplied GLPI 11.0.9 test clone using a local mock TCP receiver; no physical print was sent.
- [x] Verify a real ZD421 test printer over raw TCP with a synthetic label; GLPI reports the job as sent.
- [ ] Confirm the printed label visually; a successful TCP send alone does not confirm print quality.
- Review security, GLPI coding standards, privacy/logging, backup/restore, and migration paths.
- Publish source/archive according to the chosen commercial model.
- Submit the plugin catalog entry; request separate Marketplace review from GLPI.

No production configuration was changed. The workspace does not contain the production source or production database; the supplied test clone contains cloned deployment state and was used only for installation/migration and mock-printer validation.
