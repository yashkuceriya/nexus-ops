<div class="min-h-screen bg-gray-50">
    <div class="px-6 py-6 max-w-[1400px] mx-auto">

        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Lessons Learned</h1>
                <p class="mt-1 text-sm text-gray-500">
                    A searchable knowledge base of root causes and preventive actions from past projects.
                </p>
            </div>
            <button type="button" wire:click="$toggle('showCreateForm')"
                class="inline-flex items-center gap-2 rounded-lg btn-primary transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Capture Lesson
            </button>
        </div>

        @if($showCreateForm)
            <div class="mb-6 bg-white rounded-xl shadow-sm border border-indigo-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Capture a New Lesson</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" wire:model="title"
                            class="block w-full rounded-md border-gray-300 text-sm"
                            placeholder="Short, descriptive title">
                        @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Category</label>
                        <select wire:model="newCategory" class="block w-full rounded-md border-gray-300 text-sm">
                            @foreach($this->categories as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Severity</label>
                        <select wire:model="newSeverity" class="block w-full rounded-md border-gray-300 text-sm">
                            @foreach($this->severities as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Project (optional)</label>
                        <select wire:model="newProjectId" class="block w-full rounded-md border-gray-300 text-sm">
                            <option value="">—</option>
                            @foreach($this->projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tags (comma-separated)</label>
                        <input type="text" wire:model="tagsInput"
                            class="block w-full rounded-md border-gray-300 text-sm"
                            placeholder="hvac, chiller, controls">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Problem Summary</label>
                        <textarea wire:model="problemSummary" rows="3" class="block w-full rounded-md border-gray-300 text-sm"></textarea>
                        @error('problemSummary') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Root Cause</label>
                        <textarea wire:model="rootCause" rows="3" class="block w-full rounded-md border-gray-300 text-sm"
                            placeholder="Ask 5 Whys — what was the underlying cause?"></textarea>
                        @error('rootCause') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Corrective Action Taken</label>
                        <textarea wire:model="correctiveAction" rows="3" class="block w-full rounded-md border-gray-300 text-sm"></textarea>
                        @error('correctiveAction') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Preventive Action (optional)</label>
                        <textarea wire:model="preventiveAction" rows="3" class="block w-full rounded-md border-gray-300 text-sm"
                            placeholder="What change should we make so this never happens again?"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Recommendation for Future Projects (optional)</label>
                        <textarea wire:model="recommendation" rows="2" class="block w-full rounded-md border-gray-300 text-sm"></textarea>
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" wire:click="$set('showCreateForm', false)"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                    <button type="button" wire:click="save"
                        class="inline-flex items-center gap-2 rounded-lg btn-primary">
                        Save Lesson
                    </button>
                </div>
            </div>
        @endif

        {{-- Filter Bar --}}
        <div class="flex flex-wrap items-center gap-3 mb-5">
            <div class="flex-1 min-w-[240px]">
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Search title, root cause, action..."
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
            </div>
            <select wire:model.live="category" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">All Categories</option>
                @foreach($this->categories as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="severity" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">All Severities</option>
                @foreach($this->severities as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="projectFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                <option value="">All Projects</option>
                @foreach($this->projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </select>
            @if($search || $category || $severity || $projectFilter)
                <button wire:click="clearFilters" class="text-xs text-gray-500 hover:text-gray-700">Clear filters</button>
            @endif
        </div>

        @if($this->lessons->isEmpty())
            <div class="card p-12 text-center">
                <h3 class="text-sm font-semibold text-gray-900 mb-1">No lessons captured yet</h3>
                <p class="text-sm text-gray-500">Start building your organisational knowledge base by capturing lessons from closed issues.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($this->lessons as $lesson)
                    @php
                        $severityConfig = match($lesson->severity) {
                            'critical' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'ring' => 'ring-red-200'],
                            'high'     => ['bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'ring' => 'ring-orange-200'],
                            'medium'   => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'ring' => 'ring-amber-200'],
                            default    => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'ring' => 'ring-slate-200'],
                        };
                    @endphp
                    <div class="card p-5 hover:shadow-md transition">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <h3 class="text-base font-semibold text-gray-900">{{ $lesson->title }}</h3>
                            <span class="inline-flex items-center rounded-full {{ $severityConfig['bg'] }} {{ $severityConfig['text'] }} ring-1 ring-inset {{ $severityConfig['ring'] }} px-2 py-0.5 text-[11px] font-medium uppercase">
                                {{ $lesson->severity }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-500 mb-3 flex-wrap">
                            <span class="inline-flex items-center rounded-md bg-indigo-50 text-indigo-700 px-2 py-0.5 font-medium">
                                {{ $this->categories[$lesson->category] ?? $lesson->category }}
                            </span>
                            @if($lesson->project)
                                <span>· {{ $lesson->project->name }}</span>
                            @endif
                            <span>· by {{ $lesson->author?->name ?? '—' }}</span>
                            <span>· {{ $lesson->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="space-y-3 text-sm">
                            <div>
                                <div class="label-kicker mb-1">Problem</div>
                                <p class="text-gray-700">{{ \Illuminate\Support\Str::limit($lesson->problem_summary, 180) }}</p>
                            </div>
                            <div>
                                <div class="label-kicker mb-1">Root Cause</div>
                                <p class="text-gray-700">{{ \Illuminate\Support\Str::limit($lesson->root_cause, 180) }}</p>
                            </div>
                            <div>
                                <div class="label-kicker mb-1">Preventive Action</div>
                                <p class="text-gray-700">{{ \Illuminate\Support\Str::limit($lesson->preventive_action ?: $lesson->corrective_action, 180) }}</p>
                            </div>
                        </div>
                        @if(! empty($lesson->tags))
                            <div class="mt-3 flex flex-wrap gap-1">
                                @foreach($lesson->tags as $tag)
                                    <span class="inline-flex items-center rounded-md bg-gray-100 text-gray-700 px-2 py-0.5 text-xs">#{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $this->lessons->links() }}
            </div>
        @endif
    </div>
</div>
