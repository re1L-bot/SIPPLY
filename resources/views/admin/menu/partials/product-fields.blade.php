<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-1.5 block text-xs font-semibold text-[#52635a]" for="product-name-{{ $formKey }}">Drink name</label>
        <input class="w-full rounded-xl border border-[#e0e5dc] bg-[#fcfcf9] px-3.5 py-3 text-sm outline-none focus:border-[#70977e]" id="product-name-{{ $formKey }}" name="product_name" value="{{ old('product_name', $product->product_name ?? '') }}" maxlength="180" required>
    </div>
    <div>
        <label class="mb-1.5 block text-xs font-semibold text-[#52635a]" for="product-category-{{ $formKey }}">Category</label>
        <select class="w-full rounded-xl border border-[#e0e5dc] bg-[#fcfcf9] px-3.5 py-3 text-sm outline-none focus:border-[#70977e]" id="product-category-{{ $formKey }}" name="category_id" required>
            <option value="">Choose a category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->category_id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->category_id)>{{ $category->category_name }}{{ $category->is_active ? '' : ' (inactive)' }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1.5 block text-xs font-semibold text-[#52635a]" for="product-price-{{ $formKey }}">Base price (₱)</label>
        <input class="w-full rounded-xl border border-[#e0e5dc] bg-[#fcfcf9] px-3.5 py-3 text-sm outline-none focus:border-[#70977e]" id="product-price-{{ $formKey }}" name="base_price" type="number" min="0.01" max="99999999.99" step="0.01" value="{{ old('base_price', $product->base_price ?? '') }}" required>
    </div>
    <div>
        <label class="mb-1.5 block text-xs font-semibold text-[#52635a]" for="product-stock-{{ $formKey }}">Stock quantity</label>
        <input class="w-full rounded-xl border border-[#e0e5dc] bg-[#fcfcf9] px-3.5 py-3 text-sm outline-none focus:border-[#70977e]" id="product-stock-{{ $formKey }}" name="stock_quantity" type="number" min="0" step="1" value="{{ old('stock_quantity', $product->stock_quantity ?? 5) }}" required>
        <p class="mt-1 text-[11px] text-[#87938a]">Stock is shared across all sizes.</p>
    </div>
    <div>
        <label class="mb-1.5 block text-xs font-semibold text-[#52635a]" for="product-image-{{ $formKey }}">Product image <span class="font-normal text-[#87938a]">optional · JPG, PNG, WebP · up to 5 MB</span></label>
        <input class="w-full rounded-xl border border-[#e0e5dc] bg-[#fcfcf9] px-3.5 py-2.5 text-sm file:mr-3 file:rounded-full file:border-0 file:bg-[#edf3e9] file:px-3 file:py-1 file:text-xs file:font-semibold file:text-[#315d47]" id="product-image-{{ $formKey }}" name="image" type="file" accept="image/jpeg,image/png,image/webp">
    </div>
    <div class="sm:col-span-2">
        <label class="mb-1.5 block text-xs font-semibold text-[#52635a]" for="product-description-{{ $formKey }}">Description</label>
        <textarea class="w-full rounded-xl border border-[#e0e5dc] bg-[#fcfcf9] px-3.5 py-3 text-sm outline-none focus:border-[#70977e]" id="product-description-{{ $formKey }}" name="description" rows="2" maxlength="5000">{{ old('description', $product->description ?? '') }}</textarea>
    </div>
    <div class="sm:col-span-2">
        <label class="mb-1.5 block text-xs font-semibold text-[#52635a]" for="product-ingredients-{{ $formKey }}">Ingredients <span class="font-normal text-[#87938a]">one per line</span></label>
        <textarea class="w-full rounded-xl border border-[#e0e5dc] bg-[#fcfcf9] px-3.5 py-3 text-sm outline-none focus:border-[#70977e]" id="product-ingredients-{{ $formKey }}" name="ingredients" rows="3" maxlength="5000" placeholder="Black tea&#10;Fresh milk&#10;Brown sugar">{{ old('ingredients', $product->ingredients ?? '') }}</textarea>
    </div>
    <div class="sm:col-span-2 rounded-xl bg-[#f7f8f4] p-4">
        <p class="text-xs font-semibold text-[#52635a]">Product sizes & specific prices</p>
        <p class="mt-1 text-[11px] text-[#87938a]">Set specific prices for each size of this drink. Customers will see and purchase at these exact prices.</p>
        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            @foreach ($sizes as $size)
                @php
                    $existingSizePrice = isset($product) && isset($productSizePrices) && isset($productSizePrices[$product->product_id][$size->size_id])
                        ? $productSizePrices[$product->product_id][$size->size_id]
                        : (isset($product) ? number_format((float) $product->base_price + (float) $size->price_adjustment, 2, '.', '') : '');
                @endphp
                <div class="flex items-center gap-3 rounded-xl border border-[#e0e5dc] bg-white p-3">
                    <div class="min-w-24">
                        <span class="block text-sm font-bold text-[#244b3b]">{{ $size->size_name }}</span>
                        <span class="block text-[11px] text-[#87938a]">Base +₱{{ number_format((float) $size->price_adjustment, 2) }}</span>
                    </div>
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-[#748178]">₱</span>
                        <input
                            class="w-full rounded-lg border border-[#dce3d8] bg-[#fcfcf9] py-2 pl-7 pr-3 text-sm font-semibold outline-none focus:border-[#70977e]"
                            id="size-price-{{ $formKey }}-{{ $size->size_id }}"
                            name="size_prices[{{ $size->size_id }}]"
                            type="number"
                            min="0.01"
                            max="99999999.99"
                            step="0.01"
                            placeholder="{{ $existingSizePrice ?: '0.00' }}"
                            value="{{ old('size_prices.'.$size->size_id, $existingSizePrice) }}"
                            data-size-adjustment="{{ (float) $size->price_adjustment }}"
                            data-form-key="{{ $formKey }}"
                        >
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <label class="flex items-center gap-2.5 text-xs font-medium text-[#52635a] sm:col-span-2">
        <input class="size-4 rounded border-[#bdcbb9] text-[#315d47] focus:ring-[#70977e]" type="checkbox" name="is_available" value="1" @checked(old('is_available', $product->is_available ?? true))>
        Available for customers to order
    </label>
</div>

<script>
(function () {
    var formKey = '{{ $formKey }}';
    var basePriceInput = document.getElementById('product-price-' + formKey);
    if (!basePriceInput) { return; }

    function syncSizePrices() {
        var base = parseFloat(basePriceInput.value);
        if (isNaN(base) || base <= 0) { return; }

        var sizePriceInputs = document.querySelectorAll('[data-form-key="' + formKey + '"][data-size-adjustment]');
        sizePriceInputs.forEach(function (input) {
            var adjustment = parseFloat(input.dataset.sizeAdjustment) || 0;
            input.value = (base + adjustment).toFixed(2);
        });
    }

    basePriceInput.addEventListener('input', syncSizePrices);
    basePriceInput.addEventListener('change', syncSizePrices);

    {{-- Sync on page load only when creating a new product (no existing value) --}}
    @unless(isset($product))
    if (basePriceInput.value !== '') {
        syncSizePrices();
    }
    @endunless
}());
</script>
