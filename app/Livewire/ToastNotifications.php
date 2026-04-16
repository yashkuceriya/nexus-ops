<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class ToastNotifications extends Component
{
    /** @var array<int, array{id: string, type: string, message: string}> */
    public array $toasts = [];

    #[On('toast')]
    public function addToast(string $type = 'info', string $message = ''): void
    {
        $this->toasts[] = [
            'id' => uniqid('toast_'),
            'type' => $type,
            'message' => $message,
        ];
    }

    public function removeToast(string $id): void
    {
        $this->toasts = array_values(
            array_filter($this->toasts, fn (array $toast) => $toast['id'] !== $id)
        );
    }

    public function render()
    {
        return view('livewire.toast-notifications');
    }
}
