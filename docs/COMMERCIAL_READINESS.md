# Commercial readiness — GLPI Zebra Shipping

## Selected product model

- Public source under GPL-2.0-or-later; community contributions and forks may be published under the same terms.
- One-time sale planned for the official Professional activation. The existing product design makes the license perpetual within major version 1.x, bound to a GLPI installation, and verified offline without a license server.
- The fee does not remove recipients' GPL rights. Users can modify and redistribute the source; the Professional gate applies to the unmodified official build and is not guaranteed DRM against forks.
- No signing key has been generated. Generate it once in an owner-controlled offline environment after product identity and final license terms are agreed. Keep the private key encrypted and backed up outside Git, GLPI, and customer packages.

## GLPI publication path

GLPI's plugin catalog asks for a public Git repository, an open-source license, and XML plugin metadata. Marketplace availability is a separate review and is not automatic. After catalog acceptance, request Marketplace review from GLPI. GLPI also states that it reviews plugin authorship/developer eligibility, coding standards, ongoing maintenance, and effects on GLPI performance.

References: [GLPI plugin catalog and Marketplace tutorial](https://glpi-developer-documentation.readthedocs.io/en/master/plugins/tutorial.html#publishing-your-plugin), [GLPI plugin/Marketplace FAQ](https://help.glpi-project.org/faq/glpi/plugins).

## Owner decisions still needed before taking payment

- One-time price and payment/purchase process.
- Refund terms and policy for transferring, reissuing, or replacing an installation-bound license.
- Public support contact and maintenance commitment.
- Confirm GLPI developer/partner eligibility for Marketplace review.

## Technical release checks still open

- Review the physical ZD421 label, media dimensions, Cyrillic rendering, and clipping; patch if needed.
- Complete security, dependency-license, GLPI coding-standard, privacy/logging, backup/restore, and upgrade reviews.
- Publish the Git repository publicly; prepare the catalog XML, sanitized logo and screenshots, and a versioned public plugin archive.
- Submit to the GLPI plugin catalog; after acceptance, request separate Marketplace review.
- The 0.2.2 plugin and 0.2.0 Print Service are test builds, not a production release or Marketplace submission.
