<div class="min-h-screen bg-gray-50">
    <div class="px-6 py-6 max-w-[1400px] mx-auto">

        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Commissioning Test Scripts</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Reusable Functional Performance Test templates for mission-critical systems.
                </p>
            </div>
            <button wire:click="$toggle('showCreate')"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Script
            </button>
        </div>

        @if($showCreate)
            <div class="mb-6 bg-white rounded-xl shadow-sm border border-indigo-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Create Test Script</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" wire:model="newName" class="block w-full rounded-md border-gray-300 text-sm">
                        @error('newName') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">System Type</label>
                        <select wire:model="newSystemType" class="block w-full rounded-md border-gray-300 text-sm">
                            @foreach($this->systemTypes as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                        <textarea wire:model="newDescription" rows="2" class="block w-full rounded-md border-gray-300 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Cx Level</label>
                        <select wire:model="newCxLevel" class="block w-full rounded-md border-gray-300 text-sm">
                            <option value="">— none —</option>
                            <option value="L1">L1 — Factory Witness</option>
                            <option value="L2">L2 — Installation</option>
                            <option value="L3">L3 — Component FPT</option>
                            <option value="L4">L4 — System Integration</option>
                            <option value="L5">L5 — Integrated Systems Test</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Estimated Duration (min)</label>
                        <input type="number" wire:model="estimatedMinutes" min="1" max="480" class="block w-full rounded-md border-gray-300 text-sm">
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('showCreate', false)" class="px-4 py-2 text-sm text-gray-600">Cancel</button>
                    <button wire:click="create" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-500">
                        Create &amp; Edit Steps
                    </button>
                </div>
            </div>
        @endif

        {{-- Filters --}}
        <div class="flex flex-wrap items-center gap-3 mb-5">
            <div class="flex-1 min-w-[240px]">
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Search scripts..."
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            </div>
            <select wire:model.live="systemType" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">All Systems</option>
                @foreach($this->systemTypes as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="source" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">All Sources</option>
                <option value="system">System (built-in)</option>
                <option value="tenant">My Organisation</option>
            </select>
            <select wire:model.live="cxLevel" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">All Cx Levels</option>
                <option value="L1">L1 — Factory Witness</option>
                <option value="L2">L2 — Installation</option>
                <option value="L3">L3 — Component FPT</option>
                <option value="L4">L4 — System Integration</option>
                <option value="L5">L5 — Integrated Systems Test</option>
            </select>
        </div>

        @if($this->scripts->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <h3 class="text-sm font-semibold text-gray-900 mb-1">No scripts found</h3>
                <p class="text-sm text-gray-500">Seed the library with built-in scripts or create your first one above.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($this->scripts as $script)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition flex flex-col">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">{{ $script->name }}</h3>
                                <div class="mt-1 flex items-center gap-2 text-xs flex-wrap">
                                    <span class="inline-flex items-center rounded-md bg-indigo-50 text-indigo-700 px-2 py-0.5 font-medium uppercase">
                                        {{ $this->systemTypes[$script->system_type] ?? $script->system_type }}
                                    </span>
                                    @if($script->is_system)
                                        <span class="inline-flex items-center rounded-md bg-slate-100 text-slate-700 px-2 py-0.5 font-medium">System</span>
                                    @endif
                                    @if($script->cx_level)
                                        <span class="inline-flex items-center rounded-md bg-sky-50 text-sky-700 px-2 py-0.5 font-medium">{{ $script->cx_level }}</span>
                                    @endif
                                    <span class="text-gray-500">v{{ $script->version }}</span>
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium uppercase
                                        {{ $script->status === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ $script->status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @if($script->description)
                            <p class="text-sm text-gray-600 mb-4 line-clamp-3">{{ $script->description }}</p>
                        @endif
                        <div class="mt-auto flex items-center justify-between pt-4 border-t border-gray-100">
                            <div class="text-xs text-gray-500 space-y-0.5">
                                <div>{{ $script->steps_count }} {{ \Illuminate\Support\Str::plural('step', $script->steps_count) }}</div>
                                <div>{{ $script->executions_count }} {{ \Illuminate\Support\Str::plural('run', $script->executions_count) }}</div>
                                @if($script->estimated_duration_minutes)
                                    <div>~{{ $script->estimated_duration_minutes }} min</div>
                                @endif
                            </div>
                            @if(! $script->is_system)
                                <a href="{{ route('fpt.scripts.edit', $script->id) }}" wire:navigate
                                    class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Edit →</a>
                            @else
                                <button wire:click="cloneScript({{ $script->id }})"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800"
                                    title="Clone to my organisation for customisation">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                                    Clone to tenant
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $this->scripts->links() }}</div>
        @endif
    </div>
</div>
