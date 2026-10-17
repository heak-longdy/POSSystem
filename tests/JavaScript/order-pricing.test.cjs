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
        'calculatorProductPrice', 'validateOrderBeforeSubmit', 'discountRealTiemAction',
        'discountSelectOpton', 'formatCurrency', 'formatDeductionCurrency', 'mapDetailToCart']) {
        const match = source.match(new RegExp(`^                ${name}\\([^]*?^                },`, 'm'));
        assert.ok(match, `Missing Alpine method: ${name}`);
        const method = match[0].trim().replace(/,$/, '')
            .replace(/@json\(__\('([^']+)'\)\)/g, (_, key) => JSON.stringify(key))
            .replace(/\{\{ asset\('[^']+'\) \}\}/g, '/default.png');
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

test('Typing decimal discounts preserves the input and recalculates both discount types', () => {
    const state = component([{
        product_id: 1, product_type: 'product', product_qty: 3,
        itemData: { price: 10, discount: 0, discountType: null },
    }]);
    const item = state.dataCart[0];
    for (const value of ['', '0', '0.', '0.5', '0.50']) {
        item.itemData.discount = value;
        state.discountRealTiemAction(item);
        assert.equal(item.itemData.discount, value);
        assert.equal(item.itemData.discountType, 'usd');
        assert.deepEqual(state.validateOrderBeforeSubmit(), {});
    }
    assert.equal(state.amountPaid, 28.5);
    assert.equal(state.formatCurrency(item.itemData.discount), '$0.50');
    assert.equal(state.formatDeductionCurrency(state.subTotal - state.total), '-$1.50');

    item.itemData.discountType = 'percent';
    state.discountSelectOpton(null, item);
    assert.equal(item.itemData.discount, '0.50');
    assert.equal(state.amountPaid, 29.85);
    item.itemData.discount = '12.5';
    state.discountRealTiemAction(item);
    assert.equal(state.amountPaid, 26.25);

    item.itemData.discount = '';
    state.discountRealTiemAction(item);
    assert.equal(state.amountPaid, 30);
    assert.deepEqual(state.validateOrderBeforeSubmit(), {});
});

test('Edit Order restores decimal discounts and uses them in displayed totals', () => {
    const state = component([]);
    state.dataCart = ['product', 'service'].map(type => state.mapDetailToCart({
        type, price: 10, qty: 1, [type + '_id']: 1,
        [type + '_discount']: 0.5, [type + '_discount_type']: 'usd',
    }));
    state.calculatorProductPrice();
    assert.equal(state.amountPaid, 19);
    for (const item of state.dataCart) {
        assert.equal(item.itemData.discount, 0.5);
        assert.equal(state.formatCurrency(item.itemData.discount), '$0.50');
        assert.equal(state.formatCurrency(state.cartLineTotal(item)), '$9.50');
    }
});

test('Discount validation accepts decimals and rejects negative or non-numeric values', () => {
    const state = component(fixtures[0].items);
    for (const value of ['0.50', '12.5', '0.001', 0, '', null]) {
        state.dataCart[0].itemData.discount = value;
        assert.deepEqual(state.validateOrderBeforeSubmit(), {});
    }
    for (const value of ['-0.50', 'invalid', '0.5oops', '1e999']) {
        state.dataCart[0].itemData.discount = value;
        assert.deepEqual(state.validateOrderBeforeSubmit().dataCarts, ['order.validation.discount_invalid']);
    }
});
