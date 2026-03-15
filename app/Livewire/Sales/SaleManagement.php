<?php

namespace App\Livewire\Sales;

use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\CustomerPayment;
use App\Models\CustomerPaymentAllocation;
use App\Models\ProductMovement;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SaleManagement extends Component
{
    use WithSearchAndPagination, HasForm, HasCrudModals, HasCrudQuery;

    public string $filterPaymentType = '';
    public string $filterStatus = '';

    public ?int $paySaleId = null;
    public string $payAmount = '';
    public string $payMethod = 'cash';
    public string $payNote = '';
    public ?int $payFromSaleId = null;

    public function mount()
    {
        $this->resetForm();
    }

    protected function rules()
    {
        return [];
    }

    protected function getDefaultForm(): array
    {
        return [];
    }

    protected function getModelClass(): string
    {
        return Sale::class;
    }

    protected function getSearchableFields(): array
    {
        return ['dealer_name', 'user_name', 'number', 'customer.name'];
    }

    protected function getWithRelations(): array
    {
        return ['items', 'paymentAllocations', 'customer'];
    }

    protected function applyAdditionalFilters(Builder $query): void
    {
        if (filled($this->filterPaymentType)) {
            $query->where('payment_type', $this->filterPaymentType);
        }

        if ($this->filterStatus === 'paid') {
            $query->whereRaw('total_price <= COALESCE((SELECT SUM(amount) FROM customer_payment_allocations WHERE customer_payment_allocations.sale_id = sales.id), 0)');
        } elseif ($this->filterStatus === 'partial') {
            $query->where('installment_months', '>', 0)
                ->whereRaw('COALESCE((SELECT SUM(amount) FROM customer_payment_allocations WHERE customer_payment_allocations.sale_id = sales.id), 0) > 0')
                ->whereRaw('total_price > COALESCE((SELECT SUM(amount) FROM customer_payment_allocations WHERE customer_payment_allocations.sale_id = sales.id), 0)');
        } elseif ($this->filterStatus === 'unpaid') {
            $query->where(function ($q) {
                $q->where('installment_months', '>', 0);
            })->whereRaw('COALESCE((SELECT SUM(amount) FROM customer_payment_allocations WHERE customer_payment_allocations.sale_id = sales.id), 0) = 0');
        }
    }

    public function queryString(): array
    {
        return [
            'search' => ['except' => ''],
            'filterPaymentType' => ['except' => '', 'as' => 'payment_type'],
            'filterStatus' => ['except' => '', 'as' => 'status'],
        ];
    }

    public function getSalesProperty()
    {
        return $this->items;
    }

    public function openPayModal(int $id): void
    {
        $sale = Sale::with('paymentAllocations')->findOrFail($id);

        $this->paySaleId = $sale->id;
        $this->payFromSaleId = null;

        if ($sale->isInstallment() && $sale->customer_id) {
            $oldestUnpaid = $this->getCustomerInstallmentQueue($sale->customer_id)->first();

            if ($oldestUnpaid) {
                $this->payFromSaleId = $oldestUnpaid->id;
                $defaultInstallment = (float) ($oldestUnpaid->installment_amount ?: $oldestUnpaid->remaining_amount);
                $this->payAmount = (string) min($defaultInstallment, $oldestUnpaid->remaining_amount);
            } else {
                $this->payAmount = (string) $sale->remaining_amount;
            }
        } else {
            $this->payAmount = (string) $sale->remaining_amount;
        }

        $this->payMethod = 'cash';
        $this->payNote = '';
        $this->dispatch('open-modal-pay-sale');
    }

    public function submitPayment(): void
    {
        $this->validate([
            'payAmount' => 'required|numeric|min:0.01',
            'payMethod' => 'required|string',
        ], [], [
            'payAmount' => __('keywords.amount'),
            'payMethod' => __('keywords.payment_method'),
        ]);

        $sale = Sale::with('paymentAllocations')->findOrFail($this->paySaleId);
        $amount = (float) $this->payAmount;

        if ($amount <= 0) {
            return;
        }

        $allocations = [];

        if ($sale->isInstallment() && $sale->customer_id) {
            $queue = $this->getCustomerInstallmentQueue($sale->customer_id);
            $maxPayable = $queue->sum(fn (Sale $item) => $item->remaining_amount);
            $remainingToAllocate = min($amount, $maxPayable);

            foreach ($queue as $queuedSale) {
                if ($remainingToAllocate <= 0) {
                    break;
                }

                $payable = min($queuedSale->remaining_amount, $remainingToAllocate);

                if ($payable > 0) {
                    $allocations[] = [
                        'sale_id' => $queuedSale->id,
                        'amount' => $payable,
                    ];
                    $remainingToAllocate -= $payable;
                }
            }
        } else {
            $maxPayable = $sale->remaining_amount;
            $amount = min($amount, $maxPayable);

            if ($amount > 0) {
                $allocations[] = [
                    'sale_id' => $sale->id,
                    'amount' => $amount,
                ];
            }
        }

        $totalAllocated = collect($allocations)->sum('amount');

        if ($totalAllocated <= 0) {
            return;
        }

        $payment = CustomerPayment::create([
            'amount' => $totalAllocated,
            'payment_method' => $this->payMethod,
            'note' => $this->payNote ?: null,
            'customer_id' => $sale->customer_id,
        ]);

        foreach ($allocations as $allocation) {
            CustomerPaymentAllocation::create([
                'amount' => $allocation['amount'],
                'customer_payment_id' => $payment->id,
                'sale_id' => $allocation['sale_id'],
            ]);
        }

        $this->resetPayForm();
        $this->dispatch('close-modal-pay-sale');
    }

    protected function getCustomerInstallmentQueue(int $customerId)
    {
        return Sale::with('paymentAllocations')
            ->where('customer_id', $customerId)
            ->where('installment_months', '>', 0)
            ->orderBy('created_at')
            ->get()
            ->filter(fn (Sale $item) => $item->remaining_amount > 0)
            ->values();
    }

    public function resetPayForm(): void
    {
        $this->paySaleId = null;
        $this->payFromSaleId = null;
        $this->payAmount = '';
        $this->payMethod = 'cash';
        $this->payNote = '';
    }

    public function setDelete($id): void
    {
        $this->openDeleteModal($id, 'open-modal-delete-sale');
    }

    public function delete(): void
    {
        $sale = Sale::with('items', 'paymentAllocations')->findOrFail($this->deleteId);
        $relatedPaymentIds = $sale->paymentAllocations->pluck('customer_payment_id')->unique()->all();

        foreach ($sale->items as $item) {
            $item->product?->increment('quantity', $item->quantity);
        }

        ProductMovement::where('movable_type', Sale::class)
            ->where('movable_id', $sale->id)
            ->delete();

        $sale->delete();

        if (! empty($relatedPaymentIds)) {
            CustomerPayment::whereIn('id', $relatedPaymentIds)
                ->doesntHave('allocations')
                ->delete();
        }

        $this->deleteId = null;
        $this->dispatch('close-modal-delete-sale');
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.sales.sale-management');
    }
}
