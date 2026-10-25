# Implementation Plan: Automatic USD Discount on Create Order and Edit Order

**Date:** 24 October 2026  
**Status:** Approved implementation completed on 25 October 2026; deployment requires the reviewed pricing-field migration. No development or production database scripts have been executed.  
**Scope:** Automatic product discounts in USD ($) on the existing Create Order and Edit Order pages.

## 1. Feature requirements

For each product in Order Items:

1. Make Unit Price editable wherever the existing page allows item editing.
2. Keep Original Price unchanged as the reference for that line.
3. Replace the editable product Discount input and discount-type selector with a read-only USD amount.
4. Calculate the discount automatically whenever Unit Price changes.
5. Recalculate the extended discount and line total when quantity changes, using the existing quantity rules.
6. Feed those amounts into the existing order-summary calculation.
7. Save and restore the prices consistently so Create and Edit use the same calculation.

**Automatic discounts are fixed USD amounts only. Do not derive, store, return, or display a discount percentage for this feature.**

Only changes directly necessary for these requirements belong in the implementation. Preserve all other existing functionality, business rules, page behavior, and database processes. This document contains no tasks for other pages or modules and no general refactoring or cleanup requirements.

## 2. Relevant current implementation

The source review identifies these feature-specific facts:

- `OrderController::onCreate()` and `OrderController::edit()` both render `admin::pages.order.createOrder`. The two pages therefore share one form implementation.
- The selected-product table currently displays Unit Price as text and accepts a manual discount.
- `addToCart()` takes the shop product price, with the existing product-price fallback. `mapDetailToCart()` restores the saved detail price and discount.
- `priceRealTimeAction()` exists but is not connected to an editable Unit Price in the current product row.
- `cartLineTotal()` and `calculatorProductPrice()` calculate the displayed line and summary amounts. `OrderPricing` calculates saved amounts on the server.
- `order_details.price` is the saved pre-discount price. `product_discount` and `product_discount_type` store the existing discount. There is no separate immutable original-price snapshot or edited net unit-price field.
- The existing edit save recreates detail rows. Saved original-price information must therefore be retained before that existing recreation occurs.

| Component | Necessary change only |
| --- | --- |
| [createOrder.blade.php](../resources/admin/views/pages/order/createOrder.blade.php) | Editable product Unit Price; read-only Original Price and USD discount; automatic calculation; restore the same pricing state on Edit. |
| [OrderController.php](../app/Http/Controllers/Admin/OrderController.php) | Resolve the trusted reference, preserve it on edit, derive the USD discount, and persist the required product pricing values within the current save path. |
| [OrderRequest.php](../app/Http/Requests/Admin/OrderRequest.php) / existing cart validation | Validate only the new price/reference inputs needed by this feature. |
| [OrderPricing.php](../app/Services/OrderPricing.php) | Support automatic product-line amounts while retaining the existing calculations for other lines and the existing order-total formula. |
| [OrderDetail.php](../app/Models/OrderDetail.php) | Represent the pricing fields necessary to retain Original Price and Edited Unit Price across save/reload. |
| [English order labels](../lang/en/order.php), [Khmer order labels](../lang/km/order.php) | Add only labels and validation messages used by the affected product fields. |

These are specific edit locations, not authorization to refactor entire files. Source review does not establish the deployed database schema; the persistence proposal below remains subject to review.

## 3. Automatic Discount Calculation — USD only

Define:

```text
O = Original Price in USD
U = Edited Unit Price in USD
q = quantity produced by the existing quantity rules

Automatic Discount per Unit = max(O − U, 0)
Line Discount               = Automatic Discount per Unit × q
Line Total                  = U × q
```

The zero minimum prevents a negative discount when the edited price is higher than the original.

| Edited Unit Price | Automatic discount | Charged unit price |
| --- | --- | --- |
| Lower than Original Price | Original Price minus Edited Unit Price | Edited Unit Price |
| Equal to Original Price | $0.00 | Edited Unit Price |
| Higher than Original Price | $0.00 | Edited Unit Price |

For Original Price **$9.30** and Edited Unit Price **$9.00**, display **$0.30** as the discount per unit. At quantity three, display a line discount of **$0.90** and a line total of **$27.00**.

