<?php

namespace App\Livewire\PurchaseReturns;

use App\Actions\PurchaseReturns\CreatePurchaseReturnAction;
use App\Models\Purchase;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class PurchaseReturnCreate extends Component
{
    public string $purchase_number = '';

    #[Locked]
    public ?int $purchaseId = null;

    public string $reason = '';

    public bool $cash_refund = false;

    public string $created_at = '';

    /** @var array<int, array{product_id: int, product_name: string, cost_price: string, available_quantity: float, return_quantity: string, selected: bool}> */
    public array $items = [];

    public function mount(): void
    {
        $this->created_at = now()->format('Y-m-d\TH:i');
    }

    public function updatedPurchaseNumber(): void
    {
        $this->resetPurchase();

        if (blank($this->purchase_number)) {
            return;
        }

        $purchase = Purchase::with('items.product')->where('number', $this->purchase_number)->first();

        if (! $purchase) {
            $this->addError('purchase_number', __('keywords.purchase_not_found'));

            return;
        }

        $this->purchaseId = $purchase->id;
        $this->items = $purchase->items->map(fn ($item) => [
            'purchase_item_id' => $item->id,
            'product_id' => $item->product_id,
            'product_name' => $item->product_name,
            'cost_price' => (string) $item->cost_price,
            'available_quantity' => (float) $item->quantity,
            'return_quantity' => '',
            'selected' => false,
        ])->toArray();
    }

    #[Computed]
    public function purchase(): ?Purchase
    {
        if (! $this->purchaseId) {
            return null;
        }

        return Purchase::with('items.product')->find($this->purchaseId);
    }

    #[Computed]
    public function totalReturnPrice(): float
    {
        return collect($this->items)
            ->filter(fn ($item) => $item['selected'])
            ->sum(fn ($item) => ((float) ($item['cost_price'] ?: 0)) * ((float) ($item['return_quantity'] ?: 0)));
    }

    #[Computed]
    public function selectedItemsCount(): int
    {
        return collect($this->items)->filter(fn ($item) => $item['selected'])->count();
    }

    public function save(CreatePurchaseReturnAction $action): void
    {
        if ($this->selectedItemsCount === 0) {
            $this->addError('items', __('keywords.select_at_least_one_item'));

            return;
        }

        $request = new \App\Http\Requests\PurchaseReturns\CreatePurchaseReturnRequest;

        $dataToValidate = [
            'purchase_id' => $this->purchaseId,
            'items' => $this->items,
            'reason' => $this->reason,
            'cash_refund' => $this->cash_refund,
            'created_at' => $this->created_at,
        ];

        $validator = \Illuminate\Support\Facades\Validator::make(
            $dataToValidate,
            $request->rules(),
            $request->messages(),
            $request->attributes()
        );

        if ($validator->fails()) {
            $this->setErrorBag($validator->getMessageBag());
            return;
        }

        $validated = $validator->validated();

        $action->execute($validated['purchase_id'], [
            'items' => $this->items,
            'reason' => $validated['reason'] ?? '',
            'cash_refund' => $validated['cash_refund'] ?? false,
            'created_at' => $validated['created_at'] ?? null,
        ]);

        session()->flash('success', __('keywords.purchase_return_created'));
        $this->redirect(route('purchase-returns'), navigate: true);
    }

    private function resetPurchase(): void
    {
        $this->purchaseId = null;
        $this->items = [];
        $this->resetErrorBag('purchase_number');
    }

    public function getCanManageCreatedAtProperty(): bool
    {
        return (bool) auth()->user()?->can('manage_created_at');
    }

    public function render()
    {
        return view('livewire.purchase-returns.purchase-return-create', [
            'canManageCreatedAt' => $this->canManageCreatedAt,
        ]);
    }
}
