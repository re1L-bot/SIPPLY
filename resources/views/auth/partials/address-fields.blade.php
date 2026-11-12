<div class="grid gap-3 sm:grid-cols-2">
    <div>
        <label class="mb-1.5 block text-xs font-semibold text-[#52635a]" for="label-{{ $formKey }}">Address label</label>
        <input class="w-full rounded-xl border border-[#e0e5dc] bg-[#fcfcf9] px-3 py-2.5 text-sm outline-none focus:border-[#70977e]" id="label-{{ $formKey }}" name="label" value="{{ old('label', $address->label ?? '') }}" placeholder="Home, work, etc.">
        @error('label') <p class="mt-1 text-xs text-[#a4473d]">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="mb-1.5 block text-xs font-semibold text-[#52635a]" for="recipient-{{ $formKey }}">Recipient name</label>
        <input class="w-full rounded-xl border border-[#e0e5dc] bg-[#fcfcf9] px-3 py-2.5 text-sm outline-none focus:border-[#70977e]" id="recipient-{{ $formKey }}" name="recipient_name" value="{{ old('recipient_name', $address->recipient_name ?? '') }}" required>
        @error('recipient_name') <p class="mt-1 text-xs text-[#a4473d]">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="mb-1.5 block text-xs font-semibold text-[#52635a]" for="phone-{{ $formKey }}">Phone number</label>
        <input class="w-full rounded-xl border border-[#e0e5dc] bg-[#fcfcf9] px-3 py-2.5 text-sm outline-none focus:border-[#70977e]" id="phone-{{ $formKey }}" name="phone_number" type="tel" value="{{ old('phone_number', $address->phone_number ?? '') }}" required>
        @error('phone_number') <p class="mt-1 text-xs text-[#a4473d]">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="mb-1.5 block text-xs font-semibold text-[#52635a]" for="address-line-{{ $formKey }}">House / street / building</label>
        <input class="w-full rounded-xl border border-[#e0e5dc] bg-[#fcfcf9] px-3 py-2.5 text-sm outline-none focus:border-[#70977e]" id="address-line-{{ $formKey }}" name="address_line" value="{{ old('address_line', $address->address_line ?? '') }}" required>
        @error('address_line') <p class="mt-1 text-xs text-[#a4473d]">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="mb-1.5 block text-xs font-semibold text-[#52635a]" for="barangay-{{ $formKey }}">Barangay</label>
        <input class="w-full rounded-xl border border-[#e0e5dc] bg-[#fcfcf9] px-3 py-2.5 text-sm outline-none focus:border-[#70977e]" id="barangay-{{ $formKey }}" name="barangay" value="{{ old('barangay', $address->barangay ?? '') }}" required>
        @error('barangay') <p class="mt-1 text-xs text-[#a4473d]">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="mb-1.5 block text-xs font-semibold text-[#52635a]" for="city-{{ $formKey }}">Municipality</label>
        <select class="w-full rounded-xl border border-[#e0e5dc] bg-[#fcfcf9] px-3 py-2.5 text-sm outline-none focus:border-[#70977e]" id="city-{{ $formKey }}" name="city" required>
            <option value="">Choose a municipality</option>
            @foreach ($municipalities as $municipality)
                <option value="{{ $municipality }}" @selected(old('city', $address->city ?? '') === $municipality)>{{ $municipality }}</option>
            @endforeach
        </select>
        @error('city') <p class="mt-1 text-xs text-[#a4473d]">{{ $message }}</p> @enderror
    </div>
    <div>
        <span class="mb-1.5 block text-xs font-semibold text-[#52635a]">Province</span>
        <p class="rounded-xl border border-[#e0e5dc] bg-[#f7f8f4] px-3 py-2.5 text-sm text-[#52635a]">Cebu</p>
        <input type="hidden" name="province" value="Cebu">
        @error('province') <p class="mt-1 text-xs text-[#a4473d]">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="mb-1.5 block text-xs font-semibold text-[#52635a]" for="postal-{{ $formKey }}">Postal code <span class="font-normal text-[#87938a]">optional</span></label>
        <input class="w-full rounded-xl border border-[#e0e5dc] bg-[#fcfcf9] px-3 py-2.5 text-sm outline-none focus:border-[#70977e]" id="postal-{{ $formKey }}" name="postal_code" value="{{ old('postal_code', $address->postal_code ?? '') }}">
        @error('postal_code') <p class="mt-1 text-xs text-[#a4473d]">{{ $message }}</p> @enderror
    </div>
    <label class="flex items-center gap-2.5 text-xs font-medium text-[#52635a] sm:col-span-2">
        <input class="size-4 rounded border-[#bdcbb9] text-[#315d47] focus:ring-[#70977e]" type="checkbox" name="is_default" value="1" @checked(old('is_default', $address->is_default ?? false))>
        Set as my default delivery address
    </label>
</div>
