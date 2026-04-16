<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold text-gray-900">{{ $vendorId ? 'Edit Vendor' : 'Add Vendor' }}</h2>
        <button wire:click="$dispatch('closeModal')" class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <form wire:submit="save" class="space-y-5">
        {{-- Name --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Vendor Name <span class="text-red-500">*</span></label>
            <input wire:model="name" type="text" id="name"
                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                placeholder="Enter vendor name">
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Contact Name + Email --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-1">Contact Name</label>
                <input wire:model="contact_name" type="text" id="contact_name"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                    placeholder="Primary contact">
                @error('contact_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input wire:model="email" type="email" id="email"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                    placeholder="vendor@example.com">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Phone + License --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input wire:model="phone" type="text" id="phone"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                    placeholder="(555) 123-4567">
                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="insurance_expiry" class="block text-sm font-medium text-gray-700 mb-1">Insurance Expiry</label>
                <input wire:model="insurance_expiry" type="date" id="insurance_expiry"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
                @error('insurance_expiry') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Address --}}
        <div>
            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <input wire:model="address" type="text" id="address"
                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                placeholder="Street address">
            @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- City / State / Zip --}}
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <input wire:model="city" type="text" id="city"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
                @error('city') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="state" class="block text-sm font-medium text-gray-700 mb-1">State</label>
                <input wire:model="state" type="text" id="state"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
                @error('state') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="zip" class="block text-sm font-medium text-gray-700 mb-1">ZIP</label>
                <input wire:model="zip" type="text" id="zip"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
                @error('zip') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Trade Specialties --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Trade Specialties</label>
            <div class="grid grid-cols-3 gap-2">
                @foreach(\App\Livewire\VendorForm::AVAILABLE_TRADES as $trade)
                <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 cursor-pointer hover:bg-gray-50 transition-colors {{ in_array($trade, $trade_specialties) ? 'bg-emerald-50 border-emerald-300' : '' }}">
                    <input type="checkbox" wire:model="trade_specialties" value="{{ $trade }}"
                        class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-xs font-medium text-gray-700">{{ $trade }}</span>
                </label>
                @endforeach
            </div>
            @error('trade_specialties') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Notes --}}
        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea wire:model="notes" id="notes" rows="3"
                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors resize-none"
                placeholder="Additional notes about this vendor..."></textarea>
            @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
            <button type="button" wire:click="$dispatch('closeModal')"
                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                Cancel
            </button>
            <button type="submit" wire:loading.attr="disabled"
                class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors disabled:opacity-50">
                <span wire:loading.remove wire:target="save">{{ $vendorId ? 'Update Vendor' : 'Create Vendor' }}</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </form>
</div>
