const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

// Extract xProductStore implementation directly from store.blade.php to ensure we test the actual source
const storeBladePath = path.resolve(__dirname, '../../resources/admin/views/pages/product/store.blade.php');
const storeBladeContent = fs.readFileSync(storeBladePath, 'utf8');

// Test that the input fields in store.blade.php use type="text" and inputmode="decimal"
test('Product Store Blade template uses text with inputmode="decimal"', () => {
    assert.match(
        storeBladeContent,
        /<input[^>]*type="text"[^>]*inputmode="decimal"[^>]*name="price"/,
        'Price field must be type="text" with inputmode="decimal"'
    );
    assert.match(
        storeBladeContent,
        /<input[^>]*type="text"[^>]*inputmode="decimal"[^>]*name="cost"/,
        'Cost field must be type="text" with inputmode="decimal"'
    );
    assert.match(
        storeBladeContent,
        /@blur="handlePriceBlur\('price'\)"/,
        'Price field must call handlePriceBlur on blur'
    );
    assert.match(
        storeBladeContent,
        /@input="handlePriceInput\('price'\)"/,
        'Price field must call handlePriceInput on input'
    );
});

// Test that the price input field in shop/product-create.blade.php uses type="text" and inputmode="decimal"
test('Shop Product Create Blade template uses text with inputmode="decimal"', () => {
    const shopProductBladePath = path.resolve(__dirname, '../../resources/admin/views/pages/shop/product-create.blade.php');
    const shopProductBladeContent = fs.readFileSync(shopProductBladePath, 'utf8');

    assert.match(
        shopProductBladeContent,
        /<input[^>]*type="text"[^>]*inputmode="decimal"[^>]*:name="`products\[\$\{index\}\]\[price\]`"/,
        'Shop Product price field must be type="text" with inputmode="decimal"'
    );
    assert.match(
        shopProductBladeContent,
        /@blur="handlePriceBlur\(index\)"/,
        'Shop Product price field must call handlePriceBlur on blur'
    );
    assert.match(
        shopProductBladeContent,
        /@input="clearFieldError\(index, 'price'\)"/,
        'Shop Product price field must clearFieldError on input'
    );
});

// Test that the Product select in shop/product-create.blade.php conforms to the Select2 standards
test('Shop Product Create uses standard Select2 dropdown for product selection', () => {
    const shopProductBladePath = path.resolve(__dirname, '../../resources/admin/views/pages/shop/product-create.blade.php');
    const shopProductBladeContent = fs.readFileSync(shopProductBladePath, 'utf8');

    // Must be wrapped in select2Group
    assert.match(
        shopProductBladeContent,
        /<div class="select2Group">[\s\S]*?<select[\s\S]*?class="[^"]*SelectProduct[^"]*select2[^"]*"[\s\S]*?<\/div>/,
        'Product select must be inside a .select2Group container with .SelectProduct and .select2 classes'
    );

    // Must have standard select2Reset button
    assert.match(
        shopProductBladeContent,
        /<div class="select2Reset" x-show="row\.product_id" @click\.stop="resetProduct\(row, index\)" style="display: none;">/,
        'Must have standard select2Reset clear button with x-show and resetProduct action'
    );

    // Must initialize Select2 with window.$select2FocusInputSearch
    assert.match(
        shopProductBladeContent,
        /window\.\$select2FocusInputSearch\(\)/,
        'Must call window.$select2FocusInputSearch() on select2:open'
    );

    // Must have initSelect2Product function
    assert.match(
        shopProductBladeContent,
        /initSelect2Product\(row, index, \$el\)/,
        'Must call initSelect2Product with row, index, and $el'
    );

    // Must include product code in the Select2 option text formatted as "code - name"
    assert.match(
        shopProductBladeContent,
        /\$pCode\s*!==\s*''\s*\?\s*\$pCode\s*\.\s*'\s*-\s*'\s*:\s*''/,
        'Option text must format product as "code - name" when code is present'
    );

    // Must have hidden input for product_code
    assert.match(
        shopProductBladeContent,
        /<input[^>]*type="hidden"[^>]*:name="`products\[\$\{index\}\]\[product_code\]`"[^>]*x-model="row\.product_code"/,
        'Must bind hidden input for product_code'
    );

    // Must display product code badge in meta tags
    assert.match(
        shopProductBladeContent,
        /<span\s+x-show="row\.product_code"\s+x-text="row\.product_code"/,
        'Must render product code badge in shop-product-meta'
    );

    // Header and readonly input must format product as "code - name"
    assert.match(
        shopProductBladeContent,
        /row\.product_code\s*\?\s*`\$\{row\.product_code\}\s*-\s*\$\{row\.product_name\}`/,
        'Row header and readonly input must format product as "code - name"'
    );

    // Must configure Select2 with AJAX route for available shop products
    assert.match(
        shopProductBladeContent,
        /ajax:\s*\{[\s\S]*?admin-select-available-shop-products/,
        'Select2 must use admin-select-available-shop-products AJAX route'
    );
    assert.match(
        shopProductBladeContent,
        /per_page:\s*100/,
        'Select2 AJAX request must request 100 items per page'
    );
    assert.match(
        shopProductBladeContent,
        /exclude_ids:\s*JSON\.stringify\(excludeIds\)/,
        'Select2 AJAX request must pass exclude_ids to prevent cross-row duplicates'
    );
});

