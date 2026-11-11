# Phase 0 — Audit of the static Gadgero site and WordPress migration plan

Source audited: `index.html` (single file, ~378 KB), `model.wasm` (GLB 3D model of the foldable phone, 5.6 MB), favicons, `site.webmanifest`, `og-image.png`.

Decisions confirmed with the project owner (2026-11-08):

| Topic | Decision |
|---|---|
| Language | Storefront (header, homepage, shop, product page) in **English**. **Cart, checkout, order confirmation, e-mails and legal pages in German.** |
| Catalogue | Local build is seeded with a **flagged placeholder catalogue** (prices, SKUs, stock) so variations and checkout can be tested. Every seeded product carries `_gadgero_unconfirmed = yes` and appears in the wp-admin warning list. Nothing seeded may go live unconfirmed. |
| Condition | **Product-level** (each product is either New or Refurbished, via the `pa_condition` attribute). Not a variation. Condition is still printed in cart, checkout, order and e-mails. |

## 1. What exists today

| Area | Static implementation | Notes |
|---|---|---|
| Header | Fixed white bar, embedded base64 logo PNG, 5 anchor links (Home, Shop, About, Sell yours, Contact), cart pill with count | Nav is hidden below 820 px; there is **no mobile menu** (gap). |
| Hero | 250 svh scroll story. Sticky stage, aurora blobs, ring, giant word "DUO FOLD", Three.js r147 scene loading `model.wasm` (GLB with fold animation driven by scroll), particle field, SVG fallback without WebGL. Intro copy "iPhone Duo Fold / From $1,799", one step panel "02 Folds flat. Opens wide." with CTA to `#shop`. | `SLIDES` array (IPHONE/MACBOOK/IPAD/IMAC) is defined but **unused**. Counter shows "01 / 02". |
| Trust strip | 4 claims: invoice, battery health, shop warranty, **Easy EMI** | EMI and warranty are unconfirmed claims. |
| Shop | JS-rendered grid from a hard-coded `PRODUCTS` array (16 items), category chips, cards with New/Refurbished badge, `$` prices, crossed-out `was` prices, "Add to cart" | 8 products use embedded WebP photos, 8 use generated SVG device art. Public note "Prices shown are sample prices…". |
| Product page | **None.** Cards add straight to a JS cart. | Must be designed in the existing visual language (Phase 9). |
| About | Navy section, orbit animation with logo, Copenhagen story, 4 count-up stats (10 yrs, 25,000+, 40 checks, 4.9/5), 3 value cards, 4-step process "From our bench to your hands" | Stats, "40-point inspection", "compare against the market every week", "repair, replace or refund" are **unsupported claims**. |
| Buyback | Device + condition selects (custom themed dropdown component), free-text model, WhatsApp link + copy button | No Tablet option. Promises "reply within the hour", "paid the same day", "bring the device to the shop" are **unconfirmed**. |
| Footer | Logo, Copenhagen address, opening hours, WhatsApp number, trademark disclaimer | Address/hours/number are placeholders. No legal links. |
| Cart | Off-canvas drawer, in-memory only, WhatsApp order message | Replaced by WooCommerce cart. Drawer styling is reused as a mini-cart. |
| JS | Hero scene, scroll progress, shop filter, cart, clipboard copy, about reveal + count-up, themed dropdowns | Reusable: hero, reveal, dropdowns, clipboard, toast. Rebuilt: shop, cart. |
| Responsive | Breakpoints at 1040 / 900 / 820 / 480 px; safe-area insets; reduced-motion support | Kept as is. |

## 2. Migration map

### Template parts (theme `wordpress/themes/gadgero`)
| Static section | WordPress file |
|---|---|
| `<head>` + header | `header.php` (+ `wp_nav_menu` location `primary`, custom logo with fallback to bundled logo, WooCommerce cart fragment for the count) |
| Footer | `footer.php` (contact data from Customizer, `footer` + `legal` menu locations, mini-cart drawer, toast) |
| Hero | `template-parts/home/hero.php` — campaign product chosen in Customizer; name, image, "from" price and CTA URL read from WooCommerce |
| New / Refurbished routes | `template-parts/home/routes.php` |
| Trust strip | `template-parts/home/trust.php` (EMI removed; each item editable) |
| Shop grid | `template-parts/home/shop.php` → WooCommerce products query, card = `woocommerce/content-product.php` |
| About | `template-parts/home/about.php` |
| Refurbishment process | `template-parts/home/process.php` — shown only next to refurbished products |
| Buyback | `template-parts/home/buyback.php` |

### WooCommerce
| Feature | Approach |
|---|---|
| Products, categories, prices, sale prices, SKU, stock, images, variations | Native WooCommerce data. No product data in theme code. |
| Condition | Global attribute `pa_condition` (New / Refurbished), product-level. Filter routes: `/shop/?filter_condition=new` / `refurbished` (native layered-nav query var). |
| Variation attributes | Global attributes `pa_storage`, `pa_colour`, `pa_connectivity`, `pa_screen-size` (+ any other). |
| Refurbished details | Product meta: battery health, screen condition, frame condition, included accessories, inspected by / programme (shown only when filled). |
| Archive | `woocommerce/archive-product.php` styled with the existing grid + chips |
| Single product | `woocommerce/single-product.php` + `content-single-product.php` in Gadgero style; native variation form (button disabled until a valid in-stock variation is chosen) |
| Cart / checkout | Classic shortcode cart/checkout, restyled; German locale on those routes; guest checkout on, account creation off |
| Mini-cart | Existing drawer, filled by WooCommerce fragments |

### Normal WordPress pages
Home (static front page), Shop (WC), Cart / Warenkorb, Checkout / Kasse, About, Sell your device, Impressum, Datenschutzerklärung, Widerrufsbelehrung, Muster-Widerrufsformular, Versand & Zahlung, Garantie & Service, Kontakt.

### Reused vs rebuilt
- **Reused as-is:** all CSS tokens, typography (Sora / Manrope), header, hero CSS + Three.js scene, about orbit, value cards, process, buyback layout + dropdown component, footer, toast, drawer styling, device SVG art (fallback when a product has no image), reduced-motion handling.
- **Rebuilt dynamically:** product grid, filters, cart, prices (€), campaign price and CTA, footer contact data, legal links.
- **Extracted to files:** logo PNG, 8 product WebP images, 3D model (`duo.glb`).

## 3. Gaps the brief asks for that need client input
See `docs/CLIENT-CONFIRMATION-REQUIRED.md` (maintained throughout the build).
