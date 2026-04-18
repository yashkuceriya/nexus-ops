<div>
    @if(!$request && !$searched)
        {{-- Token Input --}}
        <div class="text-center py-10">
            <div class="mx-auto w-16 h-16 bg-accent-100 rounded-full flex items-center justify-center mb-5">
                <svg class="w-8 h-8 text-accent-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-ink mb-2">Track Your Request</h2>
            <p class="text-gray-600 mb-6">Enter the tracking code you received when you submitted your request.</p>

            <div class="max-w-sm mx-auto">
                <div class="flex gap-2">
                    <input type="text" wire:model="token" wire:keydown.enter="lookup" maxlength="8"
                        placeholder="e.g. AB12CD34"
                        class="flex-1 rounded-lg border border-gray-300 px-4 py-3 text-center text-lg font-mono font-semibold tracking-widest text-gray-900 uppercase placeholder:text-gray-400 placeholder:text-sm placeholder:tracking-normal placeholder:font-normal focus:border-accent-600 focus:ring-1 focus:ring-accent-500 focus:outline-none">
                    <button wire:click="lookup"
                        class="rounded-lg bg-accent-600 px-5 py-3 text-sm font-semibold text-white hover:bg-accent-700 transition-colors">
                        Look Up
                    </button>
                </div>
            </div>
        </div>

    @elseif(!$request && $searched)
        {{-- Not Found --}}
        <div class="text-center py-10">
            <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-5">
                <svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-ink mb-2">Request Not Found</h2>
            <p class="text-gray-600 mb-6">We could not find a request with the tracking code "{{ $token }}". Please double-check and try again.</p>

            <button wire:click="$set('searched', false)"
                class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">
                Try Again
            </button>
        </div>

    @else
        {{-- Request Found --}}
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-1">
                <span class="text-sm font-mono font-semibold text-gray-500 bg-gray-100 rounded-md px-2.5 py-1">{{ $request->tracking_token }}</span>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium
                    {{ match($request->status) {
                        'submitted' => 'bg-blue-50 text-blue-700',
                        'acknowledged' => 'bg-indigo-50 text-indigo-700',
                        'in_progress' => 'bg-yellow-50 text-yellow-700',
                        'completed' => 'bg-emerald-50 text-emerald-700',
                        'closed' => 'bg-gray-100 text-gray-600',
                        default => 'bg-gray-50 text-gray-700',
                    } }}">
                    <span class="h-1.5 w-1.5 rounded-full
                        {{ match($request->status) {
                            'submitted' => 'bg-blue-500',
                            'acknowledged' => 'bg-indigo-500',
                            'in_progress' => 'bg-yellow-500',
                            'completed' => 'bg-emerald-500',
                            'closed' => 'bg-gray-400',
                            default => 'bg-gray-500',
                        } }}"></span>
                    {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                </span>
            </div>
            <h2 class="text-xl font-bold text-ink mt-2">{{ ucfirst(str_replace('_', ' ', $request->category)) }} Request</h2>
            <p class="text-sm text-gray-500 mt-1">Submitted {{ $request->created_at->format('M d, Y \a\t g:i A') }}</p>
        </div>

        {{-- Progress Timeline --}}
        <div class="card px-6 py-5 mb-6">
            <div class="flex items-center justify-between">
                @foreach($this->statusSteps as $step)
                <div class="flex flex-col items-center flex-1 relative">
                    @if(!$loop->first)
                    <div class="absolute top-4 right-1/2 w-full h-0.5 -translate-y-1/2
                        {{ $step['is_past'] || $step['is_current'] ? 'bg-accent-500' : 'bg-gray-200' }}"></div>
                    @endif
                    <div class="relative z-10 flex items-center justify-center h-8 w-8 rounded-full border-2 transition-all
                        {{ $step['is_past'] ? 'bg-accent-600 border-accent-600 text-white' : '' }}
                        {{ $step['is_current'] ? 'bg-accent-600 border-accent-600 text-white ring-4 ring-accent-100' : '' }}
                        {{ $step['is_future'] ? 'bg-white border-gray-300 text-gray-400' : '' }}">
                        @if($step['is_past'])
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @elseif($step['is_current'])
                            <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                        @else
                            <span class="text-xs font-semibold">{{ $loop->iteration }}</span>
                        @endif
                    </div>
                    <span class="mt-2 text-xs font-semibold {{ $step['is_past'] || $step['is_current'] ? 'text-gray-900' : 'text-gray-400' }}">{{ $step['label'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Request Details --}}
        <div class="card overflow-hidden mb-6">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Request Details</h3>
            </div>
            <div class="px-5 py-4">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="label-kicker">Building</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $request->project?->name ?? '---' }}</dd>
                    </div>
                    <div>
                        <dt class="label-kicker">Location</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $request->location?->name ?? '---' }}</dd>
                    </div>
                    <div>
                        <dt class="label-kicker">Category</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $request->category)) }}</dd>
                    </div>
                    <div>
                        <dt class="label-kicker">Submitted By</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $request->requester_name }}</dd>
                    </div>
                </dl>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <dt class="label-kicker mb-1">Description</dt>
                    <dd class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $request->description }}</dd>
                </div>
            </div>
        </div>

        {{-- Satisfaction Survey (for completed/closed requests) --}}
        @if(in_array($request->status, ['completed', 'closed']))
            <div class="card overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">How did we do?</h3>
                </div>
                <div class="px-5 py-4">
                    @if($request->satisfaction_rating || $surveySubmitted)
                        <div class="text-center py-4">
                            <div class="flex items-center justify-center gap-1 mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="h-6 w-6 {{ $i <= ($request->satisfaction_rating ?? 0) ? 'text-amber-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            @if($request->satisfaction_comment)
                                <p class="text-sm text-gray-600 italic">"{{ $request->satisfaction_comment }}"</p>
                            @endif
                            <p class="text-xs text-gray-500 mt-2">Thank you for your feedback!</p>
                        </div>
                    @else
                        <p class="text-sm text-gray-600 mb-4">Your request has been completed. We would love to hear your feedback.</p>

                        {{-- Star Rating --}}
                        <div class="flex items-center justify-center gap-1 mb-4" x-data="{ hoverRating: 0 }">
                            @for($i = 1; $i <= 5; $i++)
                                <button
                                    wire:click="$set('rating', {{ $i }})"
                                    x-on:mouseenter="hoverRating = {{ $i }}"
                                    x-on:mouseleave="hoverRating = 0"
                                    class="focus:outline-none transition-transform hover:scale-110">
                                    <svg class="h-8 w-8 transition-colors"
                                        :class="(hoverRating >= {{ $i }} || {{ $i }} <= @js($rating)) ? 'text-amber-400' : 'text-gray-300'"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </button>
                            @endfor
                        </div>

                        @if($rating > 0)
                        <div class="space-y-3">
                            <textarea wire:model="comment" rows="2" placeholder="Any additional comments? (optional)"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-accent-600 focus:ring-1 focus:ring-accent-500 focus:outline-none resize-none"></textarea>
                            <div class="flex justify-end">
                                <button wire:click="submitSurvey"
                                    class="rounded-lg bg-accent-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-accent-700 transition-colors">
                                    Submit Feedback
                                </button>
                            </div>
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        @endif

        {{-- Back to search --}}
        <div class="mt-6 text-center">
            <button wire:click="$set('searched', false); $wire.set('request', null); $wire.set('token', '')"
                x-on:click="$wire.set('searched', false); $wire.set('token', '')"
                class="text-sm text-gray-500 hover:text-accent-700 transition-colors">
                Look up a different request
            </button>
        </div>
    @endif
</div>
