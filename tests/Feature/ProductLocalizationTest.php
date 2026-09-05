<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\UOM;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create sample category and UOM
        $category = Category::create(['name' => 'Beverages', 'status' => 1]);
        $uom = UOM::create(['name' => 'Can', 'status' => 1]);

        Product::create([
            'name' => 'Energy Drink 250ml',
            'category_id' => $category->id,
            'uom_id' => $uom->id,
            'cost' => 1.50,
            'price' => 2.50,
            'status' => 1,
        ]);
    }

    /** @test */
    public function it_dynamically_renders_product_module_in_khmer_for_khmer_user()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'status' => 1,
            'language_preference' => 'km',
        ]);

        $this->actingAs($user);

        // 1. Product List page
        $response = $this->get(route('admin-product-list', 1));
        $response->assertStatus(200);
        $response->assertSee('ការគ្រប់គ្រងផលិតផល'); // Title
        $response->assertSee('បង្កើតផលិតផលថ្មី'); // Create button
        $response->assertSee('សកម្ម'); // Tab active
        $response->assertSee('អសកម្ម'); // Tab disable
        $response->assertSee('ធុងសំរាម'); // Tab trash
        $response->assertSee('តម្លៃដើម'); // Cost
        $response->assertSee('តម្លៃលក់'); // Price
        $response->assertSee('កែប្រែ'); // Edit action
        $response->assertSee('លុប'); // Delete action
        $response->assertSee('lang="km"', false); // html lang

        // 2. Product Create page
        $createResponse = $this->get(route('admin-product-create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('បង្កើតផលិតផលថ្មី');
        $createResponse->assertSee('ជ្រើសរើសប្រភេទ');
        $createResponse->assertSee('ជ្រើសរើសខ្នាត');
        $createResponse->assertSee('រក្សាទុក');
        $createResponse->assertSee('បោះបង់');

        // 3. Validation messages in Khmer
        $invalidResponse = $this->post(route('admin-product-save'), []);
        $invalidResponse->assertSessionHasErrors([
            'name' => 'សូមបញ្ចូលឈ្មោះផលិតផល',
            'category_id' => 'សូមជ្រើសរើសប្រភេទផលិតផល',
            'uom_id' => 'សូមជ្រើសរើសខ្នាតផលិតផល',
            'cost' => 'សូមបញ្ចូលតម្លៃដើម',
            'price' => 'សូមបញ្ចូលតម្លៃលក់',
            'status' => 'សូមជ្រើសរើសស្ថានភាព',
        ]);
    }

    /** @test */
    public function it_dynamically_renders_product_module_in_english_for_english_user()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'status' => 1,
            'language_preference' => 'en',
        ]);

        $this->actingAs($user);

        // 1. Product List page
        $response = $this->get(route('admin-product-list', 1));
        $response->assertStatus(200);
        $response->assertSee('Product Management');
        $response->assertSee('Create Product');
        $response->assertSee('Active');
        $response->assertSee('Disable');
        $response->assertSee('Trash');
        $response->assertSee('Cost');
        $response->assertSee('Price');
        $response->assertSee('Edit');
        $response->assertSee('Delete');
        $response->assertSee('lang="en"', false);

        // 2. Product Create page
        $createResponse = $this->get(route('admin-product-create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Create Product');
        $createResponse->assertSee('Select Category');
        $createResponse->assertSee('Select UOM');
        $createResponse->assertSee('Submit');
        $createResponse->assertSee('Cancel');

        // 3. Validation messages in English
        $invalidResponse = $this->post(route('admin-product-save'), []);
        $invalidResponse->assertSessionHasErrors([
            'name' => 'Name is required',
            'category_id' => 'Category is required',
            'uom_id' => 'UOM is required',
            'cost' => 'Cost is required',
            'price' => 'Price is required',
            'status' => 'Status is required',
        ]);
    }

    /** @test */
    public function it_switches_product_language_upon_login_based_on_user_preference()
    {
        $password = 'Secret123!';
        $khmerUser = User::factory()->create([
            'email' => 'khmer_admin@example.com',
            'password' => bcrypt($password),
            'role' => 'admin',
            'status' => 1,
            'language_preference' => 'km',
        ]);

        $loginResponse = $this->post(route('admin-login-post'), [
            'email' => $khmerUser->email,
            'password' => $password,
        ]);

        $loginResponse->assertSessionHas('locale', 'km');
        $loginResponse->assertSessionHas('language', 'km');

        $listResponse = $this->get(route('admin-product-list', 1));
        $listResponse->assertStatus(200);
        $listResponse->assertSee('ការគ្រប់គ្រងផលិតផល');
        $listResponse->assertSee('បង្កើតផលិតផលថ្មី');
    }
}
