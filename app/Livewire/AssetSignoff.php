<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Asset;
use App\Models\AssetSignoff as AssetSignoffModel;
use App\Services\Signoff\AssetSignoffService;
use Livewire\Component;

/**
 * Manages the electronic sign-off workflow for a single asset.
 *
 * This component drives the UI that a commissioning provider or owner's rep
 * uses to request, approve, and reject closeout sign-offs — the workflow
 * directly parallels the industry-standard "Asset Closeout Signoff" feature.
 */
class AssetSignoff extends Component
{
    public Asset $asset;

    public string $signerRole = AssetSignoffModel::ROLE_COMMISSIONING_AUTHORITY;

    public string $requestNotes = '';

    public int $expiresInDays = 14;

    public ?int $activeSignoffId = null;

    public string $approvalNotes = '';

    public string $rejectionReason = '';

    public string $signatureImage = '';

    public bool $showRequestForm = false;

    protected function rules(): array
    {
        return [
            'signerRole' => ['required', 'string'],
            'requestNotes' => ['nullable', 'string', 'max:2000'],
            'expiresInDays' => ['nullable', 'integer', 'min:1', 'max:90'],
        ];
    }

    public function mount(int $assetId): void
    {
        $this->asset = Asset::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($assetId);
    }

    public function getSignoffsProperty()
    {
        return AssetSignoffModel::where('asset_id', $this->asset->id)
            ->with(['requester:id,name,email', 'signer:id,name,email'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function getSignerRolesProperty(): array
    {
        return AssetSignoffModel::signerRoles();
    }

    public function requestSignoff(AssetSignoffService $service): void
    {
        $this->validate();

        try {
            $service->request(
                asset: $this->asset,
                requester: auth()->user(),
                signerRole: $this->signerRole,
                notes: $this->requestNotes ?: null,
                expiresInDays: $this->expiresInDays,
            );

            $this->reset(['requestNotes', 'showRequestForm']);
            $this->signerRole = AssetSignoffModel::ROLE_COMMISSIONING_AUTHORITY;
            $this->expiresInDays = 14;

            $this->dispatch('toast', type: 'success', message: 'Sign-off requested.');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function approve(int $signoffId, AssetSignoffService $service): void
    {
        $signoff = AssetSignoffModel::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($signoffId);

        try {
            $service->approve(
                signoff: $signoff,
                signer: auth()->user(),
                signatureImage: $this->signatureImage ?: null,
                notes: $this->approvalNotes ?: null,
                request: request(),
            );

            $this->reset(['approvalNotes', 'signatureImage', 'activeSignoffId']);
            $this->dispatch('toast', type: 'success', message: 'Sign-off approved.');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function reject(int $signoffId, AssetSignoffService $service): void
    {
        $this->validate([
            'rejectionReason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $signoff = AssetSignoffModel::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($signoffId);

        try {
            $service->reject(
                signoff: $signoff,
                signer: auth()->user(),
                reason: $this->rejectionReason,
            );

            $this->reset(['rejectionReason', 'activeSignoffId']);
            $this->dispatch('toast', type: 'success', message: 'Sign-off rejected.');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function withdraw(int $signoffId, AssetSignoffService $service): void
    {
        $signoff = AssetSignoffModel::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($signoffId);

        try {
            $service->withdraw($signoff, auth()->user());
            $this->dispatch('toast', type: 'success', message: 'Sign-off withdrawn.');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.asset-signoff');
    }
}
