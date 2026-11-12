<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\CreatesMenuTables;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use CreatesMenuTables;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createMenuTables();
        $this->withoutVite();
    }

    public function test_customer_can_add_a_product_to_their_cart_without_reducing_stock(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 5);

        $this->actingAs($user)
            ->post(route('cart.items.store'), [
                'product_id' => $productId,
                'quantity' => 2,
            ])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('status', 'Added to your cart.');

        $cart = DB::table('carts')->where('user_id', $user->user_id)->first();
        $this->assertNotNull($cart);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->cart_id,
            'product_id' => $productId,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('products', [
            'product_id' => $productId,
            'stock_quantity' => 5,
        ]);
    }

    public function test_guest_can_add_update_and_remove_a_session_cart_item(): void
    {
        $productId = $this->createProduct(stock: 5);
        $secondProductId = $this->createProduct(stock: 5);
        $sizeId = (int) DB::table('product_sizes')->where('size_name', 'Regular')->value('size_id');
        $this->post(route('cart.items.store'), [
            'product_id' => $productId,
            'quantity' => 2,
        ])->assertRedirect(route('cart.index'));
        $this->post(route('cart.items.store'), [
            'product_id' => $secondProductId,
            'quantity' => 1,
        ])->assertRedirect(route('cart.index'));

        $cartItemKey = "p{$productId}-s{$sizeId}";
        $secondCartItemKey = "p{$secondProductId}-s{$sizeId}";

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Cart')
            ->assertSee('value="2"', false);

        $this->put(route('cart.items.update', $cartItemKey), ['quantity' => 4])
            ->assertRedirect(route('cart.index'));
        $this->assertSame(4, session('guest_cart')[$cartItemKey]['quantity']);
        $this->assertSame(1, session('guest_cart')[$secondCartItemKey]['quantity']);

        $this->delete(route('cart.items.destroy', $cartItemKey))
            ->assertRedirect(route('cart.index'));
        $this->assertSame([
            $secondCartItemKey => [
                'product_id' => $secondProductId,
                'size_id' => $sizeId,
                'quantity' => 1,
            ],
        ], session('guest_cart'));
    }

    public function test_customer_cannot_add_more_than_current_stock_to_their_cart(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 2);

        $this->actingAs($user)
            ->from(route('products.index'))
            ->post(route('cart.items.store'), [
                'product_id' => $productId,
                'quantity' => 3,
            ])
            ->assertRedirect(route('products.index'))
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseMissing('carts', ['user_id' => $user->user_id]);
        $this->assertDatabaseHas('products', [
            'product_id' => $productId,
            'stock_quantity' => 2,
        ]);
    }

    public function test_homepage_shows_the_customer_cart_quantity(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 5);
        $cartId = $this->createCart($user);

        DB::table('cart_items')->insert([
            'cart_id' => $cartId,
            'product_id' => $productId,
            'quantity' => 2,
            'unit_price' => 89,
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Cart (2)');
    }

    public function test_buy_now_sends_the_selected_product_and_quantity_to_checkout(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 4);

        $this->actingAs($user)
            ->post(route('buy-now', $productId), ['quantity' => 2])
            ->assertRedirect(route('checkout.index'));
        $buyNow = session('checkout.buy_now');
        $this->assertSame($productId, $buyNow['product_id']);
        $this->assertSame(2, $buyNow['quantity']);
        $this->assertNotEmpty($buyNow['size_id']);

        $this->assertDatabaseMissing('carts', ['user_id' => $user->user_id]);
        $this->assertDatabaseHas('products', [
            'product_id' => $productId,
            'stock_quantity' => 4,
        ]);
    }

    public function test_cart_items_can_be_updated_and_removed_only_by_their_owner(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $productId = $this->createProduct(stock: 5);
        $cartId = $this->createCart($otherUser);
        $cartItemId = DB::table('cart_items')->insertGetId([
            'cart_id' => $cartId,
            'product_id' => $productId,
            'quantity' => 1,
            'unit_price' => 89,
        ]);

        $this->actingAs($user)
            ->put(route('cart.items.update', $cartItemId), ['quantity' => 2])
            ->assertNotFound();

        $this->delete(route('cart.items.destroy', $cartItemId))
            ->assertNotFound();

        $this->assertDatabaseHas('cart_items', [
            'cart_item_id' => $cartItemId,
            'cart_id' => $cartId,
            'quantity' => 1,
        ]);
    }

    public function test_customer_can_add_a_selected_size_at_its_configured_price(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 5);
        $largeSizeId = (int) DB::table('product_sizes')->where('size_name', 'Large')->value('size_id');

        $this->actingAs($user)
            ->post(route('cart.items.store'), [
                'product_id' => $productId,
                'size_id' => $largeSizeId,
                'quantity' => 2,
            ])
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $productId,
            'size_id' => $largeSizeId,
            'quantity' => 2,
            'unit_price' => '109.00',
        ]);
        $this->actingAs($user)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Large')
            ->assertSee('₱218.00');
    }

    public function test_product_stock_is_shared_across_sizes_in_the_cart(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 2);
        $largeSizeId = (int) DB::table('product_sizes')->where('size_name', 'Large')->value('size_id');
        $regularSizeId = (int) DB::table('product_sizes')->where('size_name', 'Regular')->value('size_id');

        $this->actingAs($user)
            ->post(route('cart.items.store'), [
                'product_id' => $productId,
                'size_id' => $regularSizeId,
                'quantity' => 1,
            ])
            ->assertRedirect(route('cart.index'));

        $this->post(route('cart.items.store'), [
            'product_id' => $productId,
            'size_id' => $largeSizeId,
            'quantity' => 2,
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(1, (int) DB::table('cart_items')->where('product_id', $productId)->sum('quantity'));
    }

    public function test_customer_cannot_add_an_inactive_size(): void
    {
        $user = User::factory()->create();
        $productId = $this->createProduct(stock: 5);
        $largeSizeId = (int) DB::table('product_sizes')->where('size_name', 'Large')->value('size_id');
        DB::table('product_sizes')->where('size_id', $largeSizeId)->update(['is_active' => false]);

        $this->actingAs($user)
            ->from(route('products.index'))
            ->post(route('cart.items.store'), [
                'product_id' => $productId,
                'size_id' => $largeSizeId,
                'quantity' => 1,
            ])
            ->assertRedirect(route('products.index'))
            ->assertSessionHasErrors('size_id');

        $this->assertDatabaseMissing('carts', ['user_id' => $user->user_id]);
    }

    private function createProduct(int $stock): int
    {
        $categoryId = DB::table('categories')->insertGetId([
            'category_name' => fake()->unique()->words(2, true),
            'is_active' => true,
        ]);

        $productId = (int) DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'product_name' => fake()->unique()->words(2, true),
            'base_price' => 89,
            'is_available' => true,
            'stock_quantity' => $stock,
        ]);

        return $productId;
    }

    private function createCart(User $user): int
    {
        return (int) DB::table('carts')->insertGetId([
            'user_id' => $user->user_id,
            'cart_status' => 'active',
        ]);
    }
}
