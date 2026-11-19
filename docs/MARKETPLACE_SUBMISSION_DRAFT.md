# GLPI catalog and Marketplace submission draft

This document tracks the beta catalog listing and its Marketplace follow-up.

## Draft catalog details

- Display name: GLPI Zebra Shipping
- Technical key: `zebrashipping`
- Publisher: `Connect IT`
- Current plugin version: `0.2.3` (beta; revised physical layout still needs a test print)
- GLPI compatibility tested: GLPI `11.0.9`; declared minimum: `11.0`
- Plugin source: <https://github.com/connectitbg/glpi-zebra-shipping>
- License: GPL-2.0-or-later; one-time Professional sale is for the official build and does not remove GPL rights.
- Publisher/support contact: public GitHub issues can be used initially; a monitored support address and maintenance commitment still need to be selected.
- Logo and public screenshots: not prepared. Existing test screenshots contain environment and asset details and must not be published as-is.
- Download URL: <https://github.com/connectitbg/glpi-zebra-shipping/releases/download/v0.2.3/glpi-zebrashipping-plugin-0.2.3.zip>

## Proposed short description

Create and print shipping labels for GLPI assets and tickets on administrator-configured Zebra network printers.

## Proposed long description

GLPI Zebra Shipping adds a shipping-label tab to supported GLPI assets and tickets. Users can enter recipient and destination details, select an enabled Zebra printer, and send a label through a separately installed local Print Service. Administrators configure printer addresses and media dimensions in the plugin settings; no printer address is built into the package.

The demo is limited to 14 days from first installation, two enabled printers, and 50 successful print jobs. A one-time Professional purchase is planned for perpetual use of the official release within major version 1.x. Licensing is verified offline and does not require a license server. The source is GPL-2.0-or-later, so users retain the right to modify and redistribute the code under the GPL.

The beta has been installed on a GLPI 11.0.9 test clone. A physical ZD421 print succeeded after setting the media to 93 × 71 mm. The revised large-font layout must be printed and visually reviewed before this build is described as production-ready; the `DEMO` mark remains visible during the demo period.

## Remaining publication gates

1. Publish the source repository publicly under the selected GPL-2.0-or-later license.
2. Confirm that the publisher satisfies GLPI's developer or trained-partner requirements for Marketplace review.
3. Decide the publisher identity, public support address, maintenance commitment, pricing, and license transfer/reissue terms.
4. Finish the ZD421 visual label check and any resulting patch; run the release checks and create a versioned, publicly downloadable plugin archive.
5. Publish the source and archive at public URLs if taking the catalog route; create the required XML metadata, sanitized logo, and screenshots.
6. Submit the plugin to the GLPI plugin catalog. After catalog acceptance, request separate Marketplace review from GLPI.

## Submission sequence from current state

1. Complete the label review and technical release checks.
2. Publish the repository under GPL-2.0-or-later and create a public, versioned plugin archive.
3. Add the XML metadata, sanitized logo, and screenshots; submit the entry to the GLPI plugins catalog.
4. After catalog acceptance, request Marketplace review. Availability is subject to GLPI review and is not automatic.

## Official references

- [GLPI plugin catalog and Marketplace submission guidance](https://glpi-developer-documentation.readthedocs.io/en/master/plugins/tutorial.html#publishing-your-plugin)
- [GLPI FAQ: adding a plugin to the Marketplace](https://help.glpi-project.org/faq/glpi/plugins)
