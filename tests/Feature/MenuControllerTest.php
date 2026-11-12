<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\CreatesMenuTables;
use Tests\TestCase;

class MenuControllerTest extends TestCase
{
    use CreatesMenuTables;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_products_page_groups_available_products_under_active_categories(): void
    {
        $this->createMenuTables();

        $milkTeaCategoryId = DB::table('categories')->insertGetId([
            'category_name' => 'Milk Tea',
            'description' => 'Creamy favorites made fresh.',
            'is_active' => true,
        ]);
        $pastriesCategoryId = DB::table('categories')->insertGetId([
            'category_name' => 'Pastries',
            'description' => 'Bright, refreshing fruit blends.',
            'is_active' => true,
        ]);
        $inactiveCategoryId = DB::table('categories')->insertGetId([
            'category_name' => 'Hidden Category',
            'is_active' => false,
        ]);

        $brownSugarProductId = DB::table('products')->insertGetId([
            'category_id' => $milkTeaCategoryId,
            'product_name' => 'Brown Sugar Milk Tea',
            'description' => 'Rich brown sugar with creamy milk tea.',
            'base_price' => 89.00,
            'is_available' => true,
        ]);
        DB::table('products')->insert([
            'category_id' => $milkTeaCategoryId,
            'product_name' => 'Unavailable Drink',
            'base_price' => 65.00,
            'is_available' => false,
        ]);
        DB::table('products')->insert([
            'category_id' => $pastriesCategoryId,
            'product_name' => 'Mango Green Tea',
            'description' => 'Fresh mango and fragrant green tea.',
            'base_price' => 79.00,
            'is_available' => true,
        ]);
        DB::table('products')->insert([
            'category_id' => $inactiveCategoryId,
            'product_name' => 'Hidden Drink',
            'base_price' => 50.00,
            'is_available' => true,
        ]);
        DB::table('product_images')->insert([
            'product_id' => $brownSugarProductId,
            'image_path' => 'drinks/brown-sugar.jpg',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('products.index'));

        $response->assertOk()
            ->assertSeeInOrder([
                'Milk Tea',
                'Brown Sugar Milk Tea',
                'Pastries',
                'Mango Green Tea',
            ])
            ->assertSee('₱89.00')
            ->assertSee('/storage/drinks/brown-sugar.jpg')
            ->assertDontSee('Unavailable Drink')
            ->assertDontSee('Hidden Drink')
            ->assertDontSee('Hidden Category');
    }

    public function test_products_page_shows_an_empty_menu_message_when_no_products_are_available(): void
    {
        $this->createMenuTables();

        $this->actingAs(User::factory()->create())
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('Our menu is being prepared')
            ->assertSee('There aren’t any available drinks just yet.');
    }

    public function test_guest_can_search_filter_and_sort_the_menu_together(): void
    {
        $this->createMenuTables();
        $pastriesCategoryId = DB::table('categories')->insertGetId([
            'category_name' => 'Pastries',
            'is_active' => true,
        ]);
        $milkTeaCategoryId = DB::table('categories')->insertGetId([
            'category_name' => 'Milk Tea',
            'is_active' => true,
        ]);
        DB::table('products')->insert([
            ['category_id' => $pastriesCategoryId, 'product_name' => 'Mango Tea', 'base_price' => 80, 'is_available' => true, 'stock_quantity' => 5],
            ['category_id' => $pastriesCategoryId, 'product_name' => 'Mango Passion Tea', 'base_price' => 95, 'is_available' => true, 'stock_quantity' => 5],
            ['category_id' => $pastriesCategoryId, 'product_name' => 'Lemon Tea', 'base_price' => 60, 'is_available' => true, 'stock_quantity' => 5],
            ['category_id' => $milkTeaCategoryId, 'product_name' => 'Taro Milk Tea', 'base_price' => 90, 'is_available' => true, 'stock_quantity' => 5],
        ]);
        $this->get(route('products.index', [
            'search' => 'Mango',
            'category' => $pastriesCategoryId,
            'sort' => 'price_desc',
        ]))
            ->assertOk()
            ->assertSeeInOrder(['Mango Passion Tea', 'Mango Tea'])
            ->assertDontSee('Lemon Tea')
            ->assertDontSee('Taro Milk Tea');
    }

    public function test_product_detail_shows_stock_and_at_most_three_related_products(): void
    {
        $this->createMenuTables();
        $categoryId = DB::table('categories')->insertGetId([
            'category_name' => 'Milk Tea',
            'is_active' => true,
        ]);
        $productIds = [];
        foreach (['Featured Tea', 'Related A', 'Related B', 'Related C', 'Related D'] as $name) {
            $productIds[] = DB::table('products')->insertGetId([
                'category_id' => $categoryId,
                'product_name' => $name,
                'description' => 'A freshly prepared drink.',
                'ingredients' => $name === 'Featured Tea' ? "Black tea\nFresh milk" : null,
                'base_price' => 89,
                'is_available' => true,
                'stock_quantity' => $name === 'Featured Tea' ? 0 : 5,
            ]);
        }

        $this->get(route('menu.show', $productIds[0]))
            ->assertOk()
            ->assertSee('Featured Tea')
            ->assertSee('Ingredients')
            ->assertSee('Black tea')
            ->assertSee('Regular')
            ->assertSee('₱89.00')
            ->assertSee('Currently sold out')
            ->assertSee('Related A')
            ->assertSee('Related C')
            ->assertDontSee('Related D');
    }

    public function test_menu_buy_now_submits_the_selected_quantity(): void
    {
        $this->createMenuTables();

        $categoryId = DB::table('categories')->insertGetId([
            'category_name' => 'Milk Tea',
            'is_active' => true,
        ]);
        $productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'product_name' => 'Brown Sugar Milk Tea',
            'base_price' => 89,
            'is_available' => true,
            'stock_quantity' => 5,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('name="quantity" type="number" value="1"', false)
            ->assertSee('formaction="'.route('buy-now', $productId).'"', false)
            ->assertDontSee('<input type="hidden" name="quantity" value="1">', false);
    }

    public function test_homepage_shows_catalog_categories_featured_products_and_active_promotions(): void
    {
        $this->createMenuTables();

        $categoryId = DB::table('categories')->insertGetId([
            'category_name' => 'Milk Tea',
            'description' => 'Creamy favorites made fresh.',
            'is_active' => true,
        ]);
        DB::table('products')->insert([
            'category_id' => $categoryId,
            'product_name' => 'Brown Sugar Milk Tea',
            'description' => 'Rich brown sugar with creamy milk tea.',
            'base_price' => 89,
            'is_available' => true,
        ]);
        $inactiveCategoryId = DB::table('categories')->insertGetId([
            'category_name' => 'Hidden Category',
            'is_active' => false,
        ]);
        DB::table('products')->insert([
            'category_id' => $inactiveCategoryId,
            'product_name' => 'Hidden Drink',
            'base_price' => 50,
            'is_available' => true,
        ]);
        DB::table('promotions')->insert([
            'title' => 'Summer sips',
            'description' => 'Fresh drinks for sunny days.',
            'is_active' => true,
        ]);
        DB::table('promotions')->insert([
            'title' => 'Expired offer',
            'description' => 'This promotion has ended.',
            'is_active' => true,
            'ends_at' => now()->subDay(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Freshly made happiness,')
            ->assertSee('one sip at a time.')
            ->assertSee('Shop now')
            ->assertSee('Find your kind of sip.')
            ->assertSee('Our story')
            ->assertSee('My account')
            ->assertSee('Coffee')
            ->assertSee('Pastries')
            ->assertSee('data-coffee-shader', false)
            ->assertSee('images/sipply-logo.png')
            ->assertSee(route('categories.show', 'coffee'), false)
            ->assertSee(route('categories.show', 'milk-tea'), false)
            ->assertSee(route('categories.show', 'pastries'), false)
            ->assertSee('Milk Tea')
            ->assertSee('Brown Sugar Milk Tea')
            ->assertSee('Summer sips')
            ->assertSee('Fresh drinks for sunny days.')
            ->assertSee(route('categories.show', 'milk-tea'), false)
            ->assertSee('Bantayan Public Market area, Bantayan, Cebu')
            ->assertSee('Facebook')
            ->assertSee('Instagram')
            ->assertDontSee('Hidden Category')
            ->assertDontSee('Hidden Drink')
            ->assertDontSee('Expired offer')
            ->assertDontSee('This promotion has ended.');
    }

    public function test_homepage_category_link_opens_its_dedicated_products_page(): void
    {
        $this->createMenuTables();

        $milkTeaCategoryId = DB::table('categories')->insertGetId([
            'category_name' => 'Milk Tea',
            'is_active' => true,
        ]);
        $pastriesCategoryId = DB::table('categories')->insertGetId([
            'category_name' => 'Pastries',
            'is_active' => true,
        ]);
        DB::table('products')->insert([
            ['category_id' => $milkTeaCategoryId, 'product_name' => 'Taro Milk Tea', 'base_price' => 89, 'is_available' => true],
            ['category_id' => $pastriesCategoryId, 'product_name' => 'Mango Green Tea', 'base_price' => 79, 'is_available' => true],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('categories.show', 'milk-tea'), false);

        $this->get(route('categories.show', 'milk-tea'))
            ->assertOk()
            ->assertSee('Milk Tea')
            ->assertSee('Taro Milk Tea')
            ->assertDontSee('Mango Green Tea');
    }

    public function test_category_page_returns_not_found_for_missing_or_inactive_category(): void
    {
        $this->createMenuTables();

        DB::table('categories')->insert([
            'category_name' => 'Milk Tea',
            'is_active' => false,
        ]);

        $this->get(route('categories.show', 'milk-tea'))
            ->assertNotFound();

        $this->get(route('categories.show', 'pastries'))
            ->assertNotFound();
    }

    public function test_category_page_shows_empty_state_when_active_category_has_no_available_products(): void
    {
        $this->createMenuTables();
        DB::table('categories')->insert([
            'category_name' => 'Milk Tea',
            'description' => 'Creamy favorites.',
            'is_active' => true,
        ]);

        $this->get(route('categories.show', 'milk-tea'))
            ->assertOk()
            ->assertSee('Milk Tea')
            ->assertSee('There are no available products in this category right now.');
    }

    public function test_homepage_shows_empty_states_when_the_catalog_has_no_products(): void
    {
        $this->createMenuTables();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Find your kind of sip.')
            ->assertSee('Our categories are being prepared')
            ->assertSee('A few crowd pleasers.')
            ->assertSee('Our featured drinks are being prepared')
            ->assertDontSee('Current offers');
    }
}
