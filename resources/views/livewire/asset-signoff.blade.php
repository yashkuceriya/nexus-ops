<div class="card p-6" x-data="{ signing: null, pad: null }">
    <div class="flex items-start justify-between mb-5">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 tracking-tight">Electronic Sign-Off</h2>
            <p class="text-sm text-gray-500 mt-1">
                Request and capture tamper-evident approvals for this asset's closeout.
            </p>
        </div>
        <button type="button"
            wire:click="$toggle('showRequestForm')"
            class="inline-flex items-center gap-2 rounded-lg btn-primary transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Request Sign-Off
        </button>
    </div>

    @if($showRequestForm)
        <div class="mb-6 rounded-lg border border-indigo-200 bg-indigo-50/40 p-5">
            <h3 class="text-sm font-semibold text-indigo-900 mb-4">New Sign-Off Request</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Signer Role</label>
                    <select wire:model="signerRole"
                        class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($this->signerRoles as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('signerRole') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Expires In (days)</label>
                    <input type="number" min="1" max="90" wire:model="expiresInDays"
                        class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('expiresInDays') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Notes (optional)</label>
                    <textarea wire:model="requestNotes" rows="3"
                        class="block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Context for the approver..."></textarea>
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" wire:click="$set('showRequestForm', false)"
                    class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                <button type="button" wire:click="requestSignoff"
                    class="inline-flex items-center gap-2 rounded-lg btn-primary">
                    Send Request
                </button>
            </div>
        </div>
    @endif

    @if($this->signoffs->isEmpty())
        <div class="text-center py-10 text-sm text-gray-500">
            No sign-off requests yet. Request one above to begin the closeout approval workflow.
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($this->signoffs as $signoff)
                @php
                    $statusConfig = match($signoff->status) {
                        'approved'  => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'ring' => 'ring-emerald-200', 'label' => 'Approved'],
                        'rejected'  => ['bg' => 'bg-red-50',    'text' => 'text-red-700',    'ring' => 'ring-red-200',    'label' => 'Rejected'],
                        'withdrawn' => ['bg' => 'bg-gray-50',   'text' => 'text-gray-600',   'ring' => 'ring-gray-200',   'label' => 'Withdrawn'],
                        default     => ['bg' => 'bg-amber-50',  'text' => 'text-amber-700',  'ring' => 'ring-amber-200',  'label' => 'Pending'],
                    };
                @endphp
                <div class="py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center rounded-full {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} ring-1 ring-inset {{ $statusConfig['ring'] }} px-2.5 py-0.5 text-xs font-medium">
                                    {{ $statusConfig['label'] }}
                                </span>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $this->signerRoles[$signoff->signer_role] ?? $signoff->signer_role }}
                                </span>
                                @if($signoff->isExpired() && $signoff->isPending())
                                    <span class="text-xs text-red-600">(expired)</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Requested by {{ $signoff->requester?->name ?? '—' }}
                                {{ $signoff->requested_at?->diffForHumans() }}
                                @if($signoff->expires_at && $signoff->isPending())
                                    · expires {{ $signoff->expires_at->diffForHumans() }}
                                @endif
                            </p>
                            @if($signoff->notes)
                                <p class="text-sm text-gray-700 mt-2">{{ $signoff->notes }}</p>
                            @endif
                            @if($signoff->isApproved())
                                <div class="mt-2 text-xs text-gray-600">
                                    Signed by <span class="font-medium text-gray-900">{{ $signoff->signer?->name }}</span>
                                    {{ $signoff->signed_at?->toDayDateTimeString() }}
                                </div>
                                @if($signoff->signature_hash)
                                    <div class="mt-1 text-[11px] font-mono text-gray-400 break-all">
                                        sig: {{ substr($signoff->signature_hash, 0, 32) }}…
                                    </div>
                                @endif
                            @endif
                            @if($signoff->status === 'rejected' && $signoff->rejection_reason)
                                <div class="mt-2 rounded-md bg-red-50 border border-red-100 p-3 text-sm text-red-800">
                                    <span class="font-medium">Reason:</span> {{ $signoff->rejection_reason }}
                                </div>
                            @endif
                        </div>

                        @if($signoff->isPending() && ! $signoff->isExpired())
                            <div class="flex flex-col gap-2 w-48">
                                @if($activeSignoffId === $signoff->id)
                                    <div class="rounded-lg border border-gray-200 p-3 bg-gray-50 space-y-2">
                                        <textarea wire:model="approvalNotes" rows="2" placeholder="Approval notes..."
                                            class="block w-full rounded-md border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                        <textarea wire:model="rejectionReason" rows="2" placeholder="Or reason to reject..."
                                            class="block w-full rounded-md border-gray-300 text-xs focus:border-red-500 focus:ring-red-500"></textarea>
                                        @error('rejectionReason') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                        <div class="flex gap-2">
                                            <button type="button"
                                                wire:click="approve({{ $signoff->id }})"
                                                class="flex-1 rounded-md bg-emerald-600 px-2 py-1.5 text-xs font-medium text-white hover:bg-emerald-500">
                                                Approve
                                            </button>
                                            <button type="button"
                                                wire:click="reject({{ $signoff->id }})"
                                                class="flex-1 rounded-md bg-red-600 px-2 py-1.5 text-xs font-medium text-white hover:bg-red-500">
                                                Reject
                                            </button>
                                        </div>
                                        <button type="button" wire:click="$set('activeSignoffId', null)"
                                            class="w-full text-xs text-gray-500 hover:text-gray-700">Cancel</button>
                                    </div>
                                @else
                                    <button type="button" wire:click="$set('activeSignoffId', {{ $signoff->id }})"
                                        class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-500">
                                        Review & Sign
                                    </button>
                                    @if($signoff->requested_by === auth()->id())
                                        <button type="button" wire:click="withdraw({{ $signoff->id }})"
                                            wire:confirm="Withdraw this sign-off request?"
                                            class="rounded-md bg-white px-3 py-1.5 text-xs font-medium text-gray-700 border border-gray-300 hover:bg-gray-50">
                                            Withdraw
                                        </button>
                                    @endif
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
