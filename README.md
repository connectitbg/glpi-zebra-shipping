# GLPI Zebra Shipping

Commercial shipping-label product for GLPI 11 and Zebra network printers.

This repository is being rebuilt from the project handoff. The earlier implementation files and production server are not present in this workspace, so this is a new codebase rather than a port of the deployed code.

## Product decisions

- Demo period: 14 days from first plugin installation.
- Demo limits: two enabled printers, 50 successful print jobs, and 14 days from first plugin installation.
- Trial state must live outside the plugin directory and survive upgrades/reinstalls.
- Professional license: perpetual for major version 1.x, verified offline with Ed25519.
- Professional includes multiple printers.
- No production printer IP, service token, private signing key, or customer data belongs in this repository.

## Components

- `plugin/`: GLPI plugin. It owns GLPI permissions, shipment form/history, printer selection, trial/license state, and print-service requests.
- `print-service/`: localhost service that validates requests and licenses, renders ZPL, and sends jobs to registered printers.
- `licensing/`: offline license format and operator-side signing utility. The signing key is never shipped to customers.
- `docs/`: installation, configuration, security, and release procedures.

The service and plugin are not yet production ready. Marketplace compatibility, commercial distribution terms, and the existing dependency license obligations must be reviewed before publishing or selling a release. GLPI's public plugin catalog and its Marketplace have separate publication and review processes.

## Development status

Implementation in progress. The current source is a clean-room rebuild based on the handoff, not a copy of the prior production deployment. Do not install this version on a production GLPI instance.
