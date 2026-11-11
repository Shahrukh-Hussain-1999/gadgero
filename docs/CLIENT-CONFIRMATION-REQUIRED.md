# CLIENT CONFIRMATION REQUIRED — Gadgero German launch

Nothing on this list may go live until the client has supplied or approved it. The same items appear in wp-admin (Dashboard → red notice, Products → "Client data" column), and empty contact fields show a red chip to logged-in managers on the storefront.

## 1. Company and legal details (Appearance → Customize → Gadgero: company & contact)
| Item | Used in | Status |
|---|---|---|
| Legal company name incl. legal form | Footer, Impressum, Datenschutz, Widerruf, Muster-Widerrufsformular | missing |
| Business address | Footer, legal pages | missing |
| Support e-mail | Footer, legal pages, service actions | missing |
| Telephone number | Footer, Impressum, Widerruf | missing |
| WhatsApp number | WhatsApp links (buyback, advice, footer) — links currently open WhatsApp without a recipient | missing |
| Opening hours | Footer | missing |
| Returns address / contact | Widerrufsbelehrung, Garantie & Service | missing |
| Delivery time (German text) | Checkout shipping row | missing |
| Representative(s), register court + number, USt-IdNr., person responsible under § 18 (2) MStV, § 36 VSBG statement | Impressum | missing |
| Hosting provider, payment and shipping providers, data protection officer, supervisory authority | Datenschutzerklärung | missing |
| AGB (terms and conditions) — no terms page or checkbox configured | Checkout | missing |

## 2. Commercial settings
| Item | Current local test setup |
|---|---|
| Approved euro prices for every product / variation | Placeholders reused from the approved static demo |
| SKUs | Generated pattern `GAD-…` |
| Stock quantities | Placeholders (1–5); one iPad Air variation deliberately 0 for testing |
| Shipping costs per zone / method | One zone "Deutschland", "Standardversand" 0,00 € (test) |
| Delivery areas | Germany only |
| Payment methods | Only "Vorkasse per Überweisung", no bank details (test) |
| VAT treatment | 19 % standard rate, prices incl. VAT. Confirm whether refurbished devices use the margin scheme (§ 25a UStG) — if so, VAT must not be shown separately for those items |
| Financing / EMI | Removed from the site. Needs provider, terms and integration before it can return |
| Discounts (crossed-out prices) | None set. The demo's "was" prices were removed because they were not approved |

## 3. Warranty and after-sales
Warranty provider, duration, coverage, claim process, fault reporting and return process are all placeholders on "Garantie & Service". The trust-strip "Shop warranty" claim was removed.

## 4. Products requiring verification
| Product | Price | SKU | Colour | Image | Specs / other |
|---|---|---|---|---|---|
| Apple iPhone Duo | ✔ needed | ✔ | colour options unknown (only storage 256/512 GB seeded) | **no product photo** — illustration on product page, 3D model in banner | storage options, "tablet-size OLED" claim, hinge description |
| iPhone 17 Pro | ✔ | ✔ | Silver / Cosmic Orange / Deep Blue seeded per client. "Burgundy" removed | **both demo photos removed**: they are concept renders, the "blue" is light sky-blue (not Deep Blue) and "burgundy" is not an iPhone 17 Pro finish | storage options |
| iPhone 16 | ✔ | ✔ | Ultramarine | illustration only | — |
| iPhone 15 (refurbished) | ✔ | ✔ | Black | illustration only (labelled as illustrative) | battery 94 % from demo data; screen / frame condition, accessories, inspector missing |
| Galaxy S26 / S26+ / S26 Ultra | ✔ | ✔ | Cobalt Violet / Onyx Black / Silver (from demo) | photos from demo — confirm they match the stocked finish | — |
| Apple Watch Ultra | ✔ | ✔ | Natural Titanium | photo resembles Ultra 2 | **exact generation** |
| AirPods Pro | ✔ | ✔ | — | photo resembles AirPods Pro 2 | **exact generation** |
| iPad Air M3 (16 variations) | ✔ | ✔ | Blue / Space Grey seeded | one photo showing both sizes — needs per-size/colour images | storage 128/256 only seeded; 512 GB / 1 TB and Purple / Starlight not seeded |
| iPad 10th Gen (refurbished) | ✔ | ✔ | Silver | illustration only | battery 96 % from demo data; condition details missing |
| MacBook Air 13" M4, MacBook Pro 14" M5 | ✔ | ✔ | Midnight / Space Black | illustration only | RAM / storage from demo |
| MacBook Air 13" M2 (refurbished) | ✔ | ✔ | Silver | illustration only | battery 91 % from demo data; condition details missing |
| iMac 24" M4, Mac mini M4 | ✔ | ✔ | Blue / Silver | illustration only | RAM / storage from demo |
| All new devices | — | — | — | — | "Original factory-sealed packaging" is OFF everywhere until confirmed per product |
| All refurbished devices | — | — | — | — | "Inspected / certified by" and "programme" left empty; no certification claims shown |

## 5. Copy and claims
- About: client text in place. Removed: Copenhagen story, "10 yrs", "25,000+ devices", "40 checks", "4.9/5", "compare against the market every week", "repair, replace or refund". Ratings can return only with source, review count and a link.
- Buyback: removed "usually within the hour", "paid the same day", "bring the device to the shop". Which devices and tablet brands are accepted for buyback needs confirmation.
- Homepage campaign slide 2 tags: "Foldable", "Tablet-size OLED" (editable in Customizer) — confirm.
- Footer strings on the German pages ("Visit", "Contact", "Service") stay English, following the decision that only cart, checkout, e-mails and legal pages are German.

## 6. Legal review before launch
- All legal pages are drafts. Widerrufsbelehrung and Muster-Widerrufsformular follow the statutory templates and need a legal check.
- **Electronic withdrawal function** ("Widerrufsbutton") — EU Directive 2023/2673, applicable since 19.06.2026. Confirm the German requirements and add the function before launch.
- Google Fonts and three.js are loaded from external CDNs (as in the approved static site). Self-host both before launch (GDPR); the privacy-policy draft mentions this.