Changing the edited price to **$8.80** changes the discount to **$0.50** per unit. At quantity three, the line discount becomes **$1.50** and the line total becomes **$26.40**. Original Price stays **$9.30**.

### Existing summary integration

Use a pre-discount billing price of `B = max(O, U)`:

```text
Line Subtotal = B × q
Line Discount = max(O − U, 0) × q
Line Total    = Line Subtotal − Line Discount = U × q
```

This preserves the existing subtotal-minus-discount relationship even when the edited price increases. For `O=$9.30`, `U=$10.00`, and `q=2`, subtotal is `$20.00`, discount is `$0.00`, and line total is `$20.00`; Original Price remains `$9.30`.

Pass these line amounts into the existing summary calculator. Keep its other adjustments and business rules unchanged. Never subtract the generated discount a second time from `U`, which is already the selling price.

## 4. Create and Edit behavior

### Create Order

- Capture Original Price from the selected product's existing shop-price source and fallback. An explicit zero is a price, not a missing value.
- Initially use the existing effective selling price as Unit Price. For a product without an existing adjustment, this equals Original Price and the discount is `$0.00`.
- Display Original Price as read-only. Entering a different Unit Price changes the generated discount, not the reference or catalog price.
- Replace the product discount controls with existing-style currency text, such as `$0.30 / unit`. Show extended discounts in USD where the existing row or summary uses extended amounts.
- Recalculate after every valid Unit Price edit and each existing quantity-change action.

### Edit Order

- Restore the saved Original Price, Edited Unit Price, and automatic USD discount.
- Use the same input behavior and calculation as Create Order.
- Preserve the saved Original Price when the catalog price changes later.
- Continue using the existing item-editability condition; adding the price input must not enable edits that the page currently disallows.
- Adding more units of a retained line must preserve its edited price and reference.

Extend the existing cart mapping and draft save/restore methods only enough to carry the required pricing values. Keep their existing storage, lifecycle, and other state unchanged. Reuse current input styles and validation presentation; no form redesign is proposed.

## 5. Price data required for save and reload

The server must calculate the discount from a trusted Original Price and the validated Edited Unit Price. A posted discount or total is not authoritative.

For a new line, resolve the reference from the selected shop/product. Compare it with the reference shown on the form; a mismatch must be identified before saving rather than silently changing the discount. For a retained saved line, use its stored reference instead of the current catalog. Validate the submitted detail identity only as necessary to retrieve the correct saved reference.

Before the existing edit path recreates details, retain their pricing values. Save the new pricing fields within that same path, leaving its execution sequence and all unrelated processing unchanged.

### Proposed pricing fields

A separate snapshot is required to preserve both Original Price and a higher edited selling price across save/reload. The current single price field cannot independently represent both.

| Proposed detail field | Purpose |
| --- | --- |
| `original_price`, nullable `DECIMAL(15,2)` | Saved immutable reference for an automatic product line. |
| `unit_price`, nullable `DECIMAL(15,2)` | Saved edited selling price. |
| `pricing_method`, nullable `VARCHAR(32)` | `auto_unit_price_v1` identifies automatic lines; null retains existing line interpretation. |

Generate the existing compatibility fields on the server:

```text
price                 = max(original_price, unit_price)
product_discount      = max(original_price − unit_price, 0)
product_discount_type = 'usd'
```

Use the new decimal fields as the authoritative automatic-pricing inputs. Existing field meanings and existing casts elsewhere remain unchanged. No additional discount-percentage field is needed.

The following SQL is a **proposal for manual review only**, retained solely because these fields support the requested save/reload behavior. Confirm whether the fields already exist before considering it. No database process changes, migration execution, data conversion, or backfill are part of this planning task.

```sql
-- Proposed only; not executed. Verify the actual schema before manual approval.
ALTER TABLE order_details
    ADD COLUMN original_price DECIMAL(15,2) NULL,
    ADD COLUMN unit_price DECIMAL(15,2) NULL,
    ADD COLUMN pricing_method VARCHAR(32) NULL;
```

## 6. Existing saved discounts on Edit Order

Existing rows retain their saved discount interpretation until the user explicitly changes that product's Unit Price. Loading the page, focusing/blurring an unchanged input, or changing another line must not convert or round that saved discount.

