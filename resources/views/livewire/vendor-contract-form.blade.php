<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold text-gray-900">{{ $contractId ? 'Edit Contract' : 'Add Contract' }}</h2>
        <button wire:click="$dispatch('closeModal')" class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <form wire:submit="save" class="space-y-5">
        {{-- Title + Contract Number --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Contract Title <span class="text-red-500">*</span></label>
                <input wire:model="title" type="text" id="title"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                    placeholder="e.g., Annual HVAC Maintenance">
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="contract_number" class="block text-sm font-medium text-gray-700 mb-1">Contract Number</label>
                <input wire:model="contract_number" type="text" id="contract_number"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                    placeholder="e.g., CTR-2026-001">
                @error('contract_number') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Status --}}
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
            <select wire:model="status" id="status"
                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
                <option value="draft">Draft</option>
                <option value="active">Active</option>
                <option value="expired">Expired</option>
                <option value="terminated">Terminated</option>
            </select>
            @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Dates --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date <span class="text-red-500">*</span></label>
                <input wire:model="start_date" type="date" id="start_date"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
                @error('start_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date <span class="text-red-500">*</span></label>
                <input wire:model="end_date" type="date" id="end_date"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors">
                @error('end_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Financial --}}
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label for="monthly_cost" class="block text-sm font-medium text-gray-700 mb-1">Monthly Cost</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">$</span>
                    <input wire:model="monthly_cost" type="number" step="0.01" id="monthly_cost"
                        class="w-full rounded-lg border border-gray-300 pl-7 pr-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                        placeholder="0.00">
                </div>
                @error('monthly_cost') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="annual_value" class="block text-sm font-medium text-gray-700 mb-1">Annual Value</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">$</span>
                    <input wire:model="annual_value" type="number" step="0.01" id="annual_value"
                        class="w-full rounded-lg border border-gray-300 pl-7 pr-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                        placeholder="0.00">
                </div>
                @error('annual_value') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="nte_limit" class="block text-sm font-medium text-gray-700 mb-1">NTE Limit</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">$</span>
                    <input wire:model="nte_limit" type="number" step="0.01" id="nte_limit"
                        class="w-full rounded-lg border border-gray-300 pl-7 pr-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                        placeholder="0.00">
                </div>
                @error('nte_limit') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Auto Renew --}}
        <div class="flex items-center gap-3">
            <label class="relative inline-flex items-center cursor-pointer">
                <input wire:model="auto_renew" type="checkbox" class="sr-only peer">
                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
            </label>
            <span class="text-sm text-gray-700">Auto-renew this contract</span>
        </div>

        {{-- Scope --}}
        <div>
            <label for="scope" class="block text-sm font-medium text-gray-700 mb-1">Scope of Work</label>
            <textarea wire:model="scope" id="scope" rows="3"
                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors resize-none"
                placeholder="Describe the scope of work covered by this contract..."></textarea>
            @error('scope') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
            <button type="button" wire:click="$dispatch('closeModal')"
                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                Cancel
            </button>
            <button type="submit" wire:loading.attr="disabled"
                class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors disabled:opacity-50">
                <span wire:loading.remove wire:target="save">{{ $contractId ? 'Update Contract' : 'Create Contract' }}</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </form>
</div>
