# Commercial readiness — GLPI Zebra Shipping

## Selected product model

- Public source under GPL-2.0-or-later; community contributions and forks may be published under the same terms.
- One-time sale planned for the official Professional activation. The existing product design makes the license perpetual within major version 1.x, bound to a GLPI installation, and verified offline without a license server.
- The fee does not remove recipients' GPL rights. Users can modify and redistribute the source; the Professional gate applies to the unmodified official build and is not guaranteed DRM against forks.
- Signing-key readiness has not been confirmed. Before taking payment, verify the owner's encrypted offline Ed25519 key and tested backup, and ensure the matching public key is configured in the production Print Service. If no key exists, generate it in an owner-controlled offline environment. Keep the private key outside Git, GLPI, and customer packages.

## GLPI publication path

GLPI's plugin catalog asks for a public Git repository, an open-source license, and XML plugin metadata. Marketplace availability is a separate review and is not automatic. After catalog acceptance, request Marketplace review from GLPI. GLPI also states that it reviews plugin authorship/developer eligibility, coding standards, ongoing maintenance, and effects on GLPI performance.

References: [GLPI plugin catalog and Marketplace tutorial](https://glpi-developer-documentation.readthedocs.io/en/master/plugins/tutorial.html#publishing-your-plugin), [GLPI plugin/Marketplace FAQ](https://help.glpi-project.org/faq/glpi/plugins).

## Owner decisions still needed before taking payment

- One-time price and payment/purchase process.
- Refund terms and policy for transferring, reissuing, or replacing an installation-bound license.
- Public support contact and maintenance commitment.
- Confirm GLPI developer/partner eligibility for Marketplace review.

## Stable release and remaining publication work

The stable 0.2.11 code includes Professional-only custom ZPL templates, a bilingual guide, GLPI 12 compatibility metadata, and the fix for validation of documented placeholders. Physical printing has been demonstrated on a network-connected Zebra ZD421 with 93 × 71 mm media. Other ZPL models are expected to work over raw TCP but have not all been individually verified. USB printing is unsupported, outside project scope, and is not planned.

- The public GLPI Plugins catalogue validated the plugin during the 0.2.10 review. The in-app GLPI Marketplace is a separate review and is not yet available in Discover.
- Publish the corrected, versioned 0.2.11 archive and update the issue with the fix and verification evidence.
- The 0.2.7 security review findings were addressed. Version 0.2.8 fixes removal of the stored license configuration during uninstall.
- Confirm with Teclib whether publisher/developer eligibility or any extra evidence is required for the in-app Marketplace review.
- Keep monitoring the separate Marketplace review; approval remains at GLPI's discretion.

The one-time price, payment process, refund terms, license transfer/reissue policy, and public support contact remain to be decided before taking payment.

See the [monetization runbook](MONETIZATION_RUNBOOK.md) for the manual order-to-license workflow and the required go-live checks.
