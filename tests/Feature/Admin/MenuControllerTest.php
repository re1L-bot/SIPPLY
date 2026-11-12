<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesAccountTables;
use Tests\Concerns\CreatesAdminTables;
use Tests\Concerns\CreatesMenuTables;
use Tests\Concerns\CreatesOrderTables;
use Tests\TestCase;

class MenuControllerTest extends TestCase
{
    use CreatesAccountTables;
    use CreatesAdminTables;
    use CreatesMenuTables;
    use CreatesOrderTables;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAccountTables();
        $this->createAdminTables();
        $this->createMenuTables();
        $this->createOrderTables();
        $this->withoutVite();
    }

    public function test_admin_can_view_and_manage_categories(): void
    {
        DB::table('categories')->insert([
            ['category_name' => 'Coffee', 'is_active' => true],
            ['category_name' => 'Milk Tea', 'is_active' => true],
            ['category_name' => 'Pastries', 'is_active' => true],
        ]);

        $this->actingAs($this->createAdmin(), 'admin')
            ->get(route('admin.menu.index'))
            ->assertOk()
            ->assertSee('Category Management')
            ->assertSee('Coffee')
            ->assertSee('Milk Tea')
            ->assertSee('Pastries');
    }

    public function test_admin_can_create_update_and_delete_a_category(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.categories.store'), [
                'category_name' => 'Smoothies',
                'description' => 'Blended fresh fruit drinks',
            ])
            ->assertRedirect(route('admin.menu.index', ['_fragment' => 'categories']));

        $this->assertDatabaseHas('categories', [
            'category_name' => 'Smoothies',
            'description' => 'Blended fresh fruit drinks',
        ]);

        // Duplicate name prevention
        $this->actingAs($admin, 'admin')
            ->post(route('admin.categories.store'), [
                'category_name' => 'Smoothies',
            ])
            ->assertSessionHasErrors('category_name');

        $categoryId = DB::table('categories')->where('category_name', 'Smoothies')->value('category_id');

        // Update category
        $this->actingAs($admin, 'admin')
            ->put(route('admin.categories.update', $categoryId), [
                'category_name' => 'Fruit Smoothies',
                'description' => 'Updated description',
            ])
            ->assertRedirect(route('admin.menu.index', ['_fragment' => 'categories']));

        $this->assertDatabaseHas('categories', [
            'category_id' => $categoryId,
            'category_name' => 'Fruit Smoothies',
        ]);

        // Delete category
        $this->actingAs($admin, 'admin')
            ->delete(route('admin.categories.destroy', $categoryId))
            ->assertRedirect(route('admin.menu.index', ['_fragment' => 'categories']));

        $this->assertDatabaseMissing('categories', [
            'category_id' => $categoryId,
        ]);
    }

    public function test_admin_can_assign_a_product_to_any_active_category(): void
    {
        $categoryId = DB::table('categories')->insertGetId([
            'category_name' => 'Desserts',
            'is_active' => true,
        ]);

        $this->actingAs($this->createAdmin(), 'admin')
            ->from(route('admin.menu.index'))
            ->post(route('admin.products.store'), [
                'category_id' => $categoryId,
                'product_name' => 'Chocolate Cake',
                'base_price' => '89.00',
                'stock_quantity' => 5,
                'is_available' => '1',
            ])
            ->assertRedirect(route('admin.menu.index', ['_fragment' => 'products']));

        $this->assertDatabaseHas('products', ['product_name' => 'Chocolate Cake', 'category_id' => $categoryId]);
    }

    public function test_admin_can_create_update_and_delete_a_product_with_its_image(): void
    {
        Storage::fake('public');

        $admin = $this->createAdmin();
        $categoryId = $this->createCategory();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.products.store'), [
                'category_id' => $categoryId,
                'product_name' => 'Brown Sugar Milk Tea',
                'description' => 'Brown sugar and fresh milk.',
                'ingredients' => "Black tea\nFresh milk\nBrown sugar",
                'base_price' => '89.00',
                'stock_quantity' => 12,
                'is_available' => '1',
                'image' => $this->fakePng('drink.png'),
            ])
            ->assertRedirect(route('admin.menu.index', ['_fragment' => 'products']));

        $productId = (int) DB::table('products')->where('product_name', 'Brown Sugar Milk Tea')->value('product_id');
        $imagePath = (string) DB::table('product_images')->where('product_id', $productId)->value('image_path');
        $this->assertDatabaseHas('products', [
            'product_id' => $productId,
            'base_price' => '89.00',
            'ingredients' => "Black tea\nFresh milk\nBrown sugar",
            'stock_quantity' => 12,
            'is_available' => true,
        ]);
        Storage::disk('public')->assertExists($imagePath);

        $this->get(route('admin.menu.index'))
            ->assertOk()
            ->assertSee('Brown Sugar Milk Tea')
            ->assertSee('Add a drink')
            ->assertSee('Categories');

        $this->put(route('admin.products.update', $productId), [
            'category_id' => $categoryId,
            'product_name' => 'Brown Sugar Oat Latte',
            'description' => 'Updated description.',
            'ingredients' => "Oat milk\nBrown sugar",
            'base_price' => '99.50',
            'stock_quantity' => 7,
            'image' => $this->fakePng('replacement.png'),
        ])->assertRedirect(route('admin.menu.index', ['_fragment' => 'products']));

        $replacementImagePath = (string) DB::table('product_images')->where('product_id', $productId)->value('image_path');
        Storage::disk('public')->assertMissing($imagePath);
        Storage::disk('public')->assertExists($replacementImagePath);

        $this->assertDatabaseHas('products', [
            'product_id' => $productId,
            'product_name' => 'Brown Sugar Oat Latte',
            'base_price' => '99.50',
            'ingredients' => "Oat milk\nBrown sugar",
            'stock_quantity' => 7,
            'is_available' => false,
        ]);

        $this->delete(route('admin.products.destroy', $productId))
            ->assertRedirect(route('admin.menu.index', ['_fragment' => 'products']));

        $this->assertDatabaseMissing('products', ['product_id' => $productId]);
        Storage::disk('public')->assertMissing($replacementImagePath);
    }

    public function test_admin_can_save_ingredients_and_products_use_active_sizes(): void
    {
        $categoryId = $this->createCategory();
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.products.store'), [
                'category_id' => $categoryId,
                'product_name' => 'Honey Citrus Tea',
                'description' => 'A bright citrus drink.',
                'ingredients' => "Black tea\nHoney\nCalamansi",
                'base_price' => '65.00',
                'stock_quantity' => 8,
                'is_available' => '1',
            ])
            ->assertRedirect(route('admin.menu.index', ['_fragment' => 'products']));

        $productId = (int) DB::table('products')->where('product_name', 'Honey Citrus Tea')->value('product_id');
        $this->assertDatabaseHas('products', [
            'product_id' => $productId,
            'base_price' => '65.00',
            'ingredients' => "Black tea\nHoney\nCalamansi",
        ]);

        $this->put(route('admin.products.update', $productId), [
            'category_id' => $categoryId,
            'product_name' => 'Honey Citrus Tea',
            'description' => 'Updated citrus blend.',
            'ingredients' => "Green tea\nHoney\nCalamansi",
            'base_price' => '75.00',
            'stock_quantity' => 8,
            'is_available' => '1',
        ])->assertRedirect(route('admin.menu.index', ['_fragment' => 'products']));

        $this->assertDatabaseHas('products', [
            'product_id' => $productId,
            'ingredients' => "Green tea\nHoney\nCalamansi",
            'base_price' => '75.00',
        ]);

        $this->get(route('menu.show', $productId))
            ->assertOk()
            ->assertSee('Honey')
            ->assertSee('Calamansi')
            ->assertSee('Regular · ₱75.00')
            ->assertSee('Large · ₱95.00');
    }

    public function test_category_cannot_be_deleted_while_it_has_products(): void
    {
        $categoryId = $this->createCategory();
        $productId = $this->createProduct($categoryId);

        $this->actingAs($this->createAdmin(), 'admin')
            ->delete(route('admin.categories.destroy', $categoryId))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('categories', ['category_id' => $categoryId]);
        $this->assertDatabaseHas('products', ['product_id' => $productId]);
    }

    public function test_product_cannot_be_deleted_while_it_is_in_a_customer_cart(): void
    {
        $productId = $this->createProduct($this->createCategory());
        $cartId = DB::table('carts')->insertGetId([
            'user_id' => User::factory()->create()->user_id,
            'cart_status' => 'active',
        ]);
        DB::table('cart_items')->insert([
            'cart_id' => $cartId,
            'product_id' => $productId,
        ]);

        $this->actingAs($this->createAdmin(), 'admin')
            ->delete(route('admin.products.destroy', $productId))
            ->assertSessionHasErrors('product');

        $this->assertDatabaseHas('products', ['product_id' => $productId]);
    }

    public function test_product_cannot_be_deleted_when_it_is_part_of_order_history(): void
    {
        $productId = $this->createProduct($this->createCategory());
        $orderId = DB::table('orders')->insertGetId([
            'order_number' => 'SP-HISTORY-TEST',
            'order_status' => 'completed',
            'subtotal' => 80,
            'grand_total' => 80,
            'recipient_name' => 'Jamie Rivera',
            'recipient_phone' => '09171234567',
            'delivery_address' => 'Bantayan, Cebu',
            'placed_at' => now(),
        ]);
        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_id' => $productId,
            'product_name' => 'Historical drink',
            'quantity' => 1,
            'unit_price' => 80,
            'line_total' => 80,
        ]);

        $this->actingAs($this->createAdmin(), 'admin')
            ->delete(route('admin.products.destroy', $productId))
            ->assertSessionHasErrors('product');

        $this->assertDatabaseHas('products', ['product_id' => $productId]);
    }

    public function test_customer_accounts_are_forbidden_from_menu_administration(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.menu.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_invalid_product_price_does_not_create_a_menu_item(): void
    {
        $categoryId = $this->createCategory();

        $this->actingAs($this->createAdmin(), 'admin')
            ->from(route('admin.menu.index'))
            ->post(route('admin.products.store'), [
                'category_id' => $categoryId,
                'product_name' => 'Invalid drink',
                'base_price' => '0',
            ])
            ->assertRedirect(route('admin.menu.index'))
            ->assertSessionHasErrors('base_price');

        $this->assertDatabaseMissing('products', ['product_name' => 'Invalid drink']);
    }

    private function createAdmin(): Admin
    {
        return Admin::query()->create([
            'first_name' => 'Jamie',
            'last_name' => 'Rivera',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'admin-password',
            'is_active' => true,
        ]);
    }

    private function createCategory(): int
    {
        return (int) DB::table('categories')->insertGetId([
            'category_name' => 'Coffee',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createProduct(int $categoryId): int
    {
        $productId = (int) DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'product_name' => fake()->unique()->words(2, true),
            'base_price' => 80,
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $productId;
    }

    private function fakePng(string $filename): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $filename,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jf5kAAAAASUVORK5CYII='),
        );
    }
}
