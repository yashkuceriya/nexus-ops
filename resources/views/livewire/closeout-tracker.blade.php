<div class="min-h-screen bg-gray-50">
    <div class="px-6 py-6 max-w-[1400px] mx-auto">

        {{-- Header --}}
        <div class="mb-5">
            <a href="{{ route('projects.show', $project->id) }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Project
            </a>
        </div>

        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Closeout Tracker</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $project->name }}</p>
            </div>
            <a href="{{ route('projects.turnover-package', $project->id) }}"
                class="inline-flex items-center gap-2 rounded-lg btn-primary transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download Turnover Package
            </a>
        </div>

        {{-- Progress --}}
        <div class="card p-6 mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-700">Project Readiness</h2>
                <span class="text-2xl font-bold text-emerald-600">{{ $this->stats['progress'] }}%</span>
            </div>
            <div class="h-3 w-full rounded-full bg-gray-100 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-emerald-500 to-emerald-600 transition-all"
                    style="width: {{ $this->stats['progress'] }}%"></div>
            </div>
            <div class="grid grid-cols-5 gap-4 mt-5">
                <div>
                    <div class="label-kicker">Total</div>
                    <div class="text-xl font-bold text-gray-900">{{ $this->stats['total'] }}</div>
                </div>
                <div>
                    <div class="label-kicker">Approved</div>
                    <div class="text-xl font-bold text-emerald-600">{{ $this->stats['approved'] }}</div>
                </div>
                <div>
                    <div class="label-kicker">Submitted</div>
                    <div class="text-xl font-bold text-blue-600">{{ $this->stats['submitted'] }}</div>
                </div>
                <div>
                    <div class="label-kicker">Pending</div>
                    <div class="text-xl font-bold text-amber-600">{{ $this->stats['required'] }}</div>
                </div>
                <div>
                    <div class="label-kicker">Overdue</div>
                    <div class="text-xl font-bold text-red-600">{{ $this->stats['overdue'] }}</div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap items-center gap-3 mb-5">
            <select wire:model.live="categoryFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">All Categories</option>
                @foreach($this->categories as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="statusFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">All Statuses</option>
                <option value="required">Required</option>
                <option value="submitted">Submitted</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" wire:model.live="onlyOverdue" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                Overdue only
            </label>
            @if($categoryFilter || $statusFilter || $onlyOverdue)
                <button wire:click="clearFilters" class="text-xs text-gray-500 hover:text-gray-700">Clear filters</button>
            @endif
        </div>

        {{-- List --}}
        @if($this->requirements->isEmpty())
            <div class="card p-12 text-center">
                <h3 class="text-sm font-semibold text-gray-900 mb-1">No requirements match your filters</h3>
                <p class="text-sm text-gray-500">Try widening the filters or seed the project with default closeout requirements.</p>
            </div>
        @else
            <div class="card overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left label-kicker">Requirement</th>
                            <th class="px-4 py-3 text-left label-kicker">Category</th>
                            <th class="px-4 py-3 text-left label-kicker">Asset</th>
                            <th class="px-4 py-3 text-left label-kicker">Due</th>
                            <th class="px-4 py-3 text-left label-kicker">Status</th>
                            <th class="px-4 py-3 text-right label-kicker">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($this->requirements as $req)
                            @php
                                $statusConfig = match($req->status) {
                                    'approved'  => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'label' => 'Approved'],
                                    'submitted' => ['bg' => 'bg-blue-50',    'text' => 'text-blue-700',    'label' => 'Submitted'],
                                    'rejected'  => ['bg' => 'bg-red-50',     'text' => 'text-red-700',     'label' => 'Rejected'],
                                    default     => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'label' => 'Required'],
                                };
                                $isOverdue = $req->status !== 'approved' && $req->due_date && $req->due_date->isPast();
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $req->name }}</div>
                                    @if($req->notes)
                                        <div class="text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($req->notes, 80) }}</div>
                                    @endif
                                    @if($req->document)
                                        <div class="text-xs text-indigo-600 mt-1">
                                            <svg class="inline h-3 w-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            {{ $req->document->file_name }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $this->categories[$req->category] ?? $req->category }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $req->asset?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm {{ $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                                    {{ $req->due_date?->format('M d, Y') ?? '—' }}
                                    @if($isOverdue) <span class="text-[11px]">(overdue)</span> @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} px-2.5 py-0.5 text-xs font-medium">
                                        {{ $statusConfig['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        @if($req->status === 'required')
                                            <button wire:click="markSubmitted({{ $req->id }})"
                                                class="text-xs text-blue-600 hover:text-blue-800 font-medium">Submit</button>
                                        @endif
                                        @if($req->status === 'submitted')
                                            <button wire:click="approve({{ $req->id }})"
                                                class="text-xs text-emerald-600 hover:text-emerald-800 font-medium">Approve</button>
                                            <button wire:click="reject({{ $req->id }})"
                                                class="text-xs text-red-600 hover:text-red-800 font-medium">Reject</button>
                                        @endif
                                        @if($req->status === 'rejected')
                                            <button wire:click="markSubmitted({{ $req->id }})"
                                                class="text-xs text-blue-600 hover:text-blue-800 font-medium">Resubmit</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
