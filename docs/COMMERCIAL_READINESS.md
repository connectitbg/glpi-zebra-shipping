# Commercial readiness — GLPI Zebra Shipping

## Current product model

- The demo is local to each GLPI installation: 14 days, up to two enabled printers, and 50 successful print jobs.
- Professional activation is designed to work offline. A private Ed25519 signing key issues installation-bound license files; the customer Print Service verifies them using a public key. There is no license server.
- No signing key has been generated. Generate it once in an owner-controlled offline environment after deciding the product identity and license terms. Keep the private key encrypted and backed up outside Git, GLPI, and customer packages.

## Distribution decision required before publishing

GLPI's plugin catalog submission guidance asks for a publicly accessible Git repository and an open-source license. Marketplace availability is a separate review and is not automatic. The current plugin metadata marks the product as commercial/proprietary, so it should not be submitted to the community catalog in its current form without resolving that licensing mismatch.

Choose the intended route before publishing:

1. **Community catalog route:** publish the source under an open-source license, then use an explicitly documented commercial model that is compatible with that license (for example, paid support or separate services). Request GLPI Marketplace review after catalog publication.
2. **Proprietary sales route:** retain closed-source distribution and confirm an approved GLPI/partner or direct-download route with GLPI before promising Marketplace availability. Keep Git private until the publication and license terms are settled.

The catalog and Marketplace steps are described in the [GLPI plugin tutorial](https://glpi-developer-documentation.readthedocs.io/en/master/plugins/tutorial.html#publishing-your-plugin). The Marketplace review process is separate from the catalog listing.

## Owner decisions still needed

- Product/publisher name, public support contact, and support/maintenance commitment.
- Distribution route and final commercial license terms; pricing and purchase process.
- Policy for license transfers, reissues, and replacement GLPI installations.
- Approval to create or use the public Git organization/repository and GLPI publisher contact.
- Generate and securely retain the offline signing key after the product name and license policy are final.

## Technical release checks still open

- Review tomorrow's physical ZD421 DEMO output, actual media dimensions, Cyrillic rendering, and clipping.
- Complete security, dependency-license, GLPI coding-standard, privacy, and upgrade reviews before release.
- Make the release builder reproducible on the release host and review the final archives before upload.
- The 0.2.2 plugin and 0.2.0 Print Service are test builds; they are not a Marketplace submission.
