# Project status and publication roadmap

## Current status

GLPI Zebra Shipping **0.2.8 is tested and working**. The public source and 0.2.7 versioned plugin archive are available under GPL-2.0-or-later; publish a 0.2.8 archive before updating the catalog XML.

- Physical printing has been demonstrated with a networked Zebra ZD421 using 93 × 71 mm labels.
- GLPI 11.0.9 installation and activation have been verified on test and production instances.
- The 0.2.7 security review findings were corrected; 0.2.8 completes uninstall cleanup by removing the named license configuration key.
- Demo behavior remains 14 days from first installation, up to two enabled printers, 50 successful print jobs, and a visible `DEMO` label mark.

See [printer compatibility](PRINTER_COMPATIBILITY.md) for the verified device and current scope.

## Printer compatibility

- **Verified:** Zebra ZD421 over raw TCP, with 93 × 71 mm media.
- **Expected:** other networked Zebra printers and other network printers that accept ZPL over raw TCP. Each model has not been separately tested.
- **USB:** compatibility is unknown and unsupported. USB printing is outside the project's network-printing scope and is not planned.

## Remaining GLPI publication steps

- Resolve the `UNAVAILABLE_NAME` response from the latest catalog submission and obtain acceptance of the XML listing.
- Confirm publisher eligibility for GLPI Marketplace review. GLPI's current guidance says the plugin must be developed by Teclib' or an official partner trained in GLPI plugin development.
- Provide sanitized screenshots and confirm the catalog metadata/logo meet the submission requirements.
- Complete independent review of GLPI coding standards, dependencies/licenses, supported-version upgrades, and privacy/logging; document a maintenance and support commitment.
- Once listed in the plugins catalog, request separate Marketplace review from GLPI at `glpi@teclib.com`.
- Decide the Professional price, payment, refund, and license transfer/reissue terms before taking payment.

Catalog listing and Marketplace approval are separate decisions; neither is guaranteed by a stable release or successful hardware test.

## Deployment

The plugin is deployed as version 0.2.7 on the owner's production GLPI and version 0.2.8 on the GLPI 11.0.9 test instance. Version 0.2.8 adds the explicit configuration key list needed to remove the stored license on uninstall. The Print Service remains a separate local component. No printer address, customer database, service token, or signing key is part of the public source or plugin archive.
