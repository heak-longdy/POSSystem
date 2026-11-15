<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Shop;
use Tests\TestCase;

class DeliverySlipV1Test extends TestCase
{
    public function test_v1_template_renders_bonus_items_with_tag_and_zero_price(): void
    {
        $order = new Order([
            'id' => 999,
            'invoice_number' => 'INV-TEST-001',
            'order_date' => '2026-11-15 10:00:00',
            'delivery_date' => '2026-11-17',
            'total_price' => 50.00,
            'previous_balance' => 0.00,
        ]);
        $order->setRelation('shop', new Shop(['name' => 'Test Shop']));
        $order->setRelation('customer', new Customer(['name' => 'John Doe']));

        $regularProduct = new Product(['name' => 'Widget A', 'code' => 'W-001']);
        $bonusProduct = new Product(['name' => 'Free Gift B', 'code' => 'G-002']);

        $regularDetail = new OrderDetail([
            'type' => 'product',
            'is_bonus' => false,
            'qty' => 2,
            'price' => 25.00,
            'unit_price' => 25.00,
        ]);
        $regularDetail->setRelation('product', $regularProduct);

        $bonusDetail = new OrderDetail([
            'type' => 'product',
            'is_bonus' => true,
            'qty' => 1,
            'price' => 0.00,
            'unit_price' => 0.00,
        ]);
        $bonusDetail->setRelation('product', $bonusProduct);

        $order->setRelation('orderDetails', collect([$regularDetail, $bonusDetail]));

        $paymentRecord = new \App\Models\OrderPayment(['note' => '']);
        $order->setRelation('latestPayment', $paymentRecord);

        // Render English V1
        $htmlEn = view('admin::pages.order.invoice-template-02-v1', [
            'order' => $order,
            'printLocale' => 'en',
            'latestPaymentRecord' => $paymentRecord,
        ])->render();

        $this->assertStringContainsString('INV-TEST-001', $htmlEn);
        $this->assertStringContainsString('Widget A', $htmlEn);
        $this->assertStringContainsString('Free Gift B', $htmlEn);
        $this->assertStringContainsString('[Bonus (Free)]', $htmlEn);

        // Render Khmer V1
        $htmlKm = view('admin::pages.order.invoice-template-02-v1', [
            'order' => $order,
            'printLocale' => 'km',
            'latestPaymentRecord' => $paymentRecord,
        ])->render();

        $this->assertStringContainsString('[ថែមជូន (Free)]', $htmlKm);

        // Verify original template (invoice-template-02) does NOT have the bonus tag logic
        $htmlOriginal = view('admin::pages.order.invoice-template-02', [
            'order' => $order,
            'printLocale' => 'en',
            'latestPaymentRecord' => $paymentRecord,
        ])->render();

        $this->assertStringNotContainsString('[Bonus (Free)]', $htmlOriginal);
    }

    public function test_v1_template_with_bonus_only_order(): void
    {
        $order = new Order([
            'id' => 1000,
            'invoice_number' => 'INV-BONUS-ONLY',
            'order_date' => '2026-11-15 10:00:00',
            'total_price' => 0.00,
            'previous_balance' => 0.00,
        ]);
        $order->setRelation('shop', new Shop(['name' => 'Test Shop']));
        $order->setRelation('customer', new Customer(['name' => 'Jane Smith']));

        $bonusProduct = new Product(['name' => 'Bonus Shampoo', 'code' => 'B-777']);
        $bonusDetail = new OrderDetail([
            'type' => 'product',
            'is_bonus' => true,
            'qty' => 4,
            'price' => 0.00,
            'unit_price' => 0.00,
        ]);
        $bonusDetail->setRelation('product', $bonusProduct);

        $order->setRelation('orderDetails', collect([$bonusDetail]));
        $paymentRecord = new \App\Models\OrderPayment(['note' => '']);
        $order->setRelation('latestPayment', $paymentRecord);

        $html = view('admin::pages.order.invoice-template-02-v1', [
            'order' => $order,
            'printLocale' => 'en',
            'latestPaymentRecord' => $paymentRecord,
        ])->render();

        $this->assertStringContainsString('INV-BONUS-ONLY', $html);
        $this->assertStringContainsString('Bonus Shampoo', $html);
        $this->assertStringContainsString('[Bonus (Free)]', $html);
        $this->assertStringContainsString('$0.00', $html);
    }

    public function test_v1_template_with_regular_only_order(): void
    {
        $order = new Order([
            'id' => 1001,
            'invoice_number' => 'INV-REG-ONLY',
            'order_date' => '2026-11-15 10:00:00',
            'total_price' => 100.00,
            'previous_balance' => 0.00,
        ]);
        $order->setRelation('shop', new Shop(['name' => 'Test Shop']));
        $order->setRelation('customer', new Customer(['name' => 'Regular Customer']));

        $product = new Product(['name' => 'Regular Item', 'code' => 'R-111']);
        $detail = new OrderDetail([
            'type' => 'product',
            'is_bonus' => false,
            'qty' => 2,
            'price' => 50.00,
            'unit_price' => 50.00,
        ]);
        $detail->setRelation('product', $product);

        $order->setRelation('orderDetails', collect([$detail]));
        $paymentRecord = new \App\Models\OrderPayment(['note' => '']);
        $order->setRelation('latestPayment', $paymentRecord);

        $html = view('admin::pages.order.invoice-template-02-v1', [
            'order' => $order,
            'printLocale' => 'en',
            'latestPaymentRecord' => $paymentRecord,
        ])->render();

        $this->assertStringContainsString('INV-REG-ONLY', $html);
        $this->assertStringContainsString('Regular Item', $html);
        $this->assertStringNotContainsString('[Bonus (Free)]', $html);
        $this->assertStringContainsString('$100.00', $html);
    }

