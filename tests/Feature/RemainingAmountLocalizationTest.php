<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\BookingPayment;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Shop;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemainingAmountLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $kmUser;
    protected User $enUser;
    protected Shop $shop;
    protected Customer $customer;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);

        $this->kmUser = User::factory()->create([
            'role' => 'admin',
            'status' => 1,
            'language_preference' => 'km',
        ]);
        $this->kmUser->givePermissionTo(['booking-view', 'booking-update', 'booking-delete']);

        $this->enUser = User::factory()->create([
            'role' => 'admin',
            'status' => 1,
            'language_preference' => 'en',
        ]);
        $this->enUser->givePermissionTo(['booking-view', 'booking-update', 'booking-delete']);

        $this->shop = Shop::create([
            'name' => 'TK Central',
            'phone' => '012' . rand(100000, 999999),
            'status' => 1,
        ]);

        $this->customer = Customer::create([
            'name' => 'Chanthy',
            'phone' => '098' . rand(100000, 999999),
            'status' => 1,
            'user' => $this->kmUser->id,
        ]);

        $this->booking = Booking::create([
            'invoice_number' => 'INV-' . rand(1000, 9999),
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'total_price' => 100.00,
            'paid_amount' => 30.00,
            'payment_status' => 'Partial',
            'booking_date' => now(),
        ]);
    }

    /** @test */
    public function it_renders_remaining_amount_page_in_khmer_for_khmer_user()
    {
        $this->actingAs($this->kmUser);

        $response = $this->get(route('admin-remaining-amount-list', 'all'));

        $response->assertStatus(200);
        // Header
        $response->assertSee('ការគ្រប់គ្រងទឹកប្រាក់នៅសល់');
        // Tabs
        $response->assertSee('នៅសល់ទាំងអស់');
        $response->assertSee('បង់ប្រាក់ខ្លះ');
        $response->assertSee('រង់ចាំការទូទាត់');
        $response->assertSee('បានបង់ពេញ');
        // Excel Report Button
        $response->assertSee('របាយការណ៍ទឹកប្រាក់នៅសល់');
        // Table Headers
        $response->assertSee('លេខកូដកក់');
        $response->assertSee('ហាង');
        $response->assertSee('អតិថិជន');
        $response->assertSee('សេវាកម្ម/ផលិតផល');
        $response->assertSee('ស្ថានភាពទូទាត់');
        $response->assertSee('សរុប');
        $response->assertSee('បានបង់');
        $response->assertSee('នៅសល់');
        $response->assertSee('កាលបរិច្ឆេទកក់');
        $response->assertSee('សកម្មភាព');
        // Localized badge in table
        $response->assertSee('បង់ប្រាក់ខ្លះ');
        // Action titles
        $response->assertSee('គ្រប់គ្រងការទូទាត់');
        $response->assertSee('ផ្ញើសាររំលឹក');
        // Modal labels
        $response->assertSee('ចំនួនទឹកប្រាក់សរុប');
        $response->assertSee('ចំនួនទឹកប្រាក់បានបង់');
        $response->assertSee('សមតុល្យនៅសល់');
        $response->assertSee('ធ្វើការទូទាត់');
        $response->assertSee('ប្រវត្តិនៃការទូទាត់');
        $response->assertSee('ចំនួនទឹកប្រាក់ទូទាត់ ($)');
        $response->assertSee('សមតុល្យពេញ');
        $response->assertSee('វិធីសាស្ត្រទូទាត់');
        $response->assertSee('សាច់ប្រាក់');
        $response->assertSee('កត់ត្រាការទូទាត់');
    }

    /** @test */
    public function it_renders_remaining_amount_page_in_english_for_english_user()
    {
        $this->actingAs($this->enUser);

        $response = $this->get(route('admin-remaining-amount-list', 'all'));

        $response->assertStatus(200);
        $response->assertSee('Remaining Amount Management');
        $response->assertSee('All Outstanding');
        $response->assertSee('Partial Paid');
        $response->assertSee('Pending Payment');
        $response->assertSee('Fully Paid');
        $response->assertSee('Remaining Amount Report');
        $response->assertSee('Booking ID');
        $response->assertSee('Shop');
        $response->assertSee('Customer');
        $response->assertSee('Action');
        $response->assertSee('Manage Payment');
        $response->assertSee('Total Amount');
        $response->assertSee('Amount Paid');
        $response->assertSee('Remaining Balance');
        $response->assertSee('Make Payment');
    }

    /** @test */
    public function it_validates_add_payment_with_localized_error_in_khmer()
    {
        $this->actingAs($this->kmUser);

        // Submit amount exceeding remaining amount (remaining is 70)
        $response = $this->postJson(route('admin-remaining-amount-add-payment', $this->booking->id), [
            'amount' => 999.00,
            'payment_method' => 'Cash',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'amount' => 'ចំនួនទឹកប្រាក់ទូទាត់លើសពីសមតុល្យនៅសល់',
        ]);
    }

    /** @test */
    public function it_sends_payment_reminder_with_localized_success_message_in_khmer()
    {
        $this->actingAs($this->kmUser);

        $response = $this->postJson(route('admin-remaining-amount-send-reminder', $this->booking->id));

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'success',
            'status' => 200,
            'success_message' => __('remaining_amount.message.reminder_sent_booking', ['invoice' => $this->booking->invoice_number]),
        ]);
    }

    /** @test */
    public function it_returns_localized_details_fallback_for_walk_in_customer()
    {
        $walkInBooking = Booking::create([
            'invoice_number' => 'INV-' . rand(1000, 9999),
            'customer_id' => null,
            'shop_id' => $this->shop->id,
            'total_price' => 50.00,
            'paid_amount' => 0.00,
            'payment_status' => 'Pending',
            'booking_date' => now(),
        ]);

        $this->actingAs($this->kmUser);

        $response = $this->getJson(route('admin-remaining-amount-payment-details', $walkInBooking->id));

        $response->assertStatus(200);
        $response->assertJson([
            'customer_name' => 'អតិថិជនទូទៅ',
        ]);
    }
}
