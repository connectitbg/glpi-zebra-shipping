# Project status and publication roadmap

## Current status

GLPI Zebra Shipping **0.2.12 declares GLPI 11.x and 12.x compatibility**. It fixes printing of Notes in the built-in ZPL layout and adds a 1–100 label quantity selector. Version 0.2.11 fixed validation of supported placeholders in Professional ZPL templates. The source remains GPL-2.0-or-later.

- Physical printing has been demonstrated with a networked Zebra ZD421 using 93 × 71 mm labels.
- GLPI 11.0.9 and 12.0.2 installation and activation have been verified in test instances.
- The 0.2.7 security review findings were corrected; 0.2.8 completes uninstall cleanup by removing the named license configuration key.
- Demo behavior remains 14 days from first installation, up to two enabled printers, 50 successful print jobs, and a visible `DEMO` label mark.

See [printer compatibility](PRINTER_COMPATIBILITY.md) for the verified device and current scope.

## Printer compatibility

- **Verified:** Zebra ZD421 over raw TCP, with 93 × 71 mm media.
- **Expected:** other networked Zebra printers and other network printers that accept ZPL over raw TCP. Each model has not been separately tested.
- **USB:** compatibility is unknown and unsupported. USB printing is outside the project's network-printing scope and is not planned.

## GLPI publication status

- The plugin was validated on the public plugins catalogue during the 0.2.10 review.
- The in-app GLPI Marketplace review is a separate process and remains pending with GLPI/Teclib.
- The 0.2.11 release corrects the supported-placeholder validation issue identified in the latest review and provides the archive referenced by the catalog XML.
- Confirm publisher eligibility and any remaining metadata or evidence requirements with GLPI. Their current guidance says Marketplace publication may require an eligible developer/official partner.
- Decide the Professional price, payment, refund, and license transfer/reissue terms before taking payment.

Public plugin-catalog validation and in-app Marketplace approval are separate decisions; Marketplace publication is not guaranteed by a stable release or successful hardware test.

## Deployment

Plugin 0.2.12 and Print Service 0.2.5 are deployed on the owner's production GLPI and the GLPI 12.0.2 test environment. The test environment confirmed the Notes and quantity changes before production deployment. The Print Service remains a separate local component. No printer address, customer database, service token, or signing key is part of the public source or plugin archive.