    public function test_v1_template_renders_decimal_bonus_quantity(): void
    {
        $order = new Order([
            'id' => 1001,
            'invoice_number' => 'INV-DECIMAL-BONUS',
            'order_date' => '2026-11-15 10:00:00',
            'delivery_date' => '2026-11-17',
            'total_price' => 10.00,
            'previous_balance' => 0.00,
        ]);
        $order->setRelation('shop', new Shop(['name' => 'Test Shop']));
        $order->setRelation('customer', new Customer(['name' => 'Decimal Customer']));

        $regularProduct = new Product(['name' => 'Regular Item', 'code' => 'R-111']);
        $regularDetail = new OrderDetail([
            'type' => 'product',
            'is_bonus' => false,
            'qty' => 1,
            'price' => 10.00,
            'unit_price' => 10.00,
        ]);
        $regularDetail->setRelation('product', $regularProduct);

        $bonusProduct = new Product(['name' => 'Free Half Item', 'code' => 'B-0.5']);
        $bonusDetail = new OrderDetail([
            'type' => 'product',
            'is_bonus' => true,
            'qty' => 0.5,
            'price' => 0.00,
            'unit_price' => 0.00,
        ]);
        $bonusDetail->setRelation('product', $bonusProduct);

        $order->setRelation('orderDetails', collect([$regularDetail, $bonusDetail]));
        $paymentRecord = new \App\Models\OrderPayment(['note' => '']);
        $order->setRelation('latestPayment', $paymentRecord);

        // Test delivery slip v1
        $htmlV1 = view('admin::pages.order.invoice-template-02-v1', [
            'order' => $order,
            'printLocale' => 'en',
            'latestPaymentRecord' => $paymentRecord,
        ])->render();

        $this->assertStringContainsString('0.5', $htmlV1);
        $this->assertStringContainsString('[Bonus (Free)]', $htmlV1);

        // Test invoice template
        $htmlInvoice = view('admin::pages.order.invoice-template', [
            'order' => $order,
            'printLocale' => 'en',
            'cashierName' => 'Admin',
        ])->render();

        $this->assertStringContainsString('0.5', $htmlInvoice);
        $this->assertStringContainsString('(Free)', $htmlInvoice);
    }

    public function test_templates_render_quantities_without_trailing_zeros(): void
    {
        $user = new \App\Models\User(['role' => 'super_admin']);
        $user->id = 1;
        \Illuminate\Support\Facades\Auth::login($user);

        $cases = [
            ['5.00', '5'],
            ['5.50', '5.5'],
            ['4.50', '4.5'],
            ['4.25', '4.25'],
            ['3.75', '3.75'],
        ];

        foreach ($cases as [$inputQty, $expectedDisplay]) {
            $order = new Order([
                'invoice_number' => 'INV-ZERO-TRIM',
                'order_date' => '2026-11-15 10:00:00',
                'total_price' => 0.00,
                'previous_balance' => 0.00,
            ]);
            $order->id = 2000;
            $order->setRelation('shop', new Shop(['name' => 'Test Shop']));
            $order->setRelation('customer', new Customer(['name' => 'Zero Trim Customer']));
            $order->setRelation('payments', collect([]));
            $order->setRelation('incomingBalanceTransfers', collect([]));
            $order->setRelation('outgoingBalanceTransfers', collect([]));

            $bonusProduct = new Product(['name' => 'Bonus Product', 'code' => 'B-001']);
            $bonusDetail = new OrderDetail([
                'type' => 'product',
                'is_bonus' => true,
                'qty' => $inputQty,
                'price' => 0.00,
                'unit_price' => 0.00,
            ]);
            $bonusDetail->setRelation('product', $bonusProduct);

            $order->setRelation('orderDetails', collect([$bonusDetail]));
            $paymentRecord = new \App\Models\OrderPayment(['note' => '']);
            $order->setRelation('latestPayment', $paymentRecord);

            // 1. Delivery Slip V1
            $htmlV1 = view('admin::pages.order.invoice-template-02-v1', [
                'order' => $order,
                'printLocale' => 'en',
                'latestPaymentRecord' => $paymentRecord,
            ])->render();
            $this->assertStringContainsString("<td class=\"td-cases td-qty\">{$expectedDisplay}</td>", $htmlV1);

            // 2. Delivery Slip V2 (invoice-template-02)
            $htmlV2 = view('admin::pages.order.invoice-template-02', [
                'order' => $order,
                'printLocale' => 'en',
                'latestPaymentRecord' => $paymentRecord,
            ])->render();
            $this->assertStringContainsString("<td class=\"td-cases td-qty\">{$expectedDisplay}</td>", $htmlV2);

            // 3. Invoice Template
            $htmlInvoice = view('admin::pages.order.invoice-template', [
                'order' => $order,
                'printLocale' => 'en',
                'cashierName' => 'Admin',
            ])->render();
            $this->assertStringContainsString("<td class=\"cell-qty\">{$expectedDisplay}</td>", $htmlInvoice);
        }
    }
}
