<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InvestmentSetting;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class ProductPackagingTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_enable_packaging_type_option(): void
    {
        $product = $this->createProduct([
            'packaging_type_enabled' => true,
            'premium_packaging_charge' => 60,
        ]);

        $this->assertTrue($product->refresh()->packaging_type_enabled);
        $this->assertSame('60.00', $product->premium_packaging_charge);
    }

    public function test_product_form_uses_last_packaging_charge_default(): void
    {
        InvestmentSetting::setValue('premium_packaging_charge_default', '60');

        $brand = $this->createBrand();
        $category = $this->createCategory($brand);

        $this->actingAsAdmin();

        $response = $this->post(route('admin.products.store'), [
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'audience' => Product::AUDIENCE_BOTH,
            'name' => 'Graphite Travel Cap',
            'slug' => 'graphite-travel-cap',
            'product_code' => 'GS-CAP-001',
            'regular_price' => 690,
            'sale_price' => null,
            'variants' => [
                [
                    'color' => 'Black',
                    'sizes' => [
                        'M' => 3,
                    ],
                ],
            ],
            'packaging_type_enabled' => '1',
            'status' => Product::STATUS_ACTIVE,
            'images' => [
                $this->fakeProductImage(),
            ],
        ]);

        $response->assertRedirect();

        $product = Product::query()
            ->where('slug', 'graphite-travel-cap')
            ->firstOrFail();

        $this->assertSame('60.00', $product->premium_packaging_charge);
    }

    public function test_storefront_product_payload_includes_packaging_type_flag(): void
    {
        $brand = $this->createBrand();
        $category = $this->createCategory($brand);
        $product = $this->createProduct([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'packaging_type_enabled' => true,
        ]);

        ProductVariant::query()->create([
            'product_id' => $product->id,
            'color' => 'Black',
            'size' => 'M',
            'stock_quantity' => 5,
            'status' => true,
        ]);

        $product->load(['brand', 'category', 'images', 'primaryImage', 'variants']);

        $html = View::make('brands.partials.product-card', [
            'brand' => $brand,
            'product' => $product,
            'position' => 1,
        ])->render();

        $this->assertStringContainsString('"packaging_type_enabled":true', $html);
        $this->assertStringContainsString('"premium_packaging_charge":70', $html);
    }

    public function test_cart_stores_selected_packaging_type(): void
    {
        $brand = $this->createBrand();
        $category = $this->createCategory($brand);
        $product = $this->createProduct([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'packaging_type_enabled' => true,
            'premium_packaging_charge' => 60,
        ]);

        ProductVariant::query()->create([
            'product_id' => $product->id,
            'color' => 'Black',
            'size' => 'M',
            'stock_quantity' => 5,
            'status' => true,
        ]);

        $response = $this->postJson(route('cart.store'), [
            'brand_id' => $brand->id,
            'product_id' => $product->id,
            'color' => 'Black',
            'items' => [
                [
                    'size' => 'M',
                    'quantity' => 1,
                ],
            ],
            'packaging_type' => 'premium',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('cart.items.0.packaging_type', 'premium')
            ->assertJsonPath('cart.items.0.packaging_label', 'Premium Packaging')
            ->assertJsonPath('cart.items.0.packaging_charge', 60)
            ->assertJsonPath('cart.items.0.line_total', 1450)
            ->assertJsonPath('cart.items_total', 1450);
    }

    private function createBrand(): Brand
    {
        return Brand::query()->create([
            'name' => 'Grey Stone',
            'slug' => 'grey-stone',
            'is_active' => true,
        ]);
    }

    private function createCategory(Brand $brand): Category
    {
        return Category::query()->create([
            'brand_id' => $brand->id,
            'name' => 'Shirts',
            'slug' => 'shirts',
            'prefix' => 'SH',
            'status' => 'Active',
        ]);
    }

    private function createProduct(array $overrides = []): Product
    {
        $brand = isset($overrides['brand_id'])
            ? Brand::query()->find($overrides['brand_id'])
            : $this->createBrand();

        $category = isset($overrides['category_id'])
            ? Category::query()->find($overrides['category_id'])
            : $this->createCategory($brand);

        return Product::query()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'audience' => Product::AUDIENCE_BOTH,
            'name' => 'Mercury Oxford Shirt',
            'slug' => 'mercury-oxford-shirt',
            'product_code' => 'GS-DEMO-001',
            'regular_price' => 1390,
            'sale_price' => null,
            'premium_packaging_charge' => 70,
            'stock_quantity' => 5,
            'stock_status' => Product::STOCK_IN,
            'status' => Product::STATUS_ACTIVE,
            ...$overrides,
        ]);
    }

    private function actingAsAdmin(): void
    {
        $this->actingAs(
            \App\Models\User::factory()->create([
                'role' => 'admin',
                'is_active' => true,
            ])
        );
    }

    private function fakeProductImage(): \Illuminate\Http\UploadedFile
    {
        return \Illuminate\Http\UploadedFile::fake()
            ->image('product.jpg', 600, 600);
    }
}
