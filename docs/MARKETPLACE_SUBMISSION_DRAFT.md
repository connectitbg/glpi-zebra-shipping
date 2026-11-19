# GLPI catalog and Marketplace submission draft

This document tracks the stable catalog listing and its separate Marketplace review.

## Draft catalog details

- Display name: GLPI Zebra Shipping
- Technical key: `zebrashipping`
- Publisher: `Connect IT`
- Current plugin version: `0.2.7` (stable; includes security review fixes and encrypted license storage)
- GLPI compatibility tested: GLPI `11.0.9`; declared minimum: `11.0`
- Plugin source: <https://github.com/connectitbg/glpi-zebra-shipping>
- License: GPL-2.0-or-later; one-time Professional sale is for the official build and does not remove GPL rights.
- Publisher/support contact: public GitHub issues can be used initially; a monitored support address and maintenance commitment still need to be selected.
- Logo and public screenshots: not prepared. Existing test screenshots contain environment and asset details and must not be published as-is.
- Download URL: <https://github.com/connectitbg/glpi-zebra-shipping/releases/download/v0.2.7/glpi-zebrashipping-plugin-0.2.7.zip>

## Proposed short description

Create and print shipping labels for GLPI assets and tickets on administrator-configured Zebra network printers.

## Proposed long description

GLPI Zebra Shipping adds a shipping-label tab to supported GLPI assets and tickets. Users can enter recipient and destination details, select an enabled Zebra printer, and send a label through a separately installed local Print Service. Administrators configure printer addresses and media dimensions in the plugin settings; no printer address is built into the package.

The demo is limited to 14 days from first installation, two enabled printers, and 50 successful print jobs. A one-time Professional purchase is planned for perpetual use of the official release within major version 1.x. Licensing is verified offline and does not require a license server. The source is GPL-2.0-or-later, so users retain the right to modify and redistribute the code under the GPL.

The stable release has been installed on GLPI 11.0.9 test and production instances. Physical printing has been demonstrated with a network Zebra ZD421 using 93 × 71 mm media. Version 0.2.5 added asset name, asset type, inventory number, and serial number below the address. Version 0.2.6 made those fields editable and added recovery export/restore for the license and installation identity. Version 0.2.7 addresses the reported access-control, license-storage, uninstall, validation, and local-service URL findings. The recovery file does not replace backups of GLPI or Print Service data. Demo labels retain the `DEMO` mark.

The plugin is intended for Zebra printers on the network and is expected to work with other printers accepting ZPL over raw TCP. Other models have not been individually validated. USB printer compatibility is unknown and unsupported; USB printing is out of scope and is not planned.

The most recent catalog submission attempt returned `UNAVAILABLE_NAME`; publication has not been confirmed. The repository, stable release archive, and XML metadata are public, but catalog acceptance remains pending.

## Remaining publication gates

1. Resolve the `UNAVAILABLE_NAME` response and submit the catalog XML again.
2. Confirm that the publisher meets GLPI's developer/official trained-partner eligibility requirement.
3. Prepare sanitized screenshots and complete independent GLPI coding-standard, dependency-license, and upgrade reviews.
4. Document the ongoing maintenance and support commitment.
5. After catalog acceptance, email `glpi@teclib.com` to request separate Marketplace review. Decide pricing, refunds, and license transfer/reissue terms before taking payment.

## Submission sequence from current state

1. Resubmit the XML after resolving catalog name availability and wait for catalog acceptance.
2. Confirm that the publisher meets GLPI's authorship/developer eligibility requirement.
3. After catalog acceptance, request Marketplace review and provide the public repository, stable archive, compatibility evidence, and maintenance/support plan.
4. Marketplace availability is subject to GLPI review and is not automatic.

## Official references

- [GLPI plugin catalog and Marketplace submission guidance](https://glpi-developer-documentation.readthedocs.io/en/master/plugins/tutorial.html#publishing-your-plugin)
- [GLPI FAQ: adding a plugin to the Marketplace](https://help.glpi-project.org/faq/glpi/plugins)
