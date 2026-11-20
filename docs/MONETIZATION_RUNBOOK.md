# Professional sales and activation runbook

## Current implementation

- Public plugin and Print Service source are GPL-2.0-or-later.
- The demo has a 14-day period, two enabled-printer limit, and 50 successful-print limit.
- Professional activations use signed Ed25519 license files. Validation is offline; the product has no license server or recurring activation check.
- Licenses are bound to a GLPI installation ID. The signer currently issues `major_version: 1` licenses intended for the official 1.x line.
- GLPI administrators can save or restore a license in the plugin settings. The plugin encrypts the stored license with GLPI's instance key.

## Go-live gates before accepting payment

1. **Set the offer.** Decide the price, what “perpetual 1.x” means for buyers, whether sales begin with the current 0.2.8 line or a 1.0 release, and the refund, transfer, and reissue terms. Ensure the public product page and license agree.
2. **Set up payment and fulfillment.** Choose a merchant/payment provider and owner-controlled payout account. Decide how invoices/receipts, refunds, customer support, and delivery will work. Ask the accountant about applicable tax and invoicing obligations before taking payment.
3. **Confirm signing-key readiness.** Confirm that the encrypted Ed25519 private key and a tested offline backup are held in an owner-controlled environment. Confirm that the matching public key is configured in the production Print Service as `ZEBRASHIPPING_LICENSE_PUBLIC_KEY`. If no key exists, generate one offline using `licensing/generate_keys.py`. Never store the private key in Git, GLPI, a customer server, or this runbook.
4. **Test fulfillment in staging.** Generate a test license for a disposable GLPI installation ID, install it through the UI, verify Professional status and limits, and test recovery. Do not use a real customer's license or a production installation ID in a public demonstration.
5. **Choose a support contact and privacy notice.** The manual workflow receives a GLPI installation ID to issue an installation-bound license. Tell buyers what information is collected, how it is used, how long it is retained, and how they can request transfer/reissue or deletion.
6. **Publish the customer terms.** Publish the GPL terms, paid Professional offer, support/maintenance scope, compatibility statement, and refund/transfer terms together. The commercial payment cannot remove the recipient's GPL rights to modify and redistribute source.

## Manual order-to-license workflow

1. Verify payment and the customer's agreed license terms.
2. Receive the customer's installation ID through the chosen support channel.
3. Run `licensing/sign_license.py` in the protected operator environment; review the generated license before delivery.
4. Send the license file to the purchaser through the agreed delivery channel and instruct them to paste it into **Setup → Plugins → GLPI Zebra Shipping → Professional license**.
5. Confirm the status changes to Professional without asking for database credentials, printer IPs, or API tokens.
6. Keep only the order and support records needed for accounting and license reissue. Do not put customer license files or installation IDs in Git or public issue reports.

There is no payment checkout or automatic license-delivery integration in the repository yet. The workflow above can start manually once the commercial and key-readiness gates are complete.

## GLPI catalog and Marketplace

The stable GitHub release and catalog XML are public, but the plugin is not yet listed in the public catalog. The latest catalog form attempt returned `UNAVAILABLE_NAME`. Retry the XML submission after resolving that response; if it persists, ask Teclib which name/key is unavailable. After catalog acceptance, request a separate Marketplace review. See [catalog status and remaining gates](MARKETPLACE_SUBMISSION_DRAFT.md).
