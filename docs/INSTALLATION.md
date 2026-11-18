# Installation and first setup

This is an installation guide for a new deployment. The installer does not read or import any existing GLPI database. Printer configuration starts empty; the administrator enters each printer's address in GLPI.

## Requirements

- GLPI 11.x and PHP 8.2+ with the cURL extension.
- Debian 12 or a compatible Linux host for the separate Print Service.
- Python 3.11+ and access to the Zebra printers on the local network.
- TCP 9100 (or the configured raw-print port) reachable from the Print Service host.

## Print Service

1. Copy the `print-service` directory to the GLPI host or another host on the printer LAN.
2. Run `sudo sh install_debian.sh` from that directory. It creates a service account, a random API token, an empty printer registry, and a loopback-only systemd service.
3. Configure the GLPI Apache/PHP environment with:

   ```apache
   SetEnv ZEBRASHIPPING_SERVICE_URL "http://127.0.0.1:8765"
   SetEnv ZEBRASHIPPING_API_TOKEN "copy-the-generated-token-here"
   ```

   Read the generated token from `/etc/zebrashipping/print-service.env` using a privileged shell. Keep both copies private; create the Apache config as `root:root` with mode `0640`, and keep the token out of shared backups or support logs.
4. If using Professional licenses, set `ZEBRASHIPPING_LICENSE_PUBLIC_KEY` in the service environment to the product public key, then restart the service.

The sample configuration has `printers: []`. Add a printer later in **Setup → Plugins → GLPI Zebra Shipping**. The IP/hostname, port, DPI, and label size are entered by the customer during setup and stored in `/etc/zebrashipping/printers.json` with restricted permissions.

## GLPI plugin

1. Build the plugin archive using `sh build_release.sh` from the repository root.
2. Extract the `zebrashipping` plugin directory into GLPI's configured plugin directory.
3. Install and enable it from **Setup → Plugins**. Installation creates the plugin job table and a random installation ID with the first-install timestamp under `GLPI_VAR_DIR/plugins/zebrashipping/`.
4. In **Setup → Plugins → GLPI Zebra Shipping**, add each printer's connection settings and run the connectivity check.
5. The 14-day demo starts from the plugin's first installation timestamp. Demo allows up to two enabled printers and 50 successful print jobs, marks labels `DEMO`, and keeps print history after expiration.

## Security and data handling

- The Print Service binds to `127.0.0.1`; do not publish port 8765 on a network interface.
- API tokens and private signing keys are never part of the plugin or service release archives.
- The service accepts printer addresses only when they resolve to private network addresses; users cannot pass a printer address in a print request.
- The Print Service stores only trial state and job identifiers/status in its own SQLite database. It does not contain or import the GLPI production database.
- GLPI stores normal shipping history fields (destination/contact data) in its own plugin table. Apply the organization's retention and access policies to that data.
- Back up `/var/lib/zebrashipping` and `GLPI_VAR_DIR/plugins/zebrashipping` together. Restoring an older backup can restore an earlier trial/job state.

## Current limitations

This repository is an implementation in progress. Run it first in a disposable GLPI environment and a non-production printer. Printer response confirms the job was sent over TCP, not that the label physically printed correctly. Unicode glyph support and exact label fit must be confirmed on each supported Zebra model and font configuration.
