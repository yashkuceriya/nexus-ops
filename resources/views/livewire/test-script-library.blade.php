<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <p class="label-kicker">Commissioning · FPT Library</p>
            <h1 class="text-2xl font-bold tracking-tight text-ink mt-1">Test Scripts</h1>
            <p class="text-[13px] text-ink-muted mt-0.5">Reusable Functional Performance Test templates for mission-critical systems.</p>
        </div>
        <button wire:click="$toggle('showCreate')" class="btn-primary inline-flex items-center gap-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Script
        </button>
    </div>

    @if($showCreate)
        <div class="card p-5 fade-in">
            <div class="mb-4">
                <h2 class="text-[15px] font-semibold text-ink">Create Test Script</h2>
                <p class="text-[12px] text-ink-muted">Define basic metadata — you'll add steps next.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="label-kicker block mb-1">Name</label>
                    <input type="text" wire:model="newName" class="block w-full rounded-md border-gray-300 text-[13px]">
                    @error('newName') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label-kicker block mb-1">System Type</label>
                    <select wire:model="newSystemType" class="block w-full rounded-md border-gray-300 text-[13px]">
                        @foreach($this->systemTypes as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="label-kicker block mb-1">Description</label>
                    <textarea wire:model="newDescription" rows="2" class="block w-full rounded-md border-gray-300 text-[13px]"></textarea>
                </div>
                <div>
                    <label class="label-kicker block mb-1">Cx Level</label>
                    <select wire:model="newCxLevel" class="block w-full rounded-md border-gray-300 text-[13px]">
                        <option value="">— none —</option>
                        <option value="L1">L1 — Factory Witness</option>
                        <option value="L2">L2 — Installation</option>
                        <option value="L3">L3 — Component FPT</option>
                        <option value="L4">L4 — System Integration</option>
                        <option value="L5">L5 — Integrated Systems Test</option>
                    </select>
                </div>
                <div>
                    <label class="label-kicker block mb-1">Estimated Duration (min)</label>
                    <input type="number" wire:model="estimatedMinutes" min="1" max="480" class="block w-full rounded-md border-gray-300 text-[13px]">
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button wire:click="$set('showCreate', false)" class="btn-ghost">Cancel</button>
                <button wire:click="create" class="btn-primary">Create &amp; Edit Steps</button>
            </div>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card p-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[240px]">
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Search scripts..."
                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-[13px]">
            </div>
            <select wire:model.live="systemType" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-[13px]">
                <option value="">All Systems</option>
                @foreach($this->systemTypes as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="source" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-[13px]">
                <option value="">All Sources</option>
                <option value="system">System (built-in)</option>
                <option value="tenant">My Organisation</option>
            </select>
            <select wire:model.live="cxLevel" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-[13px]">
                <option value="">All Cx Levels</option>
                <option value="L1">L1 — Factory Witness</option>
                <option value="L2">L2 — Installation</option>
                <option value="L3">L3 — Component FPT</option>
                <option value="L4">L4 — System Integration</option>
                <option value="L5">L5 — Integrated Systems Test</option>
            </select>
        </div>
    </div>

    @if($this->scripts->isEmpty())
        <div class="card p-12 text-center">
            <h3 class="text-[15px] font-semibold text-ink mb-1">No scripts found</h3>
            <p class="text-[13px] text-ink-muted">Seed the library with built-in scripts or create your first one above.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($this->scripts as $script)
                <div class="card p-5 flex flex-col">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div class="min-w-0">
                            <h3 class="text-[15px] font-semibold text-ink">{{ $script->name }}</h3>
                            <div class="mt-2 flex items-center gap-1.5 flex-wrap">
                                <span class="chip chip-accent">{{ $this->systemTypes[$script->system_type] ?? $script->system_type }}</span>
                                @if($script->is_system)
                                    <span class="chip chip-pending">System</span>
                                @endif
                                @if($script->cx_level)
                                    <span class="chip chip-accent">{{ $script->cx_level }}</span>
                                @endif
                                <span class="mono text-ink-soft text-[11px]">v{{ $script->version }}</span>
                                <span class="chip {{ $script->status === 'published' ? 'chip-pass' : 'chip-warn' }}">{{ ucfirst($script->status) }}</span>
                            </div>
                        </div>
                    </div>
                    @if($script->description)
                        <p class="text-[13px] text-ink-muted mb-4 line-clamp-3">{{ $script->description }}</p>
                    @endif
                    <div class="mt-auto flex items-center justify-between pt-4 hairline-t">
                        <div class="text-[11px] text-ink-soft mono space-y-0.5">
                            <div>{{ $script->steps_count }} {{ \Illuminate\Support\Str::plural('step', $script->steps_count) }}</div>
                            <div>{{ $script->executions_count }} {{ \Illuminate\Support\Str::plural('run', $script->executions_count) }}</div>
                            @if($script->estimated_duration_minutes)
                                <div>~{{ $script->estimated_duration_minutes }} min</div>
                            @endif
                        </div>
                        @if(! $script->is_system)
                            <a href="{{ route('fpt.scripts.edit', $script->id) }}" wire:navigate
                                class="text-[12px] font-semibold text-accent-700 hover:text-accent-800">Edit →</a>
                        @else
                            <button wire:click="cloneScript({{ $script->id }})"
                                class="inline-flex items-center gap-1 text-[12px] font-semibold text-accent-700 hover:text-accent-800"
                                title="Clone to my organisation for customisation">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                                Clone to tenant
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div>{{ $this->scripts->links() }}</div>
    @endif
</div>
