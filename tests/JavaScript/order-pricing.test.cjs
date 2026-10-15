const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');

const source = fs.readFileSync(path.join(__dirname, '../../resources/admin/views/pages/order/createOrder.blade.php'), 'utf8');
const fixtures = require('../Fixtures/order-pricing.json');

// Execute the actual Alpine methods, so changes to the page are covered.
function component(items) {
    const state = { dataCart: structuredClone(items), saveDraft() {}, canEditOrderItems: () => true,
        formData: { shop_id: 1, customer_id: 1, order_date: '2026-10-15' } };
    for (const name of ['cartLineTotal', 'cartQuantity', 'roundMoney', 'totalDiscount', 'getCommssion',
        'calculatorProductPrice', 'validateOrderBeforeSubmit']) {
        const match = source.match(new RegExp(`^                ${name}\\([^]*?^                },`, 'm'));
        assert.ok(match, `Missing Alpine method: ${name}`);
        const method = match[0].trim().replace(/,$/, '')
            .replace(/@json\(__\('([^']+)'\)\)/g, (_, key) => JSON.stringify(key));
        Object.assign(state, Function(`return ({${method}});`)());
    }
    state.calculatorProductPrice();
    return state;
}

for (const fixture of fixtures) {
    test(`Create Order: ${fixture.name}`, () => {
        const state = component(fixture.items);
        assert.deepEqual(state.dataCart.map(item => state.cartLineTotal(item)), fixture.lines);
        assert.equal(state.subTotal, fixture.subtotal);
        assert.equal(state.roundMoney(state.subTotal - state.total), fixture.discount);
        assert.equal(state.commissionTotal, fixture.commission);
        assert.equal(state.amountPaid, fixture.total);
        state.formData.partial_payment_amount = fixture.total;
        assert.deepEqual(state.validateOrderBeforeSubmit(), {});
        state.formData.partial_payment_amount = fixture.total + 0.01;
        assert.ok(state.validateOrderBeforeSubmit().partial_payment_amount);
    });
}

test('Price and quantity edits recalculate without relying on cached discounted totals', () => {
    const state = component(fixtures[0].items);
    state.dataCart[0].itemData.price = 3;
    state.dataCart[0].product_qty = 10;
    state.dataCart[0].itemData.total = 999;
    state.calculatorProductPrice();
    assert.equal(state.amountPaid, 25.48);
    state.dataCart[0].itemData.discount = 0;
    state.calculatorProductPrice();
    assert.equal(state.amountPaid, 35.48);
});

test('Editing a pending order validates against its recalculated discounted total', () => {
    const state = component(fixtures[0].items);
    state.orderId = 13;
    state.remainingAmount = 61.28;
    state.formData.partial_payment_amount = 41.29;
    assert.ok(state.validateOrderBeforeSubmit().partial_payment_amount);
    state.formData.partial_payment_amount = 41.28;
    assert.deepEqual(state.validateOrderBeforeSubmit(), {});
});
