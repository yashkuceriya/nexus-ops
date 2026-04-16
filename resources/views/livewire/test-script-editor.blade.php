<div class="min-h-screen bg-gray-50">
    <div class="px-6 py-6 max-w-[1400px] mx-auto">

        <div class="mb-5">
            <a href="{{ route('fpt.scripts.index') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Script Library
            </a>
        </div>

        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Edit Test Script</h1>
                <p class="mt-1 text-sm text-gray-500">v{{ $script->version }} · {{ $script->status }}</p>
            </div>
            <div class="flex gap-2">
                @if($script->status === 'published')
                    <button wire:click="unpublish" class="rounded-lg bg-white border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        Revert to Draft
                    </button>
                @else
                    <button wire:click="publish" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">
                        Publish
                    </button>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Metadata --}}
            <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Script Details</h2>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" wire:model="name" class="block w-full rounded-md border-gray-300 text-sm">
                        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">System Type</label>
                        <input type="text" wire:model="systemType" class="block w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Cx Level</label>
                        <select wire:model="cxLevel" class="block w-full rounded-md border-gray-300 text-sm">
                            <option value="">— none —</option>
                            <option value="L1">L1 — Factory Witness</option>
                            <option value="L2">L2 — Installation</option>
                            <option value="L3">L3 — Component FPT</option>
                            <option value="L4">L4 — System Integration</option>
                            <option value="L5">L5 — Integrated Systems Test</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                        <textarea wire:model="description" rows="3" class="block w-full rounded-md border-gray-300 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Estimated Duration (min)</label>
                        <input type="number" wire:model="estimatedMinutes" class="block w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <button wire:click="saveMetadata"
                        class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                        Save Details
                    </button>
                </div>
            </div>

            {{-- Steps + Add --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 text-sm font-semibold text-gray-700">
                        Steps ({{ $this->steps->count() }})
                    </div>
                    @if($this->steps->isEmpty())
                        <div class="px-5 py-8 text-center text-sm text-gray-500">
                            No steps yet. Add the first one below.
                        </div>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach($this->steps as $step)
                                <li class="px-5 py-4 flex items-start gap-4">
                                    <div class="flex flex-col items-center">
                                        <button wire:click="moveStep({{ $step->id }}, 'up')"
                                            class="text-gray-400 hover:text-gray-700 disabled:opacity-30"
                                            @if($loop->first) disabled @endif>▲</button>
                                        <span class="text-xs font-mono text-gray-500 my-0.5">{{ $step->sequence }}</span>
                                        <button wire:click="moveStep({{ $step->id }}, 'down')"
                                            class="text-gray-400 hover:text-gray-700 disabled:opacity-30"
                                            @if($loop->last) disabled @endif>▼</button>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="text-sm font-semibold text-gray-900">{{ $step->title }}</h3>
                                            <span class="inline-flex items-center rounded-md bg-gray-100 text-gray-700 px-2 py-0.5 text-[10px] font-medium uppercase">
                                                {{ $step->measurement_type }}
                                            </span>
                                            @if($step->is_critical)
                                                <span class="inline-flex items-center rounded-md bg-red-50 text-red-700 px-2 py-0.5 text-[10px] font-medium uppercase">critical</span>
                                            @endif
                                            @if($step->auto_evaluate)
                                                <span class="inline-flex items-center rounded-md bg-sky-50 text-sky-700 px-2 py-0.5 text-[10px] font-medium uppercase" title="Auto-evaluated on numeric measurement">auto-eval</span>
                                            @endif
                                            @if($step->sensor_metric_key)
                                                <span class="inline-flex items-center rounded-md bg-indigo-50 text-indigo-700 px-2 py-0.5 text-[10px] font-medium">
                                                    BMS: {{ $step->sensor_metric_key }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="mt-1 text-sm text-gray-700">{{ $step->instruction }}</p>
                                        @if($step->expected_value)
                                            <p class="mt-1 text-xs text-gray-500">
                                                Expected: <span class="font-medium text-gray-700">{{ $step->expected_value }}</span>
                                                @if($step->measurement_unit) {{ $step->measurement_unit }} @endif
                                                @if($step->tolerance) ± {{ $step->tolerance }} @endif
                                            </p>
                                        @endif
                                    </div>
                                    <button wire:click="deleteStep({{ $step->id }})" wire:confirm="Delete this step?"
                                        class="text-xs text-red-600 hover:text-red-800 font-medium">Delete</button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4">Add Step</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Title</label>
                            <input type="text" wire:model="newTitle" class="block w-full rounded-md border-gray-300 text-sm">
                            @error('newTitle') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Instruction</label>
                            <textarea wire:model="newInstruction" rows="3" class="block w-full rounded-md border-gray-300 text-sm"></textarea>
                            @error('newInstruction') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Measurement Type</label>
                            <select wire:model.live="newMeasurementType" class="block w-full rounded-md border-gray-300 text-sm">
                                <option value="none">None (observational)</option>
                                <option value="numeric">Numeric</option>
                                <option value="boolean">Boolean (yes/no)</option>
                                <option value="text">Free text</option>
                                <option value="selection">Selection</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Unit</label>
                            <input type="text" wire:model="newUnit" placeholder="°F, psi, kW..." class="block w-full rounded-md border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Expected (display)</label>
                            <input type="text" wire:model="newExpectedValue" class="block w-full rounded-md border-gray-300 text-sm">
                        </div>
                        @if($newMeasurementType === 'numeric')
                            <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Expected Value</label>
                                    <input type="number" step="any" wire:model="newExpectedNumeric" class="block w-full rounded-md border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Tolerance (±)</label>
                                    <input type="number" step="any" wire:model="newTolerance" class="block w-full rounded-md border-gray-300 text-sm">
                                </div>
                            </div>

                            {{-- Auto-evaluation: the headline differentiator of this Cx tool.
                                 Once enabled the runner decides pass/fail on its own from
                                 the operator's measured value. --}}
                            <div class="md:col-span-2 rounded-lg bg-sky-50/60 border border-sky-200 p-4 space-y-3">
                                <label class="inline-flex items-start gap-2">
                                    <input type="checkbox" wire:model.live="newAutoEvaluate" class="mt-0.5 rounded border-gray-300 text-sky-600">
                                    <span class="text-sm">
                                        <span class="font-semibold text-sky-900">Auto-evaluate this step</span>
                                        <span class="block text-xs text-sky-800">
                                            Compute pass/fail from the measured value — removes operator judgement error and speeds up test runs.
                                        </span>
                                    </span>
                                </label>
                                @if($newAutoEvaluate)
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Rule</label>
                                            <select wire:model.live="newEvaluationMode" class="block w-full rounded-md border-gray-300 text-sm">
                                                <option value="within_tolerance">Within tolerance (±)</option>
                                                <option value="greater_than_or_equal">Greater than or equal</option>
                                                <option value="less_than_or_equal">Less than or equal</option>
                                                <option value="between">Between min and max</option>
                                                <option value="exact">Exact match</option>
                                            </select>
                                        </div>
                                        @if(in_array($newEvaluationMode, ['between', 'greater_than_or_equal']))
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Acceptable Min</label>
                                                <input type="number" step="any" wire:model="newAcceptableMin" class="block w-full rounded-md border-gray-300 text-sm">
                                            </div>
                                        @endif
                                        @if(in_array($newEvaluationMode, ['between', 'less_than_or_equal']))
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Acceptable Max</label>
                                                <input type="number" step="any" wire:model="newAcceptableMax" class="block w-full rounded-md border-gray-300 text-sm">
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">BMS Sensor Key (optional)</label>
                            <input type="text" wire:model="newSensorKey" placeholder="e.g. supply_air_temp"
                                class="block w-full rounded-md border-gray-300 text-sm">
                            <p class="text-xs text-gray-500 mt-1">Matches against sensor sources on the tested asset to enable one-tap BMS prefill.</p>
                        </div>
                        <div class="md:col-span-2 flex items-center gap-5">
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="checkbox" wire:model="newIsCritical" class="rounded border-gray-300 text-indigo-600">
                                Critical step
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="checkbox" wire:model="newRequiresPhoto" class="rounded border-gray-300 text-indigo-600">
                                Requires photo
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="checkbox" wire:model="newRequiresWitness" class="rounded border-gray-300 text-indigo-600">
                                Requires witness
                            </label>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button wire:click="addStep" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                            Add Step
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
