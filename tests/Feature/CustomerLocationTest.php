<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerLocationTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('language_preference')->default('en');
            $table->rememberToken();
            $table->string('phone')->nullable();
            $table->string('role')->nullable();
            $table->string('data_access')->default('all');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->string('name')->nullable();
            $table->integer('ordering')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('profile')->nullable();
            $table->string('image_1')->nullable();
            $table->string('image_2')->nullable();
            $table->string('password')->nullable();
            $table->integer('total_point')->default(0);
            $table->string('user')->default('1');
            $table->string('payment_restriction', 10)->default('N');
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        $this->admin = new User([
            'name' => 'Super Admin',
            'role' => 'super_admin',
            'language_preference' => 'en',
            'data_access' => 'all',
        ]);
        $this->admin->id = 1;
        $this->actingAs($this->admin);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('customers');
        parent::tearDown();
    }

    public function test_customer_model_location_attributes_and_helpers()
    {
        $customerWithoutLoc = new Customer([
            'name' => 'No Location Customer',
            'phone' => '012345678',
        ]);
        $this->assertFalse($customerWithoutLoc->hasLocation());
        $this->assertNull($customerWithoutLoc->google_maps_url);

        $customerWithLoc = new Customer([
            'name' => 'Located Customer',
            'phone' => '012345679',
            'latitude' => 11.5564000,
            'longitude' => 104.9282000,
        ]);
        $this->assertTrue($customerWithLoc->hasLocation());
        $this->assertEquals(11.5564, $customerWithLoc->latitude);
        $this->assertEquals(104.9282, $customerWithLoc->longitude);
        $this->assertEquals('https://www.google.com/maps?q=11.5564,104.9282', $customerWithLoc->google_maps_url);
    }

    public function test_customer_creation_with_valid_coordinates()
    {
        $payload = [
            'name' => 'Phnom Penh Shop',
            'phone' => '098765432',
            'address' => 'Street 271, Phnom Penh',
            'latitude' => 11.5564,
            'longitude' => 104.9282,
            'status' => 1,
            'payment_restriction' => 'N',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin-customer-save'), $payload);
        $response->assertSessionHasNoErrors();

        $customer = Customer::where('phone', '098765432')->first();
        $this->assertNotNull($customer);
        $this->assertEquals(11.5564, $customer->latitude);
        $this->assertEquals(104.9282, $customer->longitude);
    }

    public function test_customer_creation_fails_with_invalid_latitude()
    {
        $payload = [
            'name' => 'Invalid Lat Customer',
            'phone' => '011223344',
            'latitude' => 99.9999, // Out of range (>90)
            'longitude' => 104.9282,
            'status' => 1,
            'payment_restriction' => 'N',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin-customer-save'), $payload);
        $response->assertSessionHasErrors(['latitude']);
    }

    public function test_customer_creation_fails_with_invalid_longitude()
    {
        $payload = [
            'name' => 'Invalid Lng Customer',
            'phone' => '011223355',
            'latitude' => 11.5564,
            'longitude' => 200.0000, // Out of range (>180)
            'status' => 1,
            'payment_restriction' => 'N',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin-customer-save'), $payload);
        $response->assertSessionHasErrors(['longitude']);
    }

    public function test_customer_update_clears_location_when_submitted_empty()
    {
        $customer = Customer::create([
            'name' => 'Original Customer',
            'phone' => '077889900',
            'latitude' => 11.5564,
            'longitude' => 104.9282,
            'status' => 1,
            'user' => (string) $this->admin->id,
        ]);

        $payload = [
            'name' => 'Updated Customer',
            'phone' => '077889900',
            'latitude' => '',
            'longitude' => '',
            'status' => 1,
            'payment_restriction' => 'N',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin-customer-save', $customer->id), $payload);
        $response->assertSessionHasNoErrors();

        $customer->refresh();
        $this->assertNull($customer->latitude);
        $this->assertNull($customer->longitude);
        $this->assertFalse($customer->hasLocation());
    }

    public function test_customer_create_view_contains_map_and_coordinate_inputs()
    {
        $response = $this->actingAs($this->admin)->get(route('admin-customer-create'));
        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('name="latitude"', $content);
        $this->assertStringContainsString('name="longitude"', $content);
        $this->assertMatchesRegularExpression('/name="latitude"[^>]*readonly/', $content);
        $this->assertMatchesRegularExpression('/name="longitude"[^>]*readonly/', $content);
        $this->assertStringContainsString('customer-map-card', $content);
        $this->assertStringContainsString('customer-map-canvas', $content);
        $this->assertStringContainsString('btn-fullscreen-outline', $content);
        $this->assertStringContainsString('toggleFullScreen', $content);
        $this->assertStringContainsString('is-fullscreen', $content);
        $this->assertStringContainsString('plugin/leaflet/leaflet.js', $content);
    }

    public function test_customer_edit_view_contains_map_and_customer_coordinates()
    {
        $customer = Customer::create([
            'name' => 'Mapped Customer',
            'phone' => '088776655',
            'latitude' => 11.5564,
            'longitude' => 104.9282,
            'status' => 1,
            'user' => (string) $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin-customer-edit', $customer->id));
        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('name="latitude"', $content);
        $this->assertStringContainsString('name="longitude"', $content);
        $this->assertMatchesRegularExpression('/name="latitude"[^>]*readonly/', $content);
        $this->assertMatchesRegularExpression('/name="longitude"[^>]*readonly/', $content);
        $this->assertStringContainsString('customer-map-canvas', $content);
    }

    public function test_customer_detail_view_renders_google_maps_link()
    {
        $customer = Customer::create([
            'name' => 'Detail Customer',
            'phone' => '099887766',
            'latitude' => 11.5564,
            'longitude' => 104.9282,
            'status' => 1,
            'user' => (string) $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin-customer-detail', $customer->id));
        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('https://www.google.com/maps?q=11.5564,104.9282', $content);
    }

    public function test_customer_csv_export_contains_location_columns()
    {
        Customer::create([
            'name' => 'Export Customer',
            'phone' => '077112233',
            'latitude' => 11.5564,
            'longitude' => 104.9282,
            'status' => 1,
            'user' => (string) $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin-customer-export'));
        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('11.5564', $content);
        $this->assertStringContainsString('104.9282', $content);
        $this->assertStringContainsString('https://www.google.com/maps?q=11.5564,104.9282', $content);
    }
}
