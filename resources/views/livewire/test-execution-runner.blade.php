<div class="min-h-screen bg-gray-50">
    <div class="px-6 py-6 max-w-[1400px] mx-auto">

        {{-- Header --}}
        <div class="mb-5">
            <a href="{{ route('assets.show', $execution->asset_id) }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Asset
            </a>
        </div>

        @php
            $statusConfig = match($execution->status) {
                'passed'      => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'ring' => 'ring-emerald-200'],
                'failed'      => ['bg' => 'bg-red-50',     'text' => 'text-red-700',     'ring' => 'ring-red-200'],
                'aborted'     => ['bg' => 'bg-gray-100',   'text' => 'text-gray-700',    'ring' => 'ring-gray-300'],
                'on_hold'     => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'ring' => 'ring-amber-200'],
                'in_progress' => ['bg' => 'bg-blue-50',    'text' => 'text-blue-700',    'ring' => 'ring-blue-200'],
                default       => ['bg' => 'bg-slate-50',   'text' => 'text-slate-700',   'ring' => 'ring-slate-200'],
            };
        @endphp

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h1 class="text-xl font-bold text-gray-900 tracking-tight">{{ $execution->test_script_name }}</h1>
                        <span class="text-xs text-gray-500">v{{ $execution->test_script_version }}</span>
                        <span class="inline-flex items-center rounded-full {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} ring-1 ring-inset {{ $statusConfig['ring'] }} px-2.5 py-0.5 text-xs font-medium uppercase">
                            {{ str_replace('_', ' ', $execution->status) }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500">
                        {{ $execution->asset?->name }} · {{ $execution->project?->name }}
                        @if($execution->parent_execution_id)
                            · <span class="text-amber-700">Retest of #{{ $execution->parent_execution_id }}</span>
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-emerald-600">{{ $execution->pass_count }}</div>
                        <div class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Pass</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600">{{ $execution->fail_count }}</div>
                        <div class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Fail</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-400">{{ $execution->pending_count }}</div>
                        <div class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Pending</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900">{{ $execution->progressPercent() }}%</div>
                        <div class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Complete</div>
                    </div>
                </div>
            </div>
            <div class="mt-4 h-2 w-full rounded-full bg-gray-100 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-indigo-500 to-indigo-600 transition-all"
                    style="width: {{ $execution->progressPercent() }}%"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Step list --}}
            <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 text-sm font-semibold text-gray-700">Steps</div>
                <div class="divide-y divide-gray-100 max-h-[70vh] overflow-y-auto">
                    @foreach($this->results as $r)
                        @php
                            $rowColor = match($r->status) {
                                'pass'    => 'border-l-emerald-500',
                                'fail'    => 'border-l-red-500',
                                'skipped' => 'border-l-slate-400',
                                'na'      => 'border-l-gray-300',
                                default   => 'border-l-transparent',
                            };
                            $isActive = $currentResultId === $r->id;
                        @endphp
                        <button type="button"
                            wire:click="selectStep({{ $r->id }})"
                            class="w-full text-left px-4 py-3 border-l-4 {{ $rowColor }} hover:bg-gray-50 {{ $isActive ? 'bg-indigo-50/60' : '' }}">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono text-gray-400 w-6">#{{ $r->step_sequence }}</span>
                                <span class="flex-1 text-sm font-medium text-gray-900">{{ $r->step_title }}</span>
                                @if($r->status === 'pass')
                                    <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @elseif($r->status === 'fail')
                                    <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                @elseif($r->status === 'skipped')
                                    <span class="text-[10px] text-gray-400 uppercase">skip</span>
                                @elseif($r->status === 'na')
                                    <span class="text-[10px] text-gray-400 uppercase">n/a</span>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Current step --}}
            <div class="lg:col-span-2">
                @php($current = $this->currentResult)
                @if($current === null)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-10 text-center">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">All steps recorded</h3>
                        <p class="text-sm text-gray-500 mb-6">Close out the execution to lock the record.</p>

                        @if($execution->isInProgress())
                            <button type="button" wire:click="$toggle('showComplete')"
                                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-500">
                                Finish Execution
                            </button>
                        @elseif($execution->status === 'failed')
                            <button type="button" wire:click="retest"
                                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-amber-500">
                                Start Retest
                            </button>
                        @endif

                        @if(! $execution->witness_signed_at && in_array($execution->status, ['passed', 'failed']))
                            <button type="button" wire:click="$toggle('showWitness')"
                                class="ml-2 inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-emerald-500">
                                Witness &amp; Sign
                            </button>
                        @endif

                        @if(in_array($execution->status, ['passed', 'failed', 'aborted']))
                            <a href="{{ route('fpt.report', $execution->id) }}"
                                class="ml-2 inline-flex items-center gap-2 rounded-lg bg-white border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Download PDF Report
                            </a>
                        @endif

                        @if($execution->witness_signed_at)
                            <div class="mt-6 text-sm text-gray-600 max-w-xl mx-auto">
                                @if($execution->witness_signature_image)
                                    <div class="bg-white border border-gray-200 rounded-lg p-3 inline-block mb-3">
                                        <img src="{{ $execution->witness_signature_image }}" alt="signature"
                                            class="max-h-16 mx-auto">
                                    </div>
                                @endif
                                <div>
                                    Witnessed by <span class="font-semibold">{{ $execution->witness?->name }}</span> —
                                    {{ $execution->witness_signed_at->toDayDateTimeString() }}
                                </div>
                                <div class="mt-1 font-mono text-[11px] text-gray-400 break-all">
                                    sig: {{ substr($execution->witness_signature_hash, 0, 32) }}…
                                </div>
                            </div>
                        @endif

                        @if($showWitness)
                            {{-- Signature-pad modal. Captures the drawn signature as
                                 a PNG data URL and pushes it into the Livewire state
                                 before calling `witness()`. --}}
                            <div class="mt-6 text-left max-w-xl mx-auto bg-emerald-50/40 border border-emerald-200 rounded-lg p-4"
                                x-data="signaturePad()" x-init="init()">
                                <div class="text-sm font-semibold text-gray-800 mb-2">Witness Signature</div>
                                <p class="text-xs text-gray-500 mb-2">Sign inside the box below. Your signature will be hashed and stored with the execution record.</p>
                                <canvas x-ref="pad"
                                    class="w-full bg-white border border-gray-300 rounded-md cursor-crosshair"
                                    width="560" height="160"></canvas>
                                <div class="mt-2 flex items-center justify-between">
                                    <button type="button" @click="clear()" class="text-xs text-gray-500 hover:text-gray-700">Clear</button>
                                    <div class="flex gap-2">
                                        <button type="button" wire:click="$set('showWitness', false)" class="text-sm text-gray-500">Cancel</button>
                                        <button type="button" @click="commit()"
                                            class="rounded-md bg-emerald-600 px-4 py-2 text-sm text-white hover:bg-emerald-500">
                                            Sign &amp; Lock
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <script>
                                function signaturePad() {
                                    return {
                                        ctx: null, drawing: false, hasInk: false,
                                        init() {
                                            const c = this.$refs.pad;
                                            this.ctx = c.getContext('2d');
                                            this.ctx.lineCap = 'round';
                                            this.ctx.lineJoin = 'round';
                                            this.ctx.lineWidth = 2.2;
                                            this.ctx.strokeStyle = '#111827';
                                            const pos = (e) => {
                                                const r = c.getBoundingClientRect();
                                                const t = e.touches ? e.touches[0] : e;
                                                return [ (t.clientX - r.left) * (c.width / r.width),
                                                         (t.clientY - r.top)  * (c.height / r.height) ];
                                            };
                                            const start = (e) => { this.drawing = true; this.ctx.beginPath(); this.ctx.moveTo(...pos(e)); e.preventDefault(); };
                                            const move  = (e) => { if (!this.drawing) return; this.ctx.lineTo(...pos(e)); this.ctx.stroke(); this.hasInk = true; };
                                            const end   = ()  => { this.drawing = false; };
                                            c.addEventListener('mousedown', start);
                                            c.addEventListener('mousemove', move);
                                            c.addEventListener('mouseup', end);
                                            c.addEventListener('mouseleave', end);
                                            c.addEventListener('touchstart', start);
                                            c.addEventListener('touchmove', move);
                                            c.addEventListener('touchend', end);
                                        },
                                        clear() {
                                            const c = this.$refs.pad;
                                            this.ctx.clearRect(0, 0, c.width, c.height);
                                            this.hasInk = false;
                                        },
                                        commit() {
                                            const data = this.hasInk ? this.$refs.pad.toDataURL('image/png') : '';
                                            @this.set('witnessSignatureImage', data);
                                            @this.call('witness');
                                        },
                                    };
                                }
                            </script>
                        @endif

                        @if($showComplete)
                            <div class="mt-6 text-left max-w-xl mx-auto bg-indigo-50/40 border border-indigo-200 rounded-lg p-4">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Overall Notes (optional)</label>
                                <textarea wire:model="completionNotes" rows="3" class="block w-full rounded-md border-gray-300 text-sm"></textarea>
                                <div class="mt-3 flex justify-end gap-2">
                                    <button wire:click="$set('showComplete', false)" class="text-sm text-gray-500">Cancel</button>
                                    <button wire:click="complete" class="rounded-md bg-indigo-600 px-4 py-2 text-sm text-white">Confirm Close-out</button>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Step {{ $current->step_sequence }}</div>
                                <h2 class="text-lg font-semibold text-gray-900">{{ $current->step_title }}</h2>
                            </div>
                            @if($current->expected_value)
                                <div class="text-right">
                                    <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Expected</div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $current->expected_value }}
                                        @if($current->measurement_unit) <span class="text-gray-500">{{ $current->measurement_unit }}</span> @endif
                                        @if($current->tolerance) <span class="text-gray-400">± {{ $current->tolerance }}</span> @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="px-6 py-5 space-y-4">
                            <div>
                                <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Instruction</div>
                                <p class="text-sm text-gray-800 whitespace-pre-line">{{ $current->step_instruction }}</p>
                            </div>

                            @if($current->measurement_type !== 'none')
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                                            Measured Value ({{ $current->measurement_type }})
                                        </label>
                                        @if($this->bmsPrefill)
                                            <button type="button" wire:click="applyBmsPrefill"
                                                class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                Pull from BMS ({{ $this->bmsPrefill['value'] }} {{ $this->bmsPrefill['unit'] }})
                                            </button>
                                        @endif
                                    </div>

                                    @if($current->measurement_type === 'numeric')
                                        <input type="number" step="any" wire:model.live.debounce.400ms="measuredNumeric"
                                            class="block w-full rounded-md border-gray-300 text-sm"
                                            placeholder="{{ $current->measurement_unit ? "value in {$current->measurement_unit}" : 'numeric value' }}">

                                        @if($current->evaluation_mode)
                                            @php
                                                $preview = $this->autoEvalPreview;
                                                $previewConfig = match($preview) {
                                                    'pass' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => '✓', 'label' => 'Would PASS auto-eval'],
                                                    'fail' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'border' => 'border-red-200', 'icon' => '✗', 'label' => 'Would FAIL auto-eval'],
                                                    default => null,
                                                };
                                            @endphp
                                            @if($previewConfig)
                                                <div class="mt-1.5 inline-flex items-center gap-1.5 text-xs {{ $previewConfig['text'] }} {{ $previewConfig['bg'] }} {{ $previewConfig['border'] }} border rounded-md px-2 py-1">
                                                    <span class="font-semibold">{{ $previewConfig['icon'] }}</span>
                                                    {{ $previewConfig['label'] }}
                                                    <span class="text-gray-500 ml-1">
                                                        · rule: {{ str_replace('_', ' ', $current->evaluation_mode) }}
                                                    </span>
                                                </div>
                                            @else
                                                <div class="mt-1.5 text-[11px] text-gray-500">
                                                    Auto-evaluated: rule is
                                                    <span class="font-mono">{{ str_replace('_', ' ', $current->evaluation_mode) }}</span>
                                                </div>
                                            @endif
                                        @endif
                                    @elseif($current->measurement_type === 'boolean')
                                        <select wire:model="measuredValue" class="block w-full rounded-md border-gray-300 text-sm">
                                            <option value="">—</option>
                                            <option value="yes">Yes / True</option>
                                            <option value="no">No / False</option>
                                        </select>
                                    @else
                                        <textarea wire:model="measuredValue" rows="2" class="block w-full rounded-md border-gray-300 text-sm"></textarea>
                                    @endif
                                </div>
                            @endif

                            <div>
                                <label class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Technician Notes</label>
                                <textarea wire:model="notes" rows="2" class="block w-full rounded-md border-gray-300 text-sm"
                                    placeholder="Anything worth recording — conditions, observations, caveats..."></textarea>
                            </div>
                        </div>

                        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center gap-3">
                            <button wire:click="pass"
                                class="flex-1 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
                                ✓ Pass
                            </button>
                            <button wire:click="fail"
                                class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-500">
                                ✗ Fail — raises issue
                            </button>
                            <button wire:click="skip"
                                class="rounded-lg bg-white px-4 py-2.5 text-sm font-medium text-gray-700 border border-gray-300 hover:bg-gray-50">
                                Skip
                            </button>
                            <button wire:click="markNa"
                                class="rounded-lg bg-white px-4 py-2.5 text-sm font-medium text-gray-700 border border-gray-300 hover:bg-gray-50">
                                N/A
                            </button>
                        </div>
                    </div>

                    @if($current->issue_id)
                        @php($autoIssue = $current->issue)
                        <div class="mt-4 rounded-lg bg-red-50 border border-red-200 p-4 text-sm space-y-2">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="font-semibold text-red-800">
                                        Deficiency Issue #{{ $current->issue_id }} auto-opened
                                    </div>
                                    @if($autoIssue)
                                        <p class="mt-0.5 text-red-900/90">{{ $autoIssue->title }}</p>
                                    @endif
                                </div>
                                @if($autoIssue)
                                    <div class="flex flex-col items-end gap-1 text-xs">
                                        <span class="inline-flex items-center rounded-full bg-white/70 px-2 py-0.5 font-medium uppercase tracking-wide text-red-700 ring-1 ring-red-200">
                                            {{ str_replace('_', ' ', $autoIssue->status) }}
                                        </span>
                                        <span class="inline-flex items-center rounded-full bg-white/70 px-2 py-0.5 font-medium uppercase tracking-wide text-red-700 ring-1 ring-red-200">
                                            {{ $autoIssue->priority }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            @if($autoIssue && $autoIssue->asset_id)
                                <div class="pt-1">
                                    <a href="{{ route('assets.show', $autoIssue->asset_id) }}"
                                       wire:navigate
                                       class="inline-flex items-center gap-1.5 rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-500">
                                        View asset & open issues →
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
