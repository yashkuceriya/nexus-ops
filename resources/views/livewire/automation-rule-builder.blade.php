<div class="">
    <div class="px-6 py-6 max-w-[900px] mx-auto">

        {{-- Header --}}
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('automation.index') }}" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-ink tracking-tight">{{ $ruleId ? 'Edit' : 'Create' }} Automation Rule</h1>
                <p class="mt-1 text-sm text-gray-500">Define triggers, conditions, and actions for workflow automation</p>
            </div>
        </div>

        {{-- Flash Message --}}
        @if(session()->has('success'))
        <div class="mb-5 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
        @endif

        <form wire:submit="save" class="space-y-6">

            {{-- Section 1: Name + Description --}}
            <div class="card/80 p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Rule Details</h2>

                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Rule Name</label>
                        <input wire:model="name" type="text" id="name" placeholder="e.g., Auto-escalate critical HVAC issues"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                        <textarea wire:model="description" id="description" rows="2" placeholder="Describe what this rule does..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"></textarea>
                        @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input wire:model="isActive" type="checkbox" class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-accent-600"></div>
                        </label>
                        <span class="text-sm text-gray-700">Rule is active</span>
                    </div>
                </div>
            </div>

            {{-- Section 2: Trigger Type --}}
            <div class="card/80 p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Trigger</h2>
                <p class="text-sm text-gray-500 mb-4">Select the event that will activate this rule.</p>

                <select wire:model.live="triggerType"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    @foreach($triggerTypes as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                @if(isset($triggerDescriptions[$triggerType]))
                <div class="mt-3 flex items-start gap-2 rounded-lg bg-blue-50 border border-blue-100 px-3 py-2.5">
                    <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <p class="text-xs text-blue-700">{{ $triggerDescriptions[$triggerType] }}</p>
                </div>
                @endif
            </div>

            {{-- Section 3: Conditions --}}
            <div class="card/80 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Conditions</h2>
                        <p class="text-sm text-gray-500 mt-0.5">All conditions must match (AND logic). Leave empty to always trigger.</p>
                    </div>
                    <button type="button" wire:click="addCondition"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Add Condition
                    </button>
                </div>

                @if(count($conditions) === 0)
                <div class="rounded-lg border-2 border-dashed border-gray-200 px-6 py-8 text-center">
                    <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No conditions defined. Rule will trigger for all matching events.</p>
                </div>
                @else
                <div class="space-y-3">
                    @foreach($conditions as $index => $condition)
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 border border-gray-200">
                        <div class="flex-1 grid grid-cols-3 gap-3">
                            <div>
                                <label class="block label-kicker mb-1">Field</label>
                                <select wire:model="conditions.{{ $index }}.field"
                                    class="w-full rounded-lg border border-gray-300 px-2.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                    @foreach($conditionFields as $val => $lbl)
                                    <option value="{{ $val }}">{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block label-kicker mb-1">Operator</label>
                                <select wire:model="conditions.{{ $index }}.operator"
                                    class="w-full rounded-lg border border-gray-300 px-2.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                    @foreach($operators as $val => $lbl)
                                    <option value="{{ $val }}">{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block label-kicker mb-1">Value</label>
                                <input wire:model="conditions.{{ $index }}.value" type="text" placeholder="Enter value..."
                                    class="w-full rounded-lg border border-gray-300 px-2.5 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            </div>
                        </div>
                        <button type="button" wire:click="removeCondition({{ $index }})"
                            class="mt-5 p-1.5 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition" title="Remove condition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    @if(!$loop->last)
                    <div class="flex items-center justify-center">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider bg-white px-2">AND</span>
                    </div>
                    @endif
                    @endforeach
                </div>
                @endif

                @error('conditions.*') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Section 4: Actions --}}
            <div class="card/80 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Actions</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Define what happens when this rule triggers. At least one action is required.</p>
                    </div>
                    <button type="button" wire:click="addAction"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Add Action
                    </button>
                </div>

                @if(count($actions) === 0)
                <div class="rounded-lg border-2 border-dashed border-gray-200 px-6 py-8 text-center">
                    <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No actions defined. Add at least one action.</p>
                </div>
                @else
                <div class="space-y-3">
                    @foreach($actions as $index => $action)
                    <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
                        <div class="flex items-start gap-3">
                            <div class="flex-1 space-y-3">
                                {{-- Action Type --}}
                                <div>
                                    <label class="block label-kicker mb-1">Action Type</label>
                                    <select wire:model.live="actions.{{ $index }}.type"
                                        class="w-full rounded-lg border border-gray-300 px-2.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                        @foreach($actionTypes as $val => $lbl)
                                        <option value="{{ $val }}">{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Contextual fields based on action type --}}
                                @if(($action['type'] ?? '') === 'assign_to_user')
                                <div>
                                    <label class="block label-kicker mb-1">Assign To</label>
                                    <select wire:model="actions.{{ $index }}.user_id"
                                        class="w-full rounded-lg border border-gray-300 px-2.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                        <option value="">Select user...</option>
                                        @foreach($this->users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ ucfirst($user->role) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                @if(($action['type'] ?? '') === 'change_priority')
                                <div>
                                    <label class="block label-kicker mb-1">New Priority</label>
                                    <select wire:model="actions.{{ $index }}.priority"
                                        class="w-full rounded-lg border border-gray-300 px-2.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                        <option value="emergency">Emergency</option>
                                        <option value="critical">Critical</option>
                                        <option value="high">High</option>
                                        <option value="medium">Medium</option>
                                        <option value="low">Low</option>
                                    </select>
                                </div>
                                @endif

                                @if(($action['type'] ?? '') === 'send_notification')
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block label-kicker mb-1">Channel</label>
                                        <select wire:model="actions.{{ $index }}.channel"
                                            class="w-full rounded-lg border border-gray-300 px-2.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                            <option value="email">Email</option>
                                            <option value="database">In-App</option>
                                            <option value="sms">SMS</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block label-kicker mb-1">Recipient</label>
                                        <select wire:model="actions.{{ $index }}.user_id"
                                            class="w-full rounded-lg border border-gray-300 px-2.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                            <option value="">Assigned user</option>
                                            @foreach($this->users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block label-kicker mb-1">Message</label>
                                    <input wire:model="actions.{{ $index }}.message" type="text" placeholder="Custom notification message..."
                                        class="w-full rounded-lg border border-gray-300 px-2.5 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                </div>
                                @endif

                                @if(($action['type'] ?? '') === 'escalate_to_manager')
                                <div>
                                    <label class="block label-kicker mb-1">Escalation Message <span class="text-gray-400 font-normal normal-case">(optional)</span></label>
                                    <input wire:model="actions.{{ $index }}.message" type="text" placeholder="Custom escalation message..."
                                        class="w-full rounded-lg border border-gray-300 px-2.5 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                </div>
                                <div class="flex items-start gap-2 rounded-lg bg-amber-50 border border-amber-100 px-3 py-2">
                                    <svg class="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                    <p class="text-xs text-amber-700">All users with Manager, Admin, or Owner roles in the tenant will be notified.</p>
                                </div>
                                @endif

                                @if(($action['type'] ?? '') === 'create_work_order')
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block label-kicker mb-1">WO Title</label>
                                        <input wire:model="actions.{{ $index }}.template.title" type="text" placeholder="Work order title..."
                                            class="w-full rounded-lg border border-gray-300 px-2.5 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block label-kicker mb-1">Priority</label>
                                        <select wire:model="actions.{{ $index }}.template.priority"
                                            class="w-full rounded-lg border border-gray-300 px-2.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                            <option value="emergency">Emergency</option>
                                            <option value="critical">Critical</option>
                                            <option value="high">High</option>
                                            <option value="medium">Medium</option>
                                            <option value="low">Low</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block label-kicker mb-1">Description</label>
                                    <textarea wire:model="actions.{{ $index }}.template.description" rows="2" placeholder="Work order description..."
                                        class="w-full rounded-lg border border-gray-300 px-2.5 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"></textarea>
                                </div>
                                @endif
                            </div>

                            <button type="button" wire:click="removeAction({{ $index }})"
                                class="mt-5 p-1.5 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition" title="Remove action">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    @if(!$loop->last)
                    <div class="flex items-center justify-center">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider bg-white px-2">THEN</span>
                    </div>
                    @endif
                    @endforeach
                </div>
                @endif

                @error('actions') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('automation.index') }}" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">Cancel</a>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-accent-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-accent-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    {{ $ruleId ? 'Update Rule' : 'Create Rule' }}
                </button>
            </div>

        </form>
    </div>
</div>
