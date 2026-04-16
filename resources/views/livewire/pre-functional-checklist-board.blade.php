@php
    $templates = $this->templates;
    $assets = $this->visibleAssets;
    $cells = $this->cells;
    $runnerTemplate = $this->runnerTemplate;
    $runnerCompletion = $this->runnerCompletion;
    $runnerAsset = $this->runnerAsset;
@endphp

<div class="space-y-6">

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-3">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" wire:model.live="onlyGaps" class="rounded border-gray-300">
            Only show assets with open gaps
        </label>
        <span class="ml-auto flex items-center gap-2 text-xs text-gray-500">
            <a href="{{ route('projects.cx-matrix', $project->id) }}" wire:navigate
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Cx Matrix (FPT) →
            </a>
            <a href="{{ route('projects.turnover', $project->id) }}" wire:navigate
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Turnover Console
            </a>
        </span>
    </div>

    @if($sessionNotice)
        <div class="rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-800">{{ $sessionNotice }}</div>
    @endif

    {{-- Matrix --}}
    @if($templates->count() === 0)
        <div class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center">
            <div class="text-sm font-semibold text-gray-900">No PFC templates yet</div>
            <p class="text-xs text-gray-500 mt-1">Create a Pre-Functional Checklist template (type = pfc) to populate this matrix.</p>
        </div>
    @elseif($assets->count() === 0)
        <div class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center">
            <div class="text-sm font-semibold text-gray-900">All assets are PFC-clean</div>
            <p class="text-xs text-gray-500 mt-1">No gaps remain. You're ready to start functional testing.</p>
        </div>
    @else
        <div class="rounded-xl border border-gray-200 bg-white overflow-hidden overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-[10px] uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold sticky left-0 bg-gray-50 z-10">Asset</th>
                        @foreach($templates as $tpl)
                            <th class="text-left px-3 py-3 font-semibold">
                                <div class="text-gray-900">{{ $tpl->name }}</div>
                                <div class="text-[10px] uppercase tracking-wider text-gray-500">
                                    {{ $tpl->cx_level ?: '—' }} · {{ count($tpl->steps ?? []) }} items
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($assets as $asset)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 sticky left-0 bg-white z-10">
                                <a href="{{ route('assets.show', $asset->id) }}" wire:navigate class="font-medium text-gray-900 hover:text-indigo-600">
                                    {{ $asset->name }}
                                </a>
                                <div class="text-[11px] text-gray-500">{{ $asset->asset_tag }} · {{ $asset->system_type }}</div>
                            </td>
                            @foreach($templates as $tpl)
                                @php
                                    $completion = $cells[$asset->id][$tpl->id] ?? null;
                                    $config = $this->cellConfig($completion);
                                @endphp
                                <td class="px-3 py-3">
                                    <button wire:click="openRunner({{ $asset->id }}, {{ $tpl->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold {{ $config['tone'] }}">
                                        {{ $config['label'] }}
                                        @if($completion && ($completion->pass_count + $completion->fail_count + $completion->na_count) > 0)
                                            <span class="text-[10px] text-gray-500 tabular-nums">({{ $completion->pass_count }}/{{ count($tpl->steps ?? []) }})</span>
                                        @endif
                                    </button>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Runner modal --}}
    @if($runnerTemplate && $runnerCompletion && $runnerAsset)
        <div class="fixed inset-0 z-50 bg-black/40 flex items-start justify-center overflow-y-auto py-8 px-4" wire:click.self="closeRunner">
            <div class="w-full max-w-3xl rounded-2xl bg-white shadow-2xl overflow-hidden">
                <div class="bg-gradient-to-br from-indigo-600 to-purple-700 px-6 py-5 text-white">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-[11px] uppercase tracking-[0.2em] font-bold text-indigo-200">Pre-Functional Checklist · {{ $runnerTemplate->cx_level ?? 'L?' }}</div>
                            <h2 class="text-xl font-bold mt-1">{{ $runnerTemplate->name }}</h2>
                            <div class="text-sm text-indigo-100 mt-0.5">{{ $runnerAsset->name }} · {{ $runnerAsset->asset_tag }}</div>
                        </div>
                        <button wire:click="closeRunner" class="text-indigo-100 hover:text-white text-2xl leading-none">×</button>
                    </div>
                    <div class="mt-3 grid grid-cols-3 gap-3 text-xs text-indigo-100">
                        <div>Pass: <strong class="text-white tabular-nums">{{ $runnerCompletion->pass_count }}</strong></div>
                        <div>Fail: <strong class="text-white tabular-nums">{{ $runnerCompletion->fail_count }}</strong></div>
                        <div>N/A: <strong class="text-white tabular-nums">{{ $runnerCompletion->na_count }}</strong></div>
                    </div>
                </div>

                <div class="px-6 py-5 max-h-[60vh] overflow-y-auto">
                    <ol class="space-y-3">
                        @foreach(($runnerTemplate->steps ?? []) as $step)
                            @php
                                $order = (int) ($step['order'] ?? $loop->iteration);
                                $draft = $responseDraft[$order] ?? null;
                                $current = $draft['status'] ?? null;
                            @endphp
                            <li class="rounded-lg border border-gray-200 p-4" wire:key="pfc-step-{{ $runnerCompletion->id }}-{{ $order }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1">
                                        <div class="text-sm font-semibold text-gray-900">{{ $order }}. {{ $step['title'] ?? 'Step '.$order }}</div>
                                        @if(!empty($step['description']))
                                            <div class="text-xs text-gray-500 mt-1">{{ $step['description'] }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach([
                                        ['key' => 'pass', 'label' => 'Pass'],
                                        ['key' => 'fail', 'label' => 'Fail'],
                                        ['key' => 'na', 'label' => 'N/A'],
                                    ] as $opt)
                                        @php
                                            $active = $current === $opt['key'];
                                        @endphp
                                        <button type="button"
                                            wire:click="setResponse({{ $order }}, '{{ $opt['key'] }}', @js($draft['notes'] ?? null))"
                                            class="inline-flex items-center rounded-lg border px-3 py-1.5 text-xs font-semibold {{ $this->pfcResponseButtonClass($opt['key'], $active) }}">
                                            {{ $opt['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                                @if($current === 'fail')
                                    <div class="mt-3">
                                        <textarea
                                            wire:change="setResponse({{ $order }}, 'fail', $event.target.value)"
                                            rows="2"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs"
                                            placeholder="Required: what's the deficiency? (this becomes the issue description)">{{ $draft['notes'] ?? '' }}</textarea>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </div>

                <div class="border-t border-gray-100 px-6 py-4 flex items-center justify-between bg-gray-50">
                    <button wire:click="closeRunner" class="text-sm text-gray-500 hover:text-gray-800">Close</button>
                    <div class="flex items-center gap-2">
                        <button wire:click="saveRunner(false)" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Save Progress
                        </button>
                        <button wire:click="saveRunner(true)" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Save &amp; Complete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
