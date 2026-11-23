# Installation and first setup

This is an installation guide for a new deployment. The installer does not read or import any existing GLPI database. Printer configuration starts empty; the administrator enters each printer's address in GLPI.

## Requirements

- GLPI 11.x or 12.x (GLPI 12.0.2 tested) and PHP 8.2+ with the cURL extension.
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

On the shipping-label form, enter optional Notes and choose 1–100 copies for the print job. Notes appear in the built-in label when they fit the configured media. Shorten notes if the service reports that the label content does not fit, or use a Professional custom ZPL template for a different layout.

### License and recovery backup

On the plugin settings page, administrators can edit and save a Professional license, download a recovery JSON file, or restore from pasted recovery JSON. The recovery file contains the license (when present), installation ID, original installation timestamp, and custom ZPL template (when configured). Keep it private because it contains the customer activation license.

Professional license holders can save an optional custom ZPL label template in the plugin settings. The editor requires a complete ZPL format (`^XA` through `^XZ`); GLPI and the Print Service both enforce the Professional license. Demo users see a locked feature preview. For the full placeholder reference, an example template, substitution rules, and a step-by-step customization and testing guide, see [Custom ZPL label templates](CUSTOM_ZPL_TEMPLATES.md). Leave the editor empty to use the built-in layout.

The recovery file does not contain the GLPI database, printer configuration, API token, or Print Service SQLite state. It includes the custom template when configured; treat it as customer configuration and keep it private. For complete disaster recovery, back up the GLPI database, `GLPI_VAR_DIR/plugins/zebrashipping`, `/etc/zebrashipping`, and `/var/lib/zebrashipping` together. Restore the GLPI database and Print Service data first, then use the recovery panel if the plugin's installation identity, license, or custom template needs restoring.

## Security and data handling

- The Print Service binds to `127.0.0.1`; do not publish port 8765 on a network interface.
- API tokens and private signing keys are never part of the plugin or service release archives.
- The service accepts printer addresses only when they resolve to private network addresses; users cannot pass a printer address in a print request.
- The Print Service stores only trial state and job identifiers/status in its own SQLite database. It does not contain or import the GLPI production database.
- GLPI stores normal shipping history fields (destination/contact data) in its own plugin table. Apply the organization's retention and access policies to that data.
- Back up `/var/lib/zebrashipping` and `GLPI_VAR_DIR/plugins/zebrashipping` together. Restoring an older backup can restore an earlier trial/job state.

## Printer compatibility and scope

The stable plugin has demonstrated physical printing with a network-connected Zebra ZD421 using 93 × 71 mm media. It is intended for networked Zebra printers and is expected to work with other printers that accept ZPL over raw TCP (normally port 9100). Only the ZD421 has been individually verified; compatibility with other models, DPI values, fonts, and media sizes is not guaranteed until checked on that hardware.

USB printer compatibility is unknown and unsupported. USB printing is outside the network-printing scope and is not planned. See [printer compatibility](PRINTER_COMPATIBILITY.md).
