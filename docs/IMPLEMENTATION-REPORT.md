# Gadgero German launch — implementation report (local build, 2026-11-08)

Local site: http://localhost/gadgero-wp/ · WordPress 7.1.4 · WooCommerce 11.2.2 · PHP 8.4 · custom theme `gadgero`.
Status: **built and tested locally with a flagged placeholder catalogue. Not ready to publish** until the items in `CLIENT-CONFIRMATION-REQUIRED.md` are supplied.

## COMPLETED
**Phase 0–1 — Audit and theme**
- Audit and migration plan: `docs/phase-0-audit-and-migration-plan.md`.
- Custom theme built from the approved design. The approved CSS is reused unchanged; the 3D scroll hero is ported unchanged apart from reading data from WordPress. Header, footer, about orbit, buyback, dropdowns and drawer are reused.
- Dynamic menus (Header, Legal, Service), Customizer logo with fallback to the approved logo, contact data from the Customizer (never hard-coded).
- Added a mobile menu button. The approved design hid navigation below 820 px with no way to open it.

**Phase 2 — WooCommerce**
- Categories: Phones, Tablets, Laptops, Desktops, Smartwatches, Accessories.
- Global attributes: Condition, Storage, Colour, Connectivity, Screen Size.
- Condition (New / Refurbished) is product-level, filterable and shown as the approved badge.
- SKU, stock management, regular prices; sale prices only when set in WooCommerce. No fake crossed-out prices.

**Phase 3 — Apple iPhone Duo banner**
- Lettering, both slides, mobile layout and copy say "Apple iPhone Duo". The name comes from the WooCommerce product.
- "Shop Apple iPhone Duo" CTA under the price on both slides, linking to the exact product page.
- "From" price is computed from the cheapest in-stock, purchasable variation. Price and CTA hide automatically if the product can't be bought.
- Long name wraps onto lines on phones so the large lettering stays visible.

**Phase 4 — New and refurbished routes**
- Homepage "New or refurbished?" section, plus All / New / Refurbished toggles on the shop and category pages (`?filter_condition=`). Category chips keep the filter.
- The refurbishment process ("From our bench to your hands": inspection, cleaning & data wiping, battery testing, software installation) is shown only for refurbished devices: About section (labelled), refurbished shop route and refurbished product pages.
- "Original factory-sealed packaging" is a per-product switch for new devices only.

**Phase 5 — About**
- Client text in place. Unsupported stats and claims removed.

**Phase 6 — Buyback**
- New headline. Device type now includes Tablet (plus Phone, Smartwatch, Laptop, Desktop, AirPods/headphones).
- Fields: device type, model (hint changes per type), storage, condition.
- Live message preview. The WhatsApp link and the copied text both use the same message.
- Response-time, same-day-payment and in-store promises removed.

**Phase 7 — Variations**
- iPad Air M3: 11/13-inch × 128/256 GB × Blue/Space Grey × Wi-Fi/Wi-Fi + Cellular = 16 variations, each with its own SKU, price and stock.
- Native WooCommerce variation logic, shown as pill selectors.
- Add to cart is enabled only for a complete, valid, in-stock variation.
- Options with a single value are shown as preselected (2026-11-11): on simple products (e.g. iPad 10th Gen) Screen size, Storage, Colour, Connectivity and Condition appear as selected pills marked "Only option". A variation option with only one value is selected automatically.
- Variation names never merge attributes. One configuration line follows the item from the product page through cart, checkout, order, confirmation and e-mail.

**Phase 8 — Product information**
- iPhone 17 Pro uses Silver / Cosmic Orange / Deep Blue. Burgundy removed. Mismatched demo photos removed.
- Refurbished fields (battery health, screen, frame, accessories, inspected by, programme) appear only when filled.
- Refurbished pages say images are illustrative unless "Photos show this exact unit" is ticked.
- No certification claims are made.

**Phase 9 — Product page**
- Gadgero-styled layout: gallery, badge, title, price with VAT/shipping note, short description, refurbished details, pills, live "Selected configuration", SKU, stock, WhatsApp advice link, description, specifications, refurbishment process (refurbished only) and related products of the same condition.

**Phase 10 — Cart**
- WooCommerce cart restyled (German). Shows image or illustration, name, configuration line, quantity, price and subtotal.
- Header mini-cart drawer updates via WooCommerce fragments.
- WhatsApp ordering removed from the cart.

**Phase 11 — German guest checkout**
- Guest checkout only (no account creation or login prompt). Formal German ("Sie") on cart, checkout, confirmation and e-mails; English on the storefront.
- Delivery address, shipping method with amount, optional delivery-time line, payment selection, "Zahlungsart" row, VAT-inclusive total and configuration in the review.
- Order button reads "Zahlungspflichtig bestellen". A note separates WhatsApp enquiries from binding orders.

**Phase 12 — Pricing**
- € everywhere, German number format (1.799,00 €), prices incl. 19 % VAT with "plus shipping" link.
- Sample-price note, "Easy EMI" and the shop-warranty claim removed.

