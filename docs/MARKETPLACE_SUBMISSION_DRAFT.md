# GLPI catalog and Marketplace submission draft

This is a preparation document only. It is not a submission, and the source repository remains private.

## Draft catalog details

- Display name: GLPI Zebra Shipping
- Technical key: `zebrashipping`
- Current plugin version: `0.2.2` (test build; not a release candidate)
- GLPI compatibility tested: GLPI `11.0.9`; declared minimum: `11.0`
- Plugin source: <https://github.com/connectitbg/glpi-zebra-shipping> (private; not catalog-ready)
- License: unresolved. Do not publish until the owner selects a distribution and license model.
- Publisher/support contact: unresolved.
- Logo and public screenshots: not prepared. Existing test screenshots contain environment and asset details and must not be published as-is.
- Download URL: not available. The current ZIP is a local test artifact and is not a public, versioned release.

## Proposed short description

Create and print shipping labels for GLPI assets and tickets on administrator-configured Zebra network printers.

## Proposed long description

GLPI Zebra Shipping adds a shipping-label tab to supported GLPI assets and tickets. Users can enter recipient and destination details, select an enabled Zebra printer, and send a label through a separately installed local Print Service. Administrators configure printer addresses and media dimensions in the plugin settings; no printer address is built into the package.

The demo is limited to 14 days from first installation, two enabled printers, and 50 successful print jobs. Professional licensing is designed for offline verification and does not require a license server.

The current build has been installed on a GLPI 11.0.9 test clone. A physical ZD421 test print was sent, but visual review of the printed label, dimensions, Cyrillic text, and clipping is still pending. This build must not be described as production-ready until the remaining compatibility, security, upgrade, and licensing reviews are completed.

## Remaining publication gates

1. Choose and document the public catalog license, or obtain GLPI's direction for the proprietary distribution route. The code's current metadata says commercial/proprietary.
2. Confirm that the publisher satisfies GLPI's developer or trained-partner requirements for Marketplace review.
3. Decide the publisher identity, public support address, maintenance commitment, pricing, and license transfer/reissue terms.
4. Finish the ZD421 visual label check and any resulting patch; run the release checks and create a versioned, publicly downloadable plugin archive.
5. Publish the source and archive at public URLs if taking the catalog route; create the required XML metadata, sanitized logo, and screenshots.
6. Submit the plugin to the GLPI plugin catalog. After catalog acceptance, request separate Marketplace review from GLPI.

## Submission sequence from current state

1. Keep this repository private while the licensing and ownership decisions are open.
2. Complete the label review and technical release checks.
3. Resolve the distribution route and satisfy its publication prerequisites.
4. Publish the catalog entry with the source URL, XML URL, license, supported versions, and public archive URL.
5. After catalog acceptance, request Marketplace review. Availability is subject to GLPI review and is not automatic.

## Official references

- [GLPI plugin catalog and Marketplace submission guidance](https://glpi-developer-documentation.readthedocs.io/en/master/plugins/tutorial.html#publishing-your-plugin)
- [GLPI FAQ: adding a plugin to the Marketplace](https://help.glpi-project.org/faq/glpi/plugins)
