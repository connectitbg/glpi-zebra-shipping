# GLPI Zebra Shipping 0.2.9 — Test Report

Date: 2026-11-21

## Scope

Version 0.2.9 adds an optional ZPL label template editor for installations with a valid Professional license. Demo installations display a disabled editor preview and upgrade notice. The GLPI controller checks the license before saving a template, and the Print Service independently rejects custom templates unless the license is Professional.

## Verification

- PHP syntax checks passed for the new `ZplTemplate` class, configuration and print controllers, plugin setup, and uninstall hook, both locally and on the isolated GLPI 11.0.9 test environment.
- The Print Service test suite passed: 5 tests covering supported placeholders, invalid templates, substitutions and input sanitization, demo edition enforcement, and the demo mark on the built-in label.
- The Print Service health endpoint returned `ok` with service version 0.2.4.
- GLPI reported plugin version 0.2.9 as enabled after the plugin was activated and GLPI cache was cleared. The settings route redirected unauthenticated requests to authentication, as expected.
- `git diff --check` passed.

## Not covered

- No physical label was printed as part of this change. The existing built-in label flow remains the fallback when no custom template is configured.
- The settings page was not visually inspected in an authenticated browser session during this verification run.