- Derive the existing effective Unit Price using the current legacy calculator and display the monetary discount in USD only. Do not derive or display a discount percentage.
- For a quantity-only edit, retain the saved per-unit pricing rule and its existing extended-line rounding.
- On an explicit Unit Price edit, convert only that product line to automatic USD pricing. Use its saved pre-discount price as the reference, not today's catalog price.
- That saved reference is the historical value available in the record; it cannot recover an original catalog price that was never stored.
- Do not bulk-convert existing rows or alter unrelated saved values.

Precision must survive an unchanged edit. For example, an existing effective unit price of `$17.45625` at quantity three produces a rounded line total of `$52.37`. Silently replacing it with `$17.46` would produce `$52.38`. Preserve the existing precision until the user deliberately enters a new two-decimal Unit Price, and show the resulting amount before saving.

If a saved reference or existing effective price cannot be represented faithfully, identify that line-specific limitation rather than silently rounding it or adding a data-repair process.

## 7. Precision and price validation

- New automatic Unit Prices use nonnegative USD amounts with at most two decimal places. Display calculated discounts with exactly two decimal places.
- Parse new price text into integer cents, then subtract and multiply using cents. Use the same calculation contract in JavaScript and PHP.
- Preserve intermediate typed text such as `9.` while editing. Blank or invalid input at submit is an error, not a zero-price sale. Explicit `0` is valid.
- Reject negative, malformed, non-finite, excessive, or over-precision new price inputs. Guard automatic-price arithmetic against overflow.
- Preserve existing quantity normalization and use its resulting quantity. Do not add a new quantity policy.
- Original Price is not editable or trusted from client input. Recompute the generated USD discount server-side and preserve the saved reference for retained automatic lines.
- Keep legacy rounding intact. Do not change shared monetary helpers or other pricing rules to implement this product-specific calculation.
- Do not create a ratio, percentage calculation, percentage output, or percentage hint for automatic discounts.

## 8. Implementation steps after plan approval

1. Extend the existing pricing fixtures with the USD-only cases below and retain the current legacy expectations.
2. Confirm the minimal pricing-field proposal required to preserve Original Price on save/reload. No database action is authorized by this document.
3. Add the automatic product calculation and price validation to the existing frontend/backend pricing paths. Preserve all other calculation branches.
4. Update the shared product row: editable Unit Price, read-only Original Price, and read-only USD discount. Connect valid price and existing quantity changes to the calculator.
5. Restore the required price fields on Edit and through existing cart state. Retain unchanged saved discounts until an explicit price edit.
6. Persist the server-calculated fixed USD discount and retain original-price snapshots through the existing detail recreation. Leave the surrounding save process unchanged.
7. Verify the calculations, Create/Edit interaction, and save/reload behavior below. Review each changed line against this feature's scope; remove unrelated changes.

## 9. Focused verification scenarios

For automatic cases, use the same data on Create and on an editable saved order. Amounts below assume no other existing adjustments so the expected product calculations are isolated.

| Case | Original | Edited Unit | Qty | Discount/unit | Line Discount | Line Total |
| --- | ---: | ---: | ---: | ---: | ---: | ---: |
| Requested example | $9.30 | $9.00 | 1 | $0.30 | $0.30 | $9.00 |
| Quantity change | $9.30 | $9.00 | 3 | $0.30 | $0.90 | $27.00 |
| Further price reduction | $9.30 | $8.80 | 3 | $0.50 | $1.50 | $26.40 |
| Equal prices | $9.30 | $9.30 | 3 | $0.00 | $0.00 | $27.90 |
| Price increase | $9.30 | $10.00 | 2 | $0.00 | $0.00 | $20.00 |
| Zero selling price | $9.30 | $0.00 | 2 | $9.30 | $18.60 | $0.00 |
| Zero reference | $0.00 | $1.25 | 2 | $0.00 | $0.00 | $2.50 |
| Cent precision | $0.30 | $0.29 | 3 | $0.01 | $0.03 | $0.87 |

For each case:

