<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_catalog_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_the_admin_login_page_is_available(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('Masuk Admin');
    }

    public function test_paid_orders_get_a_signed_download_url(): void
    {
        $product = Product::create([
            'name' => 'Test Preset',
            'category' => 'Cerah',
            'price' => 25000,
            'is_active' => true,
        ]);

        $order = $product->orders()->create([
            'order_number' => 'INV-TEST-1',
            'access_token' => str_repeat('a', 40),
            'product_name' => $product->name,
            'buyer_name' => 'Pembeli',
            'buyer_email' => 'buyer@example.com',
            'amount' => 25000,
            'status' => 'paid',
            'paid_at' => now(),
            'download_expires_at' => now()->addDays(7),
        ]);

        $this->assertStringContainsString('/unduh/', $order->downloadUrl());
        $this->assertStringContainsString('signature=', $order->downloadUrl());
        $this->assertTrue($order->isDownloadActive());
    }

    public function test_non_admin_cannot_open_admin_area(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/admin/produk')
            ->assertForbidden();
    }

    public function test_product_seeder_is_repeatable_and_uses_fixed_images(): void
    {
        $seeder = new ProductSeeder;
        $seeder->run();
        $seeder->run();

        $this->assertSame(1, Product::where('name', 'Warm Aesthetic Pack')->count());
        $this->assertSame(6, Product::active()->count());

        $product = Product::where('name', 'Warm Aesthetic Pack')->firstOrFail();
        $this->assertSame('https://picsum.photos/id/1018/800/600', $product->image_url);
        $this->assertSame(1, $product->images()->count());

        $this->get(route('catalog.show', $product))
            ->assertOk()
            ->assertSee('https://picsum.photos/id/1018/800/600')
            ->assertSee('Sebelum after')
            ->assertSee('Setelah before');
    }
}
