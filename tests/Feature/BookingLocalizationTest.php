<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\BookingController;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Shop;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $kmUser;
    protected User $enUser;
    protected Shop $shop;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);

        $this->kmUser = User::factory()->create([
            'role' => 'admin',
            'status' => 1,
            'language_preference' => 'km',
        ]);
        $this->kmUser->givePermissionTo(['booking-view', 'booking-create', 'booking-update', 'booking-delete']);

        $this->enUser = User::factory()->create([
            'role' => 'admin',
            'status' => 1,
            'language_preference' => 'en',
        ]);
        $this->enUser->givePermissionTo(['booking-view', 'booking-create', 'booking-update', 'booking-delete']);

        if (!\Illuminate\Support\Facades\Schema::hasTable('orders')) {
            \Illuminate\Support\Facades\Schema::create('orders', function ($table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('shop_id')->nullable();
                $table->unsignedBigInteger('barber_id')->nullable();
                $table->decimal('total_price', 10, 2)->default(0);
                $table->decimal('commission', 10, 2)->default(0);
                $table->decimal('discount', 10, 2)->default(0);
                $table->decimal('rate', 10, 2)->default(0);
                $table->timestamp('order_date')->nullable();
                $table->timestamps();
            });
        }
        if (!\Illuminate\Support\Facades\Schema::hasTable('order_details')) {
            \Illuminate\Support\Facades\Schema::create('order_details', function ($table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->decimal('price', 10, 2)->default(0);
                $table->integer('qty')->default(1);
                $table->timestamps();
            });
        }

        $this->shop = Shop::create([
            'name' => 'TK Branch',
            'phone' => '012' . rand(100000, 999999),
            'status' => 1,
        ]);

        $this->customer = Customer::create([
            'name' => 'Sok Dara',
            'phone' => '099' . rand(100000, 999999),
            'status' => 1,
            'user' => $this->kmUser->id,
        ]);
    }

    /** @test */
    public function it_renders_booking_list_page_in_khmer()
    {
        Booking::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'total_price' => 100.00,
            'paid_amount' => 0.00,
            'payment_status' => 'Pending',
            'booking_date' => now(),
        ]);

        $this->actingAs($this->kmUser);

        $response = $this->get(route('admin-booking-list', 'Pending'));

        $response->assertStatus(200);
        $response->assertSee('ការគ្រប់គ្រងការកក់');
        $response->assertSee('បង្កើតការកក់');
        $response->assertSee('រង់ចាំ');
        $response->assertSee('លេខកូដកក់');
        $response->assertSee('ហាង');
        $response->assertSee('កែប្រែ');
    }

    /** @test */
    public function it_renders_booking_list_page_in_english()
    {
        Booking::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'total_price' => 100.00,
            'paid_amount' => 0.00,
            'payment_status' => 'Pending',
            'booking_date' => now(),
        ]);

        $this->actingAs($this->enUser);

        $response = $this->get(route('admin-booking-list', 'Pending'));

        $response->assertStatus(200);
        $response->assertSee('Booking Management');
        $response->assertSee('Create Booking');
        $response->assertSee('Pending');
        $response->assertSee('Booking ID');
        $response->assertSee('Shop');
    }

    /** @test */
    public function it_renders_booking_create_page_in_khmer()
    {
        $this->actingAs($this->kmUser);

        $response = $this->get(route('admin-booking-create'));

        $response->assertStatus(200);
        $response->assertSee('បង្កើតការកក់');
        $response->assertSee('កាតាឡុកទំនិញ');
        $response->assertSee('ព័ត៌មានអតិថិជន និងហាង');
        $response->assertSee('សាច់ប្រាក់');
    }

    /** @test */
    public function it_renders_booking_list_product_page_in_khmer()
    {
        $this->actingAs($this->kmUser);

        $response = $this->get(route('admin-booking-list-product', 1));

        $response->assertStatus(200);
        $response->assertSee('ការកុម្ម៉ង់ផលិតផល');
        $response->assertSee('សេវាកម្ម');
        $response->assertSee('ផលិតផល');
        $response->assertSee('ហាងទាំងអស់');
    }

    /** @test */
    public function it_validates_booking_request_with_localized_messages()
    {
        $this->actingAs($this->kmUser);

        $response = $this->postJson(route('admin-booking-save'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'shop_id' => 'សូមជ្រើសរើសហាង',
            'customer_id' => 'សូមជ្រើសរើសអតិថិជន',
            'booking_date' => 'សូមជ្រើសរើសកាលបរិច្ឆេទកក់',
            'dataCarts' => 'សូមជ្រើសរើសទំនិញយ៉ាងតិចមួយក្នុងកន្ត្រក',
        ]);
    }

    /** @test */
    public function it_returns_localized_booking_status_labels()
    {
        app()->setLocale('km');
        $this->assertEquals('បានបង់ប្រាក់', BookingController::bookingStatusLabel('Paid'));
        $this->assertEquals('បង់ប្រាក់ខ្លះ', BookingController::bookingStatusLabel('Partial'));
        $this->assertEquals('បានបដិសេធ', BookingController::bookingStatusLabel('Cancel'));
        $this->assertEquals('រង់ចាំ', BookingController::bookingStatusLabel('Pending'));

        app()->setLocale('en');
        $this->assertEquals('Paid', BookingController::bookingStatusLabel('Paid'));
        $this->assertEquals('Partial', BookingController::bookingStatusLabel('Partial'));
        $this->assertEquals('Rejected', BookingController::bookingStatusLabel('Cancel'));
        $this->assertEquals('Pending', BookingController::bookingStatusLabel('Pending'));
    }

    /** @test */
    public function it_renders_booking_detail_page_in_english_and_khmer()
    {
        $booking = Booking::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'total_price' => 150.00,
            'paid_amount' => 50.00,
            'payment_status' => 'Partial',
            'booking_date' => now(),
            'invoice_number' => 'BK-TEST-001',
        ]);

        $this->actingAs($this->enUser);
        $enList = $this->get(route('admin-booking-list', 'Pending'));
        $enList->assertStatus(200);
        $enList->assertSee('View Details');

        $enResponse = $this->get(route('admin-booking-detail', $booking->id));
        $enResponse->assertStatus(200);
        $enResponse->assertSee('Booking Details');
        $enResponse->assertSee('BK-TEST-001');
        $enResponse->assertSee('Total Bill');
        $enResponse->assertSee('Balance Due');

        $this->actingAs($this->kmUser);
        $kmList = $this->get(route('admin-booking-list', 'Pending'));
        $kmList->assertStatus(200);
        $kmList->assertSee('មើលព័ត៌មានលម្អិត');

        $kmResponse = $this->get(route('admin-booking-detail', $booking->id));
        $kmResponse->assertStatus(200);
        $kmResponse->assertSee('ព័ត៌មានលម្អិតនៃការកក់');
        $kmResponse->assertSee('BK-TEST-001');
        $kmResponse->assertSee('តម្លៃសរុប');
        $kmResponse->assertSee('ប្រាក់នៅខ្វះ');
    }
}
