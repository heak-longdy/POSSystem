<?php

namespace Tests\Unit;

use App\Http\Requests\Admin\CustomerRequest;
use App\Models\Customer;
use App\Models\ModulePermission;
use App\Models\Permission;
use App\Support\AdminPermissions;
use App\Support\PermissionModuleSelection;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DynamicPermissionsAndCustomerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();
    }

    /** @test */
    public function customer_model_has_image_1_and_image_2_in_fillable_and_appends()
    {
        $customer = new Customer();

        $this->assertContains('image_1', $customer->getFillable());
        $this->assertContains('image_2', $customer->getFillable());

        $this->assertContains('image_1_url', $customer->getAppends());
        $this->assertContains('image_2_url', $customer->getAppends());

        $customer->image_1 = 'uploads/customer/test1.png';
        $customer->image_2 = 'uploads/customer/test2.jpg';

        $this->assertStringContainsString('file_manager/uploads/customer/test1.png', $customer->image_1_url);
        $this->assertStringContainsString('file_manager/uploads/customer/test2.jpg', $customer->image_2_url);
    }

    /** @test */
    public function customer_request_validates_image_1_and_image_2()
    {
        $request = new CustomerRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('image_1', $rules);
        $this->assertArrayHasKey('image_2', $rules);

        $validator = Validator::make([
            'name' => 'John Doe',
            'phone' => '012345678',
            'status' => '1',
            'image_1' => 'uploads/image1.jpg',
            'image_2' => 'uploads/image2.jpg',
        ], $rules);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function admin_permissions_dynamic_lookup_includes_modules()
    {
        $modules = AdminPermissions::getSortedModules();

        $this->assertNotEmpty($modules);
        $this->assertContains('customer', $modules);
        $this->assertContains('user', $modules);

        $customerView = AdminPermissions::forAction('customer', 'view');
        $this->assertContains('customer-view', $customerView);

        $customerDetail = AdminPermissions::forAction('customer', 'detail');
        $this->assertContains('customer-detail', $customerDetail);

        $customerEdit = AdminPermissions::forAction('customer', 'edit', true);
        $this->assertContains('customer-update', $customerEdit);
    }

    /** @test */
    public function permission_module_selection_tree_displays_active_modules_and_excludes_obsolete()
    {
        $orderModule = new ModulePermission(['id' => 1, 'name' => 'Order']);
        $orderModule->id = 1;
        $orderPerm = new Permission(['id' => 1, 'name' => 'order-view']);
        $orderModule->setRelation('permission', collect([$orderPerm]));

        $customerModule = new ModulePermission(['id' => 2, 'name' => 'Customer']);
        $customerModule->id = 2;
        $customerPerm = new Permission(['id' => 2, 'name' => 'customer-view']);
        $customerModule->setRelation('permission', collect([$customerPerm]));

        $tree = PermissionModuleSelection::treeFromMenu(collect([$orderModule, $customerModule]));
        $flattened = PermissionModuleSelection::flatten($tree);
        $names = $flattened->pluck('name')->all();

        $this->assertContains('Customer', $names);
        $this->assertContains('Order', $names);

        $obsoleteSlugs = [
            'barber', 'wallet', 'service', 'promotion', 'banner',
            'customer-point', 'report-transaction', 'report-summary',
            'about-us', 'privacy-policy', 'contact-us', 'brand',
            'partner', 'sector', 'internships', 'blog', 'placement-type',
            'testimonial', 'customer-paid'
        ];

        foreach ($names as $name) {
            $slug = \Illuminate\Support\Str::slug($name);
            $this->assertNotContains($slug, $obsoleteSlugs, "Module {$name} should not be in the permission tree");
        }
    }

    /** @test */
    public function customer_detail_permission_mapping_and_translations()
    {
        $perms = AdminPermissions::forAction('customer', 'detail');
        $this->assertSame(['customer-detail'], $perms);

        $request = \Illuminate\Http\Request::create(route('admin-customer-detail', 1), 'GET');
        $route = \Illuminate\Support\Facades\Route::getRoutes()->match($request);
        $request->setRouteResolver(fn () => $route);

        $required = AdminPermissions::forRequest($request);
        $this->assertSame(['customer-detail'], $required);

        $this->assertSame('View Detail', __('permission.permission.customer-detail', [], 'en'));
        $this->assertSame('មើលលម្អិត', __('permission.permission.customer-detail', [], 'km'));
    }

    /** @test */
    public function customer_detail_view_renders_only_customer_information()
    {
        $user = new \App\Models\User(['name' => 'Super Admin', 'role' => 'super_admin']);
        $user->id = 1;
        $this->actingAs($user);

        $customer = new Customer([
            'code' => 'CUST001',
            'name' => 'Alice Customer',
            'phone' => '098765432',
            'address' => '123 Phnom Penh',
            'profile' => 'uploads/profile.jpg',
            'image_1' => 'uploads/doc1.jpg',
            'image_2' => 'uploads/doc2.jpg',
            'status' => 1,
            'payment_restriction' => 0,
            'total_point' => 120,
        ]);
        $customer->id = 99;

        $view = view('admin::pages.customer.detail', [
            'customer' => $customer,
            'routeName' => 'customer',
        ])->render();

        $this->assertStringContainsString('CUST001', $view);
        $this->assertStringContainsString('Alice Customer', $view);
        $this->assertStringContainsString('098765432', $view);
        $this->assertStringContainsString('123 Phnom Penh', $view);
        $this->assertStringContainsString('uploads/profile.jpg', $view);
        $this->assertStringContainsString('uploads/doc1.jpg', $view);
        $this->assertStringContainsString('uploads/doc2.jpg', $view);

        // Ensure orders table and KPI stats grid are omitted
        $this->assertStringNotContainsString('orders-data-table', $view);
        $this->assertStringNotContainsString('kpi-stats-grid', $view);
        $this->assertStringNotContainsString('customer.table.points', $view);

        // Test Khmer localization
        app()->setLocale('km');
        $kmView = view('admin::pages.customer.detail', [
            'customer' => $customer,
            'routeName' => 'customer',
        ])->render();

        $this->assertStringContainsString('ព័ត៌មានលម្អិតអតិថិជន', $kmView);
        $this->assertStringContainsString('ព័ត៌មានអតិថិជន', $kmView);
        $this->assertStringContainsString('ឯកសារ និងរូបភាពអតិថិជន', $kmView);
        $this->assertStringContainsString('កំណត់ត្រាប្រព័ន្ធ និងសវនកម្ម', $kmView);
        $this->assertStringContainsString('កែប្រែអតិថិជន', $kmView);
        $this->assertStringContainsString('ត្រឡប់ទៅបញ្ជី', $kmView);
        $this->assertStringContainsString('រូបភាពទី ១', $kmView);
        $this->assertStringContainsString('រូបភាពទី ២', $kmView);
        $this->assertStringNotContainsString('Database ID', $kmView);
        $this->assertStringNotContainsString('Uploaded', $kmView);
        app()->setLocale('en');
    }
}
