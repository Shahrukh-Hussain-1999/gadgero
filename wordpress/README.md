# Gadgero — WordPress + WooCommerce build

The approved static design (`../index.html`) rebuilt as a custom WooCommerce theme.

```
wordpress/
  themes/gadgero/        custom theme (junctioned into the WP install)
    assets/css/          gadgero.css = approved static CSS, unchanged · shop.css = WP/WooCommerce views
    assets/js/           hero.js (3D scroll hero, ported) · site.js · product.js (variation pills)
    assets/model/duo.glb 3D model (was model.wasm)
    inc/                 setup, customizer, WooCommerce integration, product fields, admin warnings, shortcodes
    template-parts/home/ hero, trust, routes, shop, about, process, buyback
    woocommerce/         archive-product, single-product, content-product overrides
  mu-plugins/gadgero-locale.php   English storefront, German (formal) cart/checkout/e-mails/legal pages
  setup/install-core.php          installs WP core without WP-CLI
  setup/seed.php                  settings, pages, menus, attributes, TEST catalogue (re-runnable)
  setup/content/*.html            German legal / service page drafts
  bin/                            wp-cli.phar + `wp` wrapper (git-ignored)
```

## Local install (Laragon)
- Site: http://localhost/gadgero-wp/ (files in `C:\laragon\www\gadgero-wp`, database `gadgero_wp`, table prefix `gd_`)
- Theme and mu-plugins are directory junctions into this repository, so edits here are live.
- wp-admin login: `wordpress/setup/local-credentials.txt` (git-ignored, local test account only).

Re-seed (deletes and recreates the test products):
```bash
wordpress/bin/wp eval-file wordpress/setup/seed.php
```

## Where things are edited
| What | Where |
|---|---|
| Products, prices, sale prices, SKU, stock, images, variations | WooCommerce → Products |
| New / Refurbished | Product → Attributes → **Condition** (product-level, used for the shop routes `?filter_condition=new|refurbished`) |
| Battery health, screen/frame condition, accessories, factory-sealed, inspected-by, illustration, "data confirmed" | Product → **Gadgero** tab |
| Homepage campaign (product, lettering, slide texts) | Appearance → Customize → Gadgero: homepage campaign |
| Company, contact, hours, returns address, delivery time | Appearance → Customize → Gadgero: company & contact |
| Menus | Appearance → Menus (Header, Legal, Service) |

## Key behaviours
- The hero's name, "From" price and CTA come from the campaign product. The price is the cheapest **in-stock, purchasable** variation; price and CTA hide automatically if the product is unpublished, not purchasable or sold out.
- Variation selection, price, stock, availability and the add-to-cart rule are native WooCommerce. The pills only drive the native selects.
- The exact configuration is one line ("11-inch · 128 GB · Blue · Wi-Fi · Neu") in the product page, mini-cart, cart, checkout, order (stored as order item meta `Konfiguration`), confirmation page and e-mails.
- WhatsApp is advice, stock questions and buyback only. Cart and checkout say that binding orders are only possible through the checkout.
- Before launch, see `../docs/CLIENT-CONFIRMATION-REQUIRED.md`.