**Phase 13–14 — Legal and warranty**
- German drafts: Impressum, Datenschutzerklärung, Widerrufsbelehrung, Muster-Widerrufsformular, Versand & Zahlung, Garantie & Service.
- Company data is inserted from the Customizer via shortcodes. Missing data shows as `[CLIENT CONFIRMATION REQUIRED: …]`.
- Garantie & Service separates statutory rights from a commercial warranty and offers Problem melden / Support kontaktieren / Rücksendung starten / Hilfe anfordern.

**Safety net**
- wp-admin warning listing missing company fields, unconfirmed products and open settings.
- "Client data" column in the product list.
- Red chips on the storefront for logged-in managers only.

## CLIENT CONFIRMATION REQUIRED and PRODUCTS REQUIRING VERIFICATION
See `docs/CLIENT-CONFIRMATION-REQUIRED.md` (sections 1–4): company/legal details, approved euro prices, SKUs, stock, shipping costs, delivery areas and times, payment methods, financing, warranty terms, returns policy, VAT margin scheme, AGB. All 16 products need price/SKU/stock confirmation. iPhone Duo, iPhone 17 Pro, Apple Watch Ultra, AirPods Pro and iPad Air M3 need specific image, colour or generation checks.

## QA RESULTS (local, in-app browser)
| Test | Result |
|---|---|
| Desktop (≈1280 px) | Pass — homepage, shop, product, cart, checkout, confirmation, legal pages |
| Tablet (768 px) | Pass — no horizontal scroll; hero, routes, shop filters, product page, legal page |
| Mobile (375 px) | Pass — no horizontal scroll; hero (lettering wraps), mobile menu, routes, product page, cart, checkout. Fixed during QA: lettering hidden behind the phone, duplicate stock line, "described on the right" note |
| 1 — Apple iPhone Duo | Pass — lettering "APPLE IPHONE DUO"; both CTAs → `/product/apple-iphone-duo/`; "From 1.799,00 €" = 256 GB variation, in stock and purchasable. ⚠ No real product photo (illustration / 3D model only) |
| 2 — iPad variation | Pass — 11-inch · 128 GB · Blue · Wi-Fi → 599,00 €, "3 in stock", SKU GAD-IPADAIR-M3-11-128-BLU-WIFI. Sold-out combination shows "Out of stock" and the button is disabled. Same configuration in cart, checkout, order #79 item meta, confirmation page. Second run (13-inch · 256 GB · Blue · Wi-Fi + Cellular, 1.089,00 €) → order #80, redirected to confirmation, customer e-mail rendered in German with the configuration |
| Cart | Pass — German, configuration line, quantities, subtotal; mini-cart count updates |
| Checkout | Pass — guest, German (formal), shipping "Standardversand: 0,00 €" (test rate), "Zahlungsart: Vorkasse per Überweisung", total incl. VAT, "Zahlungspflichtig bestellen" |
| 3 — Tablet buyback | Pass — Tablet + "iPad Air" + 128 GB + "Good, minor marks" → "Hi Gadgero, I want to sell my Tablet: iPad Air · 128 GB. Condition: Good, minor marks. What price can you offer?" identical in the WhatsApp link and the copy action ("Message copied"). ⚠ The link has no recipient until the WhatsApp number is entered |
| PHP | All files lint clean; `debug.log` empty after testing |

**Re-run 2026-11-11 (branch `feature/german-launch`), desktop and mobile 375 px:**
| Test | Result |
|---|---|
| 1 — Banner button | Pass on both. One tap → `/product/apple-iphone-duo/`, "From 1.799,00 €" |
| 2 — iPad config | Pass. 11-inch · 128 GB · Blue · Wi-Fi → 599,00 € → 256 GB → 719,00 € (SKU and stock update). 13-inch · 256 GB · Space Grey · Wi-Fi + Cellular → "Out of stock", button disabled. Test order #82: same configuration, 599,00 €, Standardversand 0,00 € (test rate) on the confirmation. Mobile: selection, price and stock update the same way, no horizontal scroll |
| 3 — Tablet buyback | Pass on both. WhatsApp text = copied text: "…sell my Tablet: iPad Air · 128 GB. Condition: Good, minor marks…" |
| Fix | Footer "Shop new / Shop refurbished" links contained `/index.php/`; fixed in the DB and in `seed.php` |

Not tested: real e-mail delivery (rendering only), real payment gateways (none configured), wp-admin product editing through the UI, browsers other than the app's Chromium.

## REMAINING RISKS BEFORE LAUNCH — do not publish until resolved
1. Placeholder prices, SKUs and stock on all 16 products.
2. Missing Impressum / company data, and unreviewed legal pages. No AGB, and no electronic withdrawal function (in force since 19.06.2026).
3. Test shipping rate (0,00 €) and test payment method without bank details.
4. Unconfirmed VAT treatment of refurbished devices (§ 25a UStG).
5. Product imagery: iPhone Duo and iPhone 17 Pro have no matching photos. Watch/AirPods generations are unconfirmed.
6. Google Fonts and three.js are loaded from third-party CDNs (GDPR) — self-host.
7. Warranty and returns process undefined.
8. Test orders (#79, #80, #82) and the test admin account exist in the local database. Don't migrate test data to production.
