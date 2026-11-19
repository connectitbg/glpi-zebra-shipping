# GLPI catalog and Marketplace submission draft

This document tracks the beta catalog listing and its Marketplace follow-up.

## Draft catalog details

- Display name: GLPI Zebra Shipping
- Technical key: `zebrashipping`
- Publisher: `Connect IT`
- Current plugin version: `0.2.6` (beta; asset metadata fields are editable, and license/installation recovery is available in plugin settings)
- GLPI compatibility tested: GLPI `11.0.9`; declared minimum: `11.0`
- Plugin source: <https://github.com/connectitbg/glpi-zebra-shipping>
- License: GPL-2.0-or-later; one-time Professional sale is for the official build and does not remove GPL rights.
- Publisher/support contact: public GitHub issues can be used initially; a monitored support address and maintenance commitment still need to be selected.
- Logo and public screenshots: not prepared. Existing test screenshots contain environment and asset details and must not be published as-is.
- Download URL: <https://github.com/connectitbg/glpi-zebra-shipping/releases/download/v0.2.6/glpi-zebrashipping-plugin-0.2.6.zip>

## Proposed short description

Create and print shipping labels for GLPI assets and tickets on administrator-configured Zebra network printers.

## Proposed long description

GLPI Zebra Shipping adds a shipping-label tab to supported GLPI assets and tickets. Users can enter recipient and destination details, select an enabled Zebra printer, and send a label through a separately installed local Print Service. Administrators configure printer addresses and media dimensions in the plugin settings; no printer address is built into the package.

The demo is limited to 14 days from first installation, two enabled printers, and 50 successful print jobs. A one-time Professional purchase is planned for perpetual use of the official release within major version 1.x. Licensing is verified offline and does not require a license server. The source is GPL-2.0-or-later, so users retain the right to modify and redistribute the code under the GPL.

The beta has been installed on a GLPI 11.0.9 test clone. A physical ZD421 print succeeded after setting the media to 93 × 71 mm. Version 0.2.5 added asset name, asset type, inventory number, and serial number below the address. Version 0.2.6 makes those prefilled fields editable and adds a recovery export/restore for the license and installation identity. The recovery file does not replace backups of GLPI or Print Service data. The `DEMO` mark remains visible during the demo period.

## Remaining publication gates

1. Confirm that the publisher satisfies GLPI's developer or trained-partner requirements for Marketplace review.
2. Decide the public support address, maintenance commitment, pricing, and license transfer/reissue terms.
3. Finish coding, security, dependency, and upgrade reviews.
4. Prepare sanitized screenshots and verify the XML metadata; the source and v0.2.6 release archive are already public.
5. Submit the plugin to the GLPI plugin catalog. After catalog acceptance, request separate Marketplace review from GLPI.

## Submission sequence from current state

1. Complete the remaining technical reviews and publisher/business decisions.
2. Prepare sanitized screenshots and verify the XML metadata against the public v0.2.6 release.
3. Submit the catalog entry, then request Marketplace review after catalog acceptance.
4. Availability is subject to GLPI review and is not automatic.

## Official references

- [GLPI plugin catalog and Marketplace submission guidance](https://glpi-developer-documentation.readthedocs.io/en/master/plugins/tutorial.html#publishing-your-plugin)
- [GLPI FAQ: adding a plugin to the Marketplace](https://help.glpi-project.org/faq/glpi/plugins)
