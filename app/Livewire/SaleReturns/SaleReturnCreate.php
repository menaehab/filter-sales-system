<?php

namespace App\Livewire\SaleReturns;

use App\Actions\SaleReturns\CreateSaleReturnAction;
use App\Models\Sale;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class SaleReturnCreate extends Component
{
    public string $sale_number = '';

    #[Locked]
    public ?int $saleId = null;

    public string $reason = '';

    public bool $cash_refund = false;

    public string $created_at = '';

    /** @var array<int, array{product_id: int, product_name: string, sell_price: string, available_quantity: float, return_quantity: string, selected: bool}> */
    public array $items = [];

    public function mount(): void
    {
        $this->created_at = now()->format('Y-m-d\TH:i');
    }

    public function updatedSaleNumber(): void
    {
        $this->resetSale();

        if (blank($this->sale_number)) {
            return;
        }

        $sale = Sale::with(['items.product', 'customer'])->where('number', $this->sale_number)->first();

        if (!$sale) {
            $this->addError('sale_number', __('keywords.sale_not_found'));
            return;
        }

        $this->saleId = $sale->id;
        $this->items = $sale->items->map(fn ($item) => [
            'sale_item_id' => $item->id,
            'product_id' => $item->product_id,
            'product_name' => $item->product?->name ?? __('keywords.not_specified'),
            'sell_price' => (string) $item->sell_price,
            'available_quantity' => (float) $item->quantity,
            'return_quantity' => '',
            'selected' => false,
        ])->toArray();
    }

    #[Computed]
    public function sale(): ?Sale
    {
        if (!$this->saleId) {
            return null;
        }

        return Sale::with(['items.product', 'customer'])->find($this->saleId);
    }

    #[Computed]
    public function totalReturnPrice(): float
    {
        return collect($this->items)
            ->filter(fn ($item) => $item['selected'])
            ->sum(fn ($item) => ((float) ($item['sell_price'] ?: 0)) * ((float) ($item['return_quantity'] ?: 0)));
    }

    #[Computed]
    public function selectedItemsCount(): int
    {
        return collect($this->items)->filter(fn ($item) => $item['selected'])->count();
    }

    public function save(CreateSaleReturnAction $action): void
    {
        if ($this->selectedItemsCount === 0) {
            $this->addError('items', __('keywords.select_at_least_one_item'));
            return;
        }

        $request = new \App\Http\Requests\SaleReturns\CreateSaleReturnRequest();

        $dataToValidate = [
            'sale_id' => $this->saleId,
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

        $action->execute($validated['sale_id'], [
            'items' => $this->items,
            'reason' => $validated['reason'] ?? '',
            'cash_refund' => $validated['cash_refund'] ?? false,
            'created_at' => $validated['created_at'] ?? null,
        ]);

        session()->flash('success', __('keywords.sale_return_created'));
        $this->redirect(route('sale-returns'), navigate: true);
    }

    private function resetSale(): void
    {
        $this->saleId = null;
        $this->items = [];
        $this->resetErrorBag('sale_number');
    }

    public function getCanManageCreatedAtProperty(): bool
    {
        return (bool) auth()->user()?->can('manage_created_at');
    }

    public function render()
    {
        return view('livewire.sale-returns.sale-return-create', [
            'canManageCreatedAt' => $this->canManageCreatedAt,
        ]);
    }
}
