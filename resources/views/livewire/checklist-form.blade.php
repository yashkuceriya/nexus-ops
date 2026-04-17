<div>
    {{-- Flash Messages --}}
    @if(session('checklist-success'))
    <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 px-5 py-3 text-sm text-emerald-700 font-medium">
        {{ session('checklist-success') }}
    </div>
    @endif

    {{-- Active Checklist Form --}}
    @if($activeCompletionId)
        @php
            $currentStepData = $steps[$currentStep] ?? null;
            $existingResponse = $this->getResponseForCurrentStep();
            $progressPercent = $totalSteps > 0 ? round(($currentStep / $totalSteps) * 100) : 0;
        @endphp

        <div class="card overflow-hidden">
            {{-- Header with progress --}}
            <div class="px-5 py-3.5 border-b border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-gray-900">
                        {{ \App\Models\ChecklistTemplate::find($selectedTemplateId)?->name ?? 'Checklist' }}
                    </h3>
                    <span class="text-xs font-medium text-gray-500">Step {{ $currentStep + 1 }} of {{ $totalSteps }}</span>
                </div>
                {{-- Progress Bar --}}
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-accent-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progressPercent }}%"></div>
                </div>
            </div>

            <div class="px-5 py-5">
                @if($currentStepData)
                    {{-- Step Label --}}
                    <p class="text-base font-medium text-gray-900 mb-4">{{ $currentStepData['label'] }}</p>

                    {{-- Pass/Fail Input --}}
                    @if($currentStepData['type'] === 'pass_fail')
                        <div class="flex gap-3">
                            <button
                                wire:click="saveStepResponse('pass')"
                                class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-semibold transition-colors
                                    {{ ($existingResponse && $existingResponse['value'] === 'pass') ? 'bg-emerald-600 text-white ring-2 ring-emerald-600' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 ring-1 ring-emerald-200' }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                Pass
                            </button>
                            <button
                                wire:click="saveStepResponse('fail')"
                                class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-semibold transition-colors
                                    {{ ($existingResponse && $existingResponse['value'] === 'fail') ? 'bg-red-600 text-white ring-2 ring-red-600' : 'bg-red-50 text-red-700 hover:bg-red-100 ring-1 ring-red-200' }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                Fail
                            </button>
                        </div>

                    {{-- Numeric Input --}}
                    @elseif($currentStepData['type'] === 'numeric')
                        <div x-data="{ numValue: '{{ $existingResponse['value'] ?? '' }}' }">
                            <div class="flex items-center gap-3 mb-2">
                                @if(isset($currentStepData['min']) || isset($currentStepData['max']))
                                <span class="text-xs text-gray-500">
                                    Range: {{ $currentStepData['min'] ?? '---' }} - {{ $currentStepData['max'] ?? '---' }}
                                    @if(isset($currentStepData['unit']))
                                        {{ $currentStepData['unit'] }}
                                    @endif
                                </span>
                                @endif
                            </div>
                            <div class="flex gap-3">
                                <input
                                    type="number"
                                    x-model="numValue"
                                    step="0.1"
                                    @if(isset($currentStepData['min'])) min="{{ $currentStepData['min'] }}" @endif
                                    @if(isset($currentStepData['max'])) max="{{ $currentStepData['max'] }}" @endif
                                    placeholder="Enter value..."
                                    class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-accent-500 focus:outline-none">
                                <button
                                    x-on:click="$wire.saveStepResponse(parseFloat(numValue))"
                                    class="rounded-lg bg-accent-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition-colors">
                                    Save
                                </button>
                            </div>
                        </div>

                    {{-- Text Input --}}
                    @elseif($currentStepData['type'] === 'text')
                        <div x-data="{ textValue: '{{ addslashes($existingResponse['value'] ?? '') }}' }">
                            <textarea
                                x-model="textValue"
                                rows="3"
                                placeholder="Enter notes..."
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-accent-500 focus:outline-none resize-none"></textarea>
                            <button
                                x-on:click="$wire.saveStepResponse(textValue)"
                                class="mt-2 rounded-lg bg-accent-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition-colors">
                                Save
                            </button>
                        </div>

                    {{-- Photo Input --}}
                    @elseif($currentStepData['type'] === 'photo')
                        <div x-data="{ photoNote: '{{ addslashes($existingResponse['value'] ?? '') }}' }">
                            <p class="text-xs text-gray-500 mb-2">Photo upload is available in the mobile app. Add a note below instead.</p>
                            <textarea
                                x-model="photoNote"
                                rows="2"
                                placeholder="Describe what was observed..."
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-accent-500 focus:outline-none resize-none"></textarea>
                            <button
                                x-on:click="$wire.saveStepResponse(photoNote)"
                                class="mt-2 rounded-lg bg-accent-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition-colors">
                                Save
                            </button>
                        </div>
                    @endif

                    {{-- Step indicator dots --}}
                    <div class="flex items-center gap-1.5 mt-5 justify-center">
                        @foreach($steps as $i => $s)
                            @php
                                $answered = collect($responses)->firstWhere('step_order', $s['order']);
                                $dotColor = 'bg-gray-300';
                                if ($answered) {
                                    $dotColor = ($answered['passed'] === false) ? 'bg-red-500' : 'bg-emerald-500';
                                    if ($answered['passed'] === null) $dotColor = 'bg-blue-500';
                                }
                                if ($i === $currentStep) $dotColor .= ' ring-2 ring-offset-2 ring-brand-400';
                            @endphp
                            <button wire:click="goToStep({{ $i }})" class="h-2.5 w-2.5 rounded-full {{ $dotColor }} transition-all" title="Step {{ $i + 1 }}: {{ $s['label'] }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Footer Navigation --}}
            <div class="px-5 py-3.5 border-t border-gray-100 flex items-center justify-between">
                <div class="flex gap-2">
                    <button wire:click="previousStep" @if($currentStep === 0) disabled @endif
                        class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Previous
                    </button>
                    @if($currentStep < $totalSteps - 1)
                    <button wire:click="goToStep({{ $currentStep + 1 }})"
                        class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 transition-colors">
                        Next
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    @endif
                </div>
                <div class="flex gap-2">
                    <button wire:click="cancelChecklist" wire:confirm="Are you sure you want to cancel this checklist?"
                        class="rounded-lg px-4 py-2 text-xs font-medium text-red-600 hover:bg-red-50 transition-colors">
                        Cancel
                    </button>
                    @if(count($responses) === $totalSteps)
                    <button wire:click="completeChecklist"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors">
                        Complete Checklist
                    </button>
                    @endif
                </div>
            </div>
        </div>

    {{-- Template Selection --}}
    @else
        <div class="card overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Inspection Checklists</h3>
            </div>
            <div class="px-5 py-4">
                {{-- Completed Checklists --}}
                @if($this->completions->count() > 0)
                <div class="mb-5">
                    <p class="label-kicker mb-2">Previous Checklists</p>
                    <div class="space-y-2">
                        @foreach($this->completions as $comp)
                        <div class="flex items-center justify-between rounded-lg border px-4 py-3
                            {{ $comp->status === 'completed' ? 'border-emerald-200 bg-emerald-50/50' : ($comp->status === 'failed' ? 'border-red-200 bg-red-50/50' : 'border-blue-200 bg-blue-50/50') }}">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $comp->template->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500">
                                    By {{ $comp->completedByUser->name ?? 'Unknown' }}
                                    @if($comp->completed_at)
                                        &middot; {{ $comp->completed_at->format('M d, g:i A') }}
                                    @endif
                                </p>
                            </div>
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                                {{ $comp->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($comp->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                                {{ ucfirst($comp->status) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Start New Checklist --}}
                <p class="label-kicker mb-2">Start New Checklist</p>
                @forelse($this->templates as $template)
                <button wire:click="startChecklist({{ $template->id }})"
                    class="w-full text-left flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3 hover:border-brand-300 hover:bg-accent-50/50 transition-colors mb-2 group">
                    <div>
                        <p class="text-sm font-medium text-gray-900 group-hover:text-brand-700">{{ $template->name }}</p>
                        <p class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $template->category)) }} &middot; {{ count($template->steps) }} steps</p>
                    </div>
                    <svg class="h-5 w-5 text-gray-400 group-hover:text-accent-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                @empty
                <div class="py-6 text-center">
                    <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="mt-2 text-sm text-gray-500">No checklist templates available</p>
                </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
