<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\UOM;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $kmUser;
    protected User $enUser;
    protected Shop $shop;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);

        $this->kmUser = User::factory()->create([
            'role' => 'admin',
            'status' => 1,
            'language_preference' => 'km',
        ]);
        $this->kmUser->givePermissionTo(['shop-view', 'shop-create', 'shop-update']);

        $this->enUser = User::factory()->create([
            'role' => 'admin',
            'status' => 1,
            'language_preference' => 'en',
        ]);
        $this->enUser->givePermissionTo(['shop-view', 'shop-create', 'shop-update']);

        $this->shop = Shop::create([
            'name' => 'Shop ' . uniqid(),
            'phone' => '012' . rand(1000000, 9999999),
            'address' => 'St. 2004, Phnom Penh',
            'status' => 1,
        ]);

        $category = Category::firstOrCreate(['name' => 'Pomade'], ['status' => 1]);
        $uom = UOM::firstOrCreate(['name' => 'Can'], ['status' => 1]);

        $this->product = Product::create([
            'name' => 'Classic Pomade ' . uniqid(),
            'category_id' => $category->id,
            'uom_id' => $uom->id,
            'cost' => 5.00,
            'price' => 10.00,
            'status' => 1,
        ]);

        ShopProduct::create([
            'shop_id' => $this->shop->id,
            'product_id' => $this->product->id,
            'price' => 12.00,
            'point' => 1.50,
            'max_qty' => 50,
            'commission' => 1.00,
            'commission_type' => 'usd',
            'status' => 1,
        ]);
    }

    /** @test */
    public function it_renders_shop_module_in_khmer_for_khmer_user()
    {
        $this->actingAs($this->kmUser);

        // 1. Shop list page
        $listResponse = $this->get(route('admin-shop-list', 1));
        $listResponse->assertStatus(200);
        $listResponse->assertSee('ការគ្រប់គ្រងហាង');
        $listResponse->assertSee('បង្កើតហាងថ្មី');
        $listResponse->assertSee('សកម្ម');
        $listResponse->assertSee('អសកម្ម');
        $listResponse->assertSee('ធុងសំរាម');
        $listResponse->assertSee('ឈ្មោះ');
        $listResponse->assertSee('លេខទូរស័ព្ទ');
        $listResponse->assertSee('អាសយដ្ឋាន');
        $listResponse->assertSee('កែប្រែ');
        $listResponse->assertSee('ផលិតផល');
        $listResponse->assertSee('លុប');

        // 2. Shop create page
        $createResponse = $this->get(route('admin-shop-create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('បង្កើតហាងថ្មី');
        $createResponse->assertSee('ឈ្មោះ');
        $createResponse->assertSee('លេខទូរស័ព្ទ');
        $createResponse->assertSee('អាសយដ្ឋាន');
        $createResponse->assertSee('រូបភាព');
        $createResponse->assertSee('រក្សាទុក');
        $createResponse->assertSee('បោះបង់');

        // 3. Shop product listing
        $productResponse = $this->get(route('admin-shop-product', $this->shop->id));
        $productResponse->assertStatus(200);
        $productResponse->assertSee('ផលិតផលក្នុងហាង');
        $productResponse->assertSee('បន្ថែមផលិតផលទៅក្នុងហាង');
        $productResponse->assertSee('តម្លៃលក់');
        $productResponse->assertSee('ពិន្ទុ');
        $productResponse->assertSee('ចំនួនអតិបរមា');
        $productResponse->assertSee('កម្រៃជើងសារ');

        // 4. Shop product assignment page
        $assignResponse = $this->get(route('admin-shop-product-create', $this->shop->id));
        $assignResponse->assertStatus(200);
        $assignResponse->assertSee('បន្ថែមផលិតផលទៅក្នុងហាង');
        $assignResponse->assertSee('ការចាត់តាំងផលិតផល');
        $assignResponse->assertSee('ជ្រើសរើសផលិតផល');
        $assignResponse->assertSee('រក្សាទុក');
        $assignResponse->assertSee('បោះបង់');
    }

    /** @test */
    public function it_renders_shop_module_in_english_for_english_user()
    {
        $this->actingAs($this->enUser);

        // 1. Shop list page
        $listResponse = $this->get(route('admin-shop-list', 1));
        $listResponse->assertStatus(200);
        $listResponse->assertSee('Shop Management');
        $listResponse->assertSee('Create Shop');
        $listResponse->assertSee('Active');
        $listResponse->assertSee('Disable');
        $listResponse->assertSee('Trash');
        $listResponse->assertSee('Name');
        $listResponse->assertSee('Phone Number');
        $listResponse->assertSee('Address');
        $listResponse->assertSee('Edit');
        $listResponse->assertSee('Product');
        $listResponse->assertSee('Delete');

        // 2. Shop create page
        $createResponse = $this->get(route('admin-shop-create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Create Shop');
        $createResponse->assertSee('Name');
        $createResponse->assertSee('Phone Number');
        $createResponse->assertSee('Address');
        $createResponse->assertSee('Image');
        $createResponse->assertSee('Submit');
        $createResponse->assertSee('Cancel');

        // 3. Shop product listing
        $productResponse = $this->get(route('admin-shop-product', $this->shop->id));
        $productResponse->assertStatus(200);
        $productResponse->assertSee('Shop Product');
        $productResponse->assertSee('Add Product to Shop');
        $productResponse->assertSee('Price');
        $productResponse->assertSee('Point');
        $productResponse->assertSee('Max Qty');
        $productResponse->assertSee('Commission');

        // 4. Shop product assignment page
        $assignResponse = $this->get(route('admin-shop-product-create', $this->shop->id));
        $assignResponse->assertStatus(200);
        $assignResponse->assertSee('Add Product to Shop');
        $assignResponse->assertSee('Product assignment');
        $assignResponse->assertSee('Select Product');
        $assignResponse->assertSee('Submit');
        $assignResponse->assertSee('Cancel');
    }

    /** @test */
    public function it_validates_shop_creation_with_localized_error_in_khmer()
    {
        $this->actingAs($this->kmUser);

        $response = $this->post(route('admin-shop-save'), []);
        $response->assertSessionHasErrors([
            'name' => 'សូមបញ្ចូលឈ្មោះហាង',
            'phone' => 'សូមបញ្ចូលលេខទូរស័ព្ទ',
            'address' => 'សូមបញ្ចូលអាសយដ្ឋាន',
            'status' => 'សូមជ្រើសរើសស្ថានភាព',
        ]);
    }

    /** @test */
    public function it_validates_shop_product_creation_with_localized_error_in_khmer()
    {
        $this->actingAs($this->kmUser);

        $response = $this->post(route('admin-shop-product-save', $this->shop->id), [
            'products' => [
                [
                    'product_id' => '',
                    'price' => '',
                    'status' => '',
                ],
            ],
        ]);

        $response->assertSessionHasErrors([
            'products.0.product_id' => 'សូមជ្រើសរើសផលិតផល',
            'products.0.price' => 'សូមបញ្ចូលតម្លៃ',
            'products.0.status' => 'សូមជ្រើសរើសស្ថានភាព',
        ]);
    }

    /** @test */
    public function it_saves_and_deletes_shop_product_with_localized_flash_messages_in_khmer()
    {
        $this->actingAs($this->kmUser);

        $newProduct = Product::create([
            'name' => 'Hair Wax 50g',
            'cost' => 3.00,
            'price' => 7.00,
            'status' => 1,
        ]);

        $saveResponse = $this->post(route('admin-shop-product-save', $this->shop->id), [
            'products' => [
                [
                    'product_id' => $newProduct->id,
                    'price' => 8.50,
                    'point' => 1.00,
                    'max_qty' => 30,
                    'commission' => 0.50,
                    'commission_type' => 'usd',
                    'status' => 1,
                ],
            ],
        ]);

        $saveResponse->assertRedirect(route('admin-shop-product', $this->shop->id));
        $saveResponse->assertSessionHas('success', 'ផលិតផលក្នុងហាងត្រូវបានរក្សាទុកដោយជោគជ័យ។');

        $shopProduct = ShopProduct::where('shop_id', $this->shop->id)->where('product_id', $newProduct->id)->first();
        $this->assertNotNull($shopProduct);

        $deleteResponse = $this->post(route('admin-shop-product-delete', [
            'shopId' => $this->shop->id,
            'shopProductId' => $shopProduct->id,
        ]));

        $deleteResponse->assertStatus(200);
        $deleteResponse->assertSessionHas('success', 'លុបបានជោគជ័យ!');
    }
}
