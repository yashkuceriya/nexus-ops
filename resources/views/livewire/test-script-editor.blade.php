<div class="space-y-6">
    <div>
        <a href="{{ route('fpt.scripts.index') }}" wire:navigate class="inline-flex items-center gap-1.5 text-[12px] text-ink-soft hover:text-ink">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Script Library
        </a>
    </div>

    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <p class="label-kicker">Commissioning · FPT Library</p>
            <h1 class="text-2xl font-bold tracking-tight text-ink mt-1">Edit Test Script</h1>
            <p class="text-[13px] text-ink-muted mt-0.5 mono">v{{ $script->version }} · {{ ucfirst($script->status) }}</p>
        </div>
        <div class="flex gap-2">
            @if($script->status === 'published')
                <button wire:click="unpublish" class="btn-ghost">Revert to Draft</button>
            @else
                <button wire:click="publish" class="btn-primary">Publish</button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Metadata --}}
        <div class="card p-5 lg:col-span-1">
            <div class="mb-4">
                <h2 class="text-[15px] font-semibold text-ink">Script Details</h2>
                <p class="text-[12px] text-ink-muted">Metadata for library discovery.</p>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="label-kicker block mb-1">Name</label>
                    <input type="text" wire:model="name" class="block w-full rounded-md border-gray-300 text-[13px]">
                    @error('name') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label-kicker block mb-1">System Type</label>
                    <input type="text" wire:model="systemType" class="block w-full rounded-md border-gray-300 text-[13px]">
                </div>
                <div>
                    <label class="label-kicker block mb-1">Cx Level</label>
                    <select wire:model="cxLevel" class="block w-full rounded-md border-gray-300 text-[13px]">
                        <option value="">— none —</option>
                        <option value="L1">L1 — Factory Witness</option>
                        <option value="L2">L2 — Installation</option>
                        <option value="L3">L3 — Component FPT</option>
                        <option value="L4">L4 — System Integration</option>
                        <option value="L5">L5 — Integrated Systems Test</option>
                    </select>
                </div>
                <div>
                    <label class="label-kicker block mb-1">Description</label>
                    <textarea wire:model="description" rows="3" class="block w-full rounded-md border-gray-300 text-[13px]"></textarea>
                </div>
                <div>
                    <label class="label-kicker block mb-1">Estimated Duration (min)</label>
                    <input type="number" wire:model="estimatedMinutes" class="block w-full rounded-md border-gray-300 text-[13px]">
                </div>
                <button wire:click="saveMetadata" class="btn-primary w-full">Save Details</button>
            </div>
        </div>

        {{-- Steps + Add --}}
        <div class="lg:col-span-2 space-y-5">
            <div class="card overflow-hidden">
                <div class="px-5 py-3 hairline-b flex items-center justify-between">
                    <div>
                        <h2 class="text-[15px] font-semibold text-ink">Steps</h2>
                        <p class="text-[12px] text-ink-muted">{{ $this->steps->count() }} total</p>
                    </div>
                </div>
                @if($this->steps->isEmpty())
                    <div class="px-5 py-10 text-center text-[13px] text-ink-muted">
                        No steps yet. Add the first one below.
                    </div>
                @else
                    <ul>
                        @foreach($this->steps as $step)
                            <li class="px-5 py-4 flex items-start gap-4 hairline-b last:border-b-0">
                                <div class="flex flex-col items-center">
                                    <button wire:click="moveStep({{ $step->id }}, 'up')"
                                        class="text-ink-soft hover:text-ink disabled:opacity-30"
                                        @if($loop->first) disabled @endif>▲</button>
                                    <span class="mono text-[11px] text-ink-soft my-0.5">{{ $step->sequence }}</span>
                                    <button wire:click="moveStep({{ $step->id }}, 'down')"
                                        class="text-ink-soft hover:text-ink disabled:opacity-30"
                                        @if($loop->last) disabled @endif>▼</button>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <h3 class="text-[13px] font-semibold text-ink">{{ $step->title }}</h3>
                                        <span class="chip chip-pending">{{ $step->measurement_type }}</span>
                                        @if($step->is_critical)
                                            <span class="chip chip-fail">Critical</span>
                                        @endif
                                        @if($step->auto_evaluate)
                                            <span class="chip chip-accent" title="Auto-evaluated on numeric measurement">Auto-eval</span>
                                        @endif
                                        @if($step->sensor_metric_key)
                                            <span class="chip chip-accent mono">BMS: {{ $step->sensor_metric_key }}</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-[13px] text-ink">{{ $step->instruction }}</p>
                                    @if($step->expected_value)
                                        <p class="mt-1 text-[11px] text-ink-soft">
                                            Expected: <span class="mono text-ink">{{ $step->expected_value }}</span>
                                            @if($step->measurement_unit) <span class="mono">{{ $step->measurement_unit }}</span> @endif
                                            @if($step->tolerance) ± <span class="mono">{{ $step->tolerance }}</span> @endif
                                        </p>
                                    @endif
                                </div>
                                <button wire:click="deleteStep({{ $step->id }})" wire:confirm="Delete this step?"
                                    class="text-[11px] text-red-600 hover:text-red-800 font-semibold">Delete</button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="card p-5">
                <div class="mb-4">
                    <h2 class="text-[15px] font-semibold text-ink">Add Step</h2>
                    <p class="text-[12px] text-ink-muted">Define the next instruction in this script.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="label-kicker block mb-1">Title</label>
                        <input type="text" wire:model="newTitle" class="block w-full rounded-md border-gray-300 text-[13px]">
                        @error('newTitle') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="label-kicker block mb-1">Instruction</label>
                        <textarea wire:model="newInstruction" rows="3" class="block w-full rounded-md border-gray-300 text-[13px]"></textarea>
                        @error('newInstruction') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label-kicker block mb-1">Measurement Type</label>
                        <select wire:model.live="newMeasurementType" class="block w-full rounded-md border-gray-300 text-[13px]">
                            <option value="none">None (observational)</option>
                            <option value="numeric">Numeric</option>
                            <option value="boolean">Boolean (yes/no)</option>
                            <option value="text">Free text</option>
                            <option value="selection">Selection</option>
                        </select>
                    </div>
                    <div>
                        <label class="label-kicker block mb-1">Unit</label>
                        <input type="text" wire:model="newUnit" placeholder="°F, psi, kW..." class="block w-full rounded-md border-gray-300 text-[13px]">
                    </div>
                    <div>
                        <label class="label-kicker block mb-1">Expected (display)</label>
                        <input type="text" wire:model="newExpectedValue" class="block w-full rounded-md border-gray-300 text-[13px]">
                    </div>
                    @if($newMeasurementType === 'numeric')
                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div>
                                <label class="label-kicker block mb-1">Expected Value</label>
                                <input type="number" step="any" wire:model="newExpectedNumeric" class="block w-full rounded-md border-gray-300 text-[13px]">
                            </div>
                            <div>
                                <label class="label-kicker block mb-1">Tolerance (±)</label>
                                <input type="number" step="any" wire:model="newTolerance" class="block w-full rounded-md border-gray-300 text-[13px]">
                            </div>
                        </div>

                        {{-- Auto-evaluation: the headline differentiator of this Cx tool. --}}
                        <div class="md:col-span-2 rounded-lg bg-accent-50 border border-accent-200 p-4 space-y-3">
                            <label class="inline-flex items-start gap-2">
                                <input type="checkbox" wire:model.live="newAutoEvaluate" class="mt-0.5 rounded border-gray-300 text-accent-600">
                                <span class="text-[13px]">
                                    <span class="font-semibold text-accent-900">Auto-evaluate this step</span>
                                    <span class="block text-[11px] text-accent-800">
                                        Compute pass/fail from the measured value — removes operator judgement error and speeds up test runs.
                                    </span>
                                </span>
                            </label>
                            @if($newAutoEvaluate)
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                    <div>
                                        <label class="label-kicker block mb-1">Rule</label>
                                        <select wire:model.live="newEvaluationMode" class="block w-full rounded-md border-gray-300 text-[13px]">
                                            <option value="within_tolerance">Within tolerance (±)</option>
                                            <option value="greater_than_or_equal">Greater than or equal</option>
                                            <option value="less_than_or_equal">Less than or equal</option>
                                            <option value="between">Between min and max</option>
                                            <option value="exact">Exact match</option>
                                        </select>
                                    </div>
                                    @if(in_array($newEvaluationMode, ['between', 'greater_than_or_equal']))
                                        <div>
                                            <label class="label-kicker block mb-1">Acceptable Min</label>
                                            <input type="number" step="any" wire:model="newAcceptableMin" class="block w-full rounded-md border-gray-300 text-[13px]">
                                        </div>
                                    @endif
                                    @if(in_array($newEvaluationMode, ['between', 'less_than_or_equal']))
                                        <div>
                                            <label class="label-kicker block mb-1">Acceptable Max</label>
                                            <input type="number" step="any" wire:model="newAcceptableMax" class="block w-full rounded-md border-gray-300 text-[13px]">
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif
                    <div class="md:col-span-2">
                        <label class="label-kicker block mb-1">BMS Sensor Key (optional)</label>
                        <input type="text" wire:model="newSensorKey" placeholder="e.g. supply_air_temp"
                            class="block w-full rounded-md border-gray-300 text-[13px]">
                        <p class="text-[11px] text-ink-soft mt-1">Matches against sensor sources on the tested asset to enable one-tap BMS prefill.</p>
                    </div>
                    <div class="md:col-span-2 flex items-center gap-5">
                        <label class="inline-flex items-center gap-2 text-[13px]">
                            <input type="checkbox" wire:model="newIsCritical" class="rounded border-gray-300 text-accent-600">
                            Critical step
                        </label>
                        <label class="inline-flex items-center gap-2 text-[13px]">
                            <input type="checkbox" wire:model="newRequiresPhoto" class="rounded border-gray-300 text-accent-600">
                            Requires photo
                        </label>
                        <label class="inline-flex items-center gap-2 text-[13px]">
                            <input type="checkbox" wire:model="newRequiresWitness" class="rounded border-gray-300 text-accent-600">
                            Requires witness
                        </label>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button wire:click="addStep" class="btn-primary">Add Step</button>
                </div>
            </div>
        </div>
    </div>
</div>