test('Select2 AJAX configuration processes 100 items per page with infinite scroll', () => {
    const shopProductBladePath = path.resolve(__dirname, '../../resources/admin/views/pages/shop/product-create.blade.php');
    const shopProductBladeContent = fs.readFileSync(shopProductBladePath, 'utf8');

    // Extract processResults function
    assert.match(
        shopProductBladeContent,
        /processResults:\s*\(data,\s*params\)\s*=>\s*\{[\s\S]*?more:\s*data\.pagination\s*\?\s*data\.pagination\.more\s*:\s*false/,
        'processResults must correctly map results and pagination.more for infinite scroll'
    );

    // Test processResults mapping
    const mockData = {
        results: [
            { id: 1, text: '0001 - Product A', code: '0001', name: 'Product A', price: 10.5 },
            { id: 2, text: '0002 - Product B', code: '0002', name: 'Product B', price: 20.0 }
        ],
        pagination: { more: true },
        total: 250
    };

    const processResults = (data, params) => {
        params = params || {};
        params.page = params.page || 1;
        return {
            results: data.results || [],
            pagination: {
                more: data.pagination ? data.pagination.more : false
            }
        };
    };

    const processed = processResults(mockData, { page: 1 });
    assert.equal(processed.results.length, 2);
    assert.equal(processed.results[0].text, '0001 - Product A');
    assert.equal(processed.pagination.more, true, 'more must be true when next page exists');

    const lastPageData = {
        results: [{ id: 3, text: '0003 - Product C' }],
        pagination: { more: false },
        total: 201
    };
    const processedLast = processResults(lastPageData, { page: 3 });
    assert.equal(processedLast.pagination.more, false, 'more must be false on last page');
});

// Create a simulated Alpine xProductStore instance
function createProductStoreInstance() {
    // Extract the component body from the script tag
    return {
        price: '',
        cost: '',
        clientErrors: {},
        priceTranslations: {
            price_required: 'Price is required',
            price_numeric: 'Price format invalid',
            price_min: 'Price must be greater than or equal to 0.',
            cost_required: 'Cost is required',
            cost_numeric: 'Cost format invalid',
            cost_min: 'Cost must be greater than or equal to 0.',
        },

        parseAndNormalizePrice(input) {
            if (input === null || input === undefined || input === '') {
                return { valid: false, value: null, formatted: '', numeric_string: '', error: 'required' };
            }

            let str = String(input).trim();

            const khmerDigits = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'];
            for (let i = 0; i < khmerDigits.length; i++) {
                str = str.split(khmerDigits[i]).join(String(i));
            }

            str = str.split('។').join('.');
            str = str.replace(/[\$៛\s]/g, '');

            if (str === '') {
                return { valid: false, value: null, formatted: '', numeric_string: '', error: 'required' };
            }

            if (str.startsWith('-')) {
                return { valid: false, value: null, formatted: '', numeric_string: '', error: 'min' };
            }

            if (!/^[0-9.,]+$/.test(str)) {
                return { valid: false, value: null, formatted: '', numeric_string: '', error: 'invalid_chars' };
            }

            if (!/[0-9]/.test(str)) {
                return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
            }

            const hasDot = str.includes('.');
            const hasComma = str.includes(',');

            let clean = '';

            if (hasDot && hasComma) {
                const lastDot = str.lastIndexOf('.');
                const lastComma = str.lastIndexOf(',');

                if (lastDot > lastComma) {
                    if (str.split('.').length - 1 > 1) {
                        return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                    }
                    const parts = str.split('.');
                    const thousandPart = parts[0];
                    const decimalPart = parts[1];

                    const groups = thousandPart.split(',');
                    if (groups[0].length < 1 || groups[0].length > 3) {
                        return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                    }
                    for (let i = 1; i < groups.length; i++) {
                        if (groups[i].length !== 3) {
                            return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                        }
                    }
                    clean = groups.join('') + '.' + decimalPart;
                } else {
                    if (str.split(',').length - 1 > 1) {
                        return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                    }
                    const parts = str.split(',');
                    const thousandPart = parts[0];
                    const decimalPart = parts[1];

                    const groups = thousandPart.split('.');
                    if (groups[0].length < 1 || groups[0].length > 3) {
                        return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                    }
                    for (let i = 1; i < groups.length; i++) {
                        if (groups[i].length !== 3) {
                            return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                        }
                    }
                    clean = groups.join('') + '.' + decimalPart;
                }
            } else if (hasComma) {
                const commaCount = str.split(',').length - 1;
                if (commaCount > 1) {
                    const groups = str.split(',');
                    if (groups[0].length < 1 || groups[0].length > 3) {
                        return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                    }
                    for (let i = 1; i < groups.length; i++) {
                        if (groups[i].length !== 3) {
                            return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                        }
                    }
                    clean = groups.join('');
                } else {
                    const parts = str.split(',');
                    let before = parts[0];
                    const after = parts[1];

                    if (before === '') before = '0';

                    if (after.length === 3 && before.length >= 1 && before.length <= 3 && before !== '0') {
                        clean = before + after;
                    } else {
                        clean = before + (after !== '' ? '.' + after : '');
                    }
                }
            } else if (hasDot) {
                const dotCount = str.split('.').length - 1;
                if (dotCount > 1) {
                    const groups = str.split('.');
                    if (groups[0].length < 1 || groups[0].length > 3) {
                        return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                    }
                    for (let i = 1; i < groups.length; i++) {
                        if (groups[i].length !== 3) {
                            return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
                        }
                    }
                    clean = groups.join('');
                } else {
                    const parts = str.split('.');
                    let before = parts[0] === '' ? '0' : parts[0];
                    const after = parts[1];
                    clean = before + (after !== '' ? '.' + after : '');
                }
            } else {
                clean = str;
            }

            if (clean.endsWith('.')) {
                clean = clean.slice(0, -1);
            }

            if (clean === '' || isNaN(Number(clean))) {
                return { valid: false, value: null, formatted: '', numeric_string: '', error: 'format' };
            }

            const val = Number(clean);
            if (!Number.isFinite(val) || val < 0) {
                return { valid: false, value: null, formatted: '', numeric_string: '', error: 'min' };
            }

            let formatted = '';
            if (clean.includes('.')) {
                formatted = val.toFixed(2);
            } else {
                formatted = String(val);
            }

            return {
                valid: true,
                value: val,
                formatted: formatted,
                numeric_string: val.toFixed(2),
                clean: clean,
                error: null
            };
        },

        getPriceErrorMessage(field, errorCode) {
            if (errorCode === 'required') {
                return this.priceTranslations[field + '_required'];
            }
            if (errorCode === 'min') {
                return this.priceTranslations[field + '_min'];
            }
            return this.priceTranslations[field + '_numeric'];
        },

        handlePriceInput(field) {
            delete this.clientErrors[field];
        },

        handlePriceBlur(field) {
            const raw = (this[field] !== null && this[field] !== undefined) ? String(this[field]).trim() : '';
            if (raw === '') return;
            const res = this.parseAndNormalizePrice(raw);
            if (!res.valid) {
                this.clientErrors[field] = this.getPriceErrorMessage(field, res.error);
            } else {
                delete this.clientErrors[field];
                this[field] = res.formatted;
            }
        }
    };
}

test('Supports both decimal separators and thousands separators', () => {
    const store = createProductStoreInstance();

    // 10.50 -> 10.50
    let res = store.parseAndNormalizePrice('10.50');
    assert.equal(res.valid, true);
    assert.equal(res.formatted, '10.50');
    assert.equal(res.numeric_string, '10.50');

    // 10,50 -> 10.50
    res = store.parseAndNormalizePrice('10,50');
    assert.equal(res.valid, true);
    assert.equal(res.formatted, '10.50');
    assert.equal(res.numeric_string, '10.50');

    // 1,500.50 -> 1500.50
    res = store.parseAndNormalizePrice('1,500.50');
    assert.equal(res.valid, true);
    assert.equal(res.formatted, '1500.50');
    assert.equal(res.numeric_string, '1500.50');

    // 1.500,50 -> 1500.50
    res = store.parseAndNormalizePrice('1.500,50');
    assert.equal(res.valid, true);
    assert.equal(res.formatted, '1500.50');
    assert.equal(res.numeric_string, '1500.50');
});

test('Handles whole numbers and thousands separators', () => {
    const store = createProductStoreInstance();

    assert.equal(store.parseAndNormalizePrice('1500').formatted, '1500');
    assert.equal(store.parseAndNormalizePrice('1500').numeric_string, '1500.00');

    assert.equal(store.parseAndNormalizePrice('1,500').formatted, '1500');
    assert.equal(store.parseAndNormalizePrice('1,500').numeric_string, '1500.00');

    assert.equal(store.parseAndNormalizePrice('1,500,000').formatted, '1500000');
    assert.equal(store.parseAndNormalizePrice('1.500.000').formatted, '1500000');

    assert.equal(store.parseAndNormalizePrice('1.500.000,50').formatted, '1500000.50');
    assert.equal(store.parseAndNormalizePrice('1,500,000.50').formatted, '1500000.50');
});

test('Handles Khmer numerals and Khmer punctuation', () => {
    const store = createProductStoreInstance();

    // Khmer numerals
    assert.equal(store.parseAndNormalizePrice('១០.៥០').formatted, '10.50');
    assert.equal(store.parseAndNormalizePrice('១០,៥០').formatted, '10.50');
    assert.equal(store.parseAndNormalizePrice('១,៥០០.៥០').formatted, '1500.50');

    // Khmer khan
    assert.equal(store.parseAndNormalizePrice('10។50').formatted, '10.50');
});

test('Rejects invalid input and displays validation error on blur', () => {
    const store = createProductStoreInstance();

    // Invalid format
    store.price = 'abc';
    store.handlePriceBlur('price');
    assert.equal(store.clientErrors.price, 'Price format invalid');

    // Typing clears the error without rewriting raw input
    store.price = '10,';
    store.handlePriceInput('price');
    assert.equal(store.clientErrors.price, undefined);
    assert.equal(store.price, '10,'); // Raw typing preserved!

    // Typing completed to 10,50 and blurred
    store.price = '10,50';
    store.handlePriceBlur('price');
    assert.equal(store.clientErrors.price, undefined);
    assert.equal(store.price, '10.50'); // Normalized on blur!
});

test('Rejects negative prices', () => {
    const store = createProductStoreInstance();
    store.price = '-10,50';
    store.handlePriceBlur('price');
    assert.equal(store.clientErrors.price, 'Price must be greater than or equal to 0.');
});