1. Select the product, enter Unit Price, and change quantity using the existing controls.
2. Verify the unchanged Original Price, read-only USD discount, line total, and corresponding summary amounts.
3. Verify there is no editable product discount control, discount percentage, or percentage hint.
4. Save and reopen Edit. Confirm the same reference, Unit Price, fixed USD discount, and totals.
5. Edit Unit Price again and verify the discount updates immediately without being deducted twice.

Additional checks directly required by this feature:

| Check | Expected result |
| --- | --- |
| Change price from `$9.00` back to `$9.30` | Discount becomes `$0.00`; no previous discount remains applied. |
| Increase quantity from three to five at `O=$9.30`, `U=$9.00` | Discount/unit stays `$0.30`; line discount becomes `$1.50`; line total becomes `$45.00`. |
| Two automatic lines: `$9.30 → $9.00`, qty three; `$2.79 → $1.79`, qty two | Subtotal `$33.48`; discount `$2.90`; combined line total `$30.58`. Existing other summary adjustments are unchanged. |
| Catalog price changes after save | Editing the retained line still uses its saved Original Price. |
| Posted original/discount/total is altered | Trusted reference and server-calculated USD discount determine the saved amount. |
| Enter blank, negative, malformed, or more-than-two-decimal new Unit Price | Show a price-specific validation error without silently substituting zero or rounding the entry. |
| Type `9`, `9.`, `9.0`, `9.00` | Input remains usable; final valid value is `$9.00`, discount `$0.30`. |
| Open an existing discounted line without changing Unit Price | Saved pricing inputs and established rounding remain intact; monetary discount is displayed in USD. |
| Existing effective unit `$17.45625`, qty three, unchanged | Existing rounded line total remains `$52.37`. |
| Explicitly enter `$17.46` for that legacy line | Only that line adopts automatic USD pricing; preview shows the new line total `$52.38` before save. |
| Existing page prohibits item editing | New Unit Price input remains disabled by the same condition. |
| Restore cart/draft pricing state | Original Price and Edited Unit Price restore consistently; existing draft behavior stays unchanged. |

Extend [OrderPricingTest.php](../tests/Unit/OrderPricingTest.php), [order-pricing.json](../tests/Fixtures/order-pricing.json), and [order-pricing.test.cjs](../tests/JavaScript/order-pricing.test.cjs) for calculation parity. Add only the necessary save/reload assertions to the existing order tests and browser checks for the changed product inputs. Test changes must not introduce new unrelated behavior requirements.

**Acceptance:** both Create Order and Edit Order automatically calculate and display the fixed USD difference, retain the correct Original Price after save/reload, update product line and existing summary amounts, and preserve all unrelated behavior.

## 10. Implementation record — 25 October 2026

The shared Create/Edit product row now accepts Unit Price and shows Original Price and a read-only USD discount. JavaScript and `OrderPricing` calculate automatic prices in integer cents. The save path derives compatibility fields on the server, validates product references, and preserves original snapshots before the existing detail-replacement step. Legacy saved pricing remains unchanged until that product's Unit Price is edited. Service discount controls and the surrounding order workflow retain their existing behavior.

Verification passed: 42 PHP tests (589 assertions), 26 JavaScript tests, and the browser checks. These cover the table above, combined product lines, invalid prices, altered submitted amounts, saved references after catalog changes, legacy fractional-cent pricing, and existing order/payment behavior. The browser checks exercise decimal typing, live USD discounts, quantity changes, legacy and automatic Edit, mobile inputs, and disabled inputs on locked orders. Database-backed tests use SQLite `:memory:` only.

Required manual deployment step: review and apply [the additive pricing-field migration](../database/migrations/2026_10_25_000000_add_automatic_product_pricing_to_order_details_table.php) before using the updated product save flow. It adds only the three nullable fields described above and performs no backfill or conversion of existing records. Use the migration **or** the equivalent reviewed SQL from section 5, not both. No migration has been applied to the development or production database by this implementation.

Focused verification commands:

```sh
vendor/bin/phpunit --filter 'OrderPricingTest|OrderPaymentTest'
node --test tests/JavaScript/order-pricing.test.cjs
# Requires a disposable Chromium browser at BROWSER_DEBUG_URL (default localhost:9234).
node tests/JavaScript/order-product-picker.browser.mjs
```
