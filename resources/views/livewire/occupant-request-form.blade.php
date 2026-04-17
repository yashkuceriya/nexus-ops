<div>
    @if($submitted)
        {{-- Success Page --}}
        <div class="text-center py-10">
            <div class="mx-auto w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mb-5">
                <svg class="w-8 h-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Request Submitted!</h2>
            <p class="text-gray-600 mb-6">Your request has been received and our team will review it shortly.</p>

            <div class="card p-6 max-w-sm mx-auto mb-6">
                <p class="label-kicker mb-1">Your Tracking Code</p>
                <p class="text-3xl font-mono font-bold text-accent-700 tracking-widest">{{ $trackingToken }}</p>
                <p class="text-xs text-gray-500 mt-2">Save this code to check the status of your request</p>
            </div>

            <div class="flex items-center justify-center gap-3">
                <a href="/request/{{ $trackingToken }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-accent-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    Track Your Request
                </a>
                <a href="/request"
                    class="inline-flex items-center gap-2 rounded-lg bg-white border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    Submit Another
                </a>
            </div>
        </div>
    @else
        {{-- Request Form --}}
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Submit a Service Request</h2>
            <p class="text-gray-600 mt-1">Let us know about any maintenance or service needs in your building. We will address your request as quickly as possible.</p>
        </div>

        <form wire:submit="submit" class="space-y-6">
            {{-- Your Information --}}
            <div class="card overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Your Information</h3>
                </div>
                <div class="px-5 py-4 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" id="name" wire:model="requesterName" placeholder="John Smith"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:ring-1 focus:ring-accent-500 focus:outline-none">
                            @error('requesterName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" id="email" wire:model="requesterEmail" placeholder="john@example.com"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:ring-1 focus:ring-accent-500 focus:outline-none">
                            @error('requesterEmail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-gray-400">(optional)</span></label>
                        <input type="tel" id="phone" wire:model="requesterPhone" placeholder="(555) 123-4567"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:ring-1 focus:ring-accent-500 focus:outline-none sm:max-w-xs">
                        @error('requesterPhone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Request Details --}}
            <div class="card overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Request Details</h3>
                </div>
                <div class="px-5 py-4 space-y-4">
                    {{-- Building / Location --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="project" class="block text-sm font-medium text-gray-700 mb-1">Building <span class="text-red-500">*</span></label>
                            <select id="project" wire:model.live="projectId"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-accent-500 focus:outline-none">
                                <option value="">Select a building...</option>
                                @foreach($this->projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                            @error('projectId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location <span class="text-gray-400">(optional)</span></label>
                            <select id="location" wire:model="locationId"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-accent-500 focus:outline-none"
                                @if(!$projectId) disabled @endif>
                                <option value="">Select a location...</option>
                                @foreach($this->locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }} ({{ ucfirst(str_replace('_', ' ', $location->type)) }})</option>
                                @endforeach
                            </select>
                            @error('locationId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Category --}}
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                        <select id="category" wire:model="category"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-accent-500 focus:outline-none sm:max-w-xs">
                            <option value="">What type of issue?</option>
                            <option value="hvac">Heating / Cooling / Air Quality</option>
                            <option value="plumbing">Plumbing / Water</option>
                            <option value="electrical">Electrical / Lighting</option>
                            <option value="cleaning">Cleaning / Janitorial</option>
                            <option value="pest_control">Pest Control</option>
                            <option value="other">Other</option>
                        </select>
                        @error('category') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                        <textarea id="description" wire:model="description" rows="4"
                            placeholder="Please describe the issue in detail. Include the specific location, what you observed, and when it started..."
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:ring-1 focus:ring-accent-500 focus:outline-none resize-none"></textarea>
                        @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="flex justify-end">
                <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-lg bg-accent-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-accent-500 focus:ring-offset-2 transition-colors disabled:opacity-50">
                    <span wire:loading.remove>Submit Request</span>
                    <span wire:loading>Submitting...</span>
                </button>
            </div>
        </form>
    @endif
</div>
