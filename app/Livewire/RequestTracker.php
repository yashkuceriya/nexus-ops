<?php

namespace App\Livewire;

use App\Models\OccupantRequest;
use Livewire\Component;

class RequestTracker extends Component
{
    public string $token = '';
    public ?OccupantRequest $request = null;
    public bool $searched = false;

    public int $rating = 0;
    public string $comment = '';
    public bool $surveySubmitted = false;

    public function mount(?string $token = null): void
    {
        if ($token) {
            $this->token = $token;
            $this->lookup();
        }
    }

    public function lookup(): void
    {
        $this->searched = true;
        $this->request = OccupantRequest::where('tracking_token', strtoupper($this->token))
            ->with('project:id,name', 'location:id,name')
            ->first();
    }

    public function submitSurvey(): void
    {
        $this->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($this->request && in_array($this->request->status, ['completed', 'closed'])) {
            $this->request->update([
                'satisfaction_rating' => $this->rating,
                'satisfaction_comment' => $this->comment ?: null,
            ]);
            $this->surveySubmitted = true;
            $this->request->refresh();
        }
    }

    public function getStatusStepsProperty(): array
    {
        $statuses = ['submitted', 'acknowledged', 'in_progress', 'completed'];
        $currentIndex = $this->request ? array_search($this->request->status, $statuses) : -1;
        if ($currentIndex === false) {
            $currentIndex = $this->request?->status === 'closed' ? 4 : -1;
        }

        return collect($statuses)->map(function ($status, $index) use ($currentIndex) {
            return [
                'status' => $status,
                'label' => ucfirst(str_replace('_', ' ', $status)),
                'is_past' => $index < $currentIndex,
                'is_current' => $index === $currentIndex,
                'is_future' => $index > $currentIndex,
            ];
        })->all();
    }

    public function render()
    {
        return view('livewire.request-tracker')
            ->layout('layouts.public', ['title' => 'Track Your Request']);
    }
}
