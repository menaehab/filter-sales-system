<?php

namespace App\Livewire\Purchases;

use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Purchase;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentAllocation;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PurchaseManagement extends Component
{
    use HasCrudModals, HasCrudQuery, HasForm, WithSearchAndPagination;

    public string $filterPaymentType = '';

    public string $filterStatus = '';

    // Payment modal
    public ?int $payPurchaseId = null;

    public string $payAmount = '';

    public string $payMethod = 'cash';

    public string $payNote = '';

    public ?int $payFromPurchaseId = null;

    public bool $printAfterPayment = false;

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
        return Purchase::class;
    }

    protected function getSearchableFields(): array
    {
        return ['supplier_name', 'user_name', 'number'];
    }

    protected function getWithRelations(): array
    {
        return ['items', 'paymentAllocations', 'supplier'];
    }

    protected function applyAdditionalFilters(Builder $query): void
    {
        if (filled($this->filterPaymentType)) {
            $query->where('payment_type', $this->filterPaymentType);
        }

        if ($this->filterStatus === 'paid') {
            $query->whereRaw('total_price <= COALESCE((SELECT SUM(amount) FROM supplier_payment_allocations WHERE supplier_payment_allocations.purchase_id = purchases.id), 0)');
        } elseif ($this->filterStatus === 'partial') {
            $query->where('installment_months', '>', 0)
                ->whereRaw('COALESCE((SELECT SUM(amount) FROM supplier_payment_allocations WHERE supplier_payment_allocations.purchase_id = purchases.id), 0) > 0')
                ->whereRaw('total_price > COALESCE((SELECT SUM(amount) FROM supplier_payment_allocations WHERE supplier_payment_allocations.purchase_id = purchases.id), 0)');
        } elseif ($this->filterStatus === 'unpaid') {
            $query->where(function ($q) {
                $q->where('installment_months', '>', 0);
            })->whereRaw('COALESCE((SELECT SUM(amount) FROM supplier_payment_allocations WHERE supplier_payment_allocations.purchase_id = purchases.id), 0) = 0');
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

    public function getPurchasesProperty()
    {
        return $this->items;
    }

    // Payment handling
    public function openPayModal(int $id)
    {
        $this->authorizePayPurchases();

        $purchase = Purchase::with('paymentAllocations')->findOrFail($id);

        $this->payPurchaseId = $purchase->id;
        $this->payFromPurchaseId = null;

        if ($purchase->isInstallment() && $purchase->supplier_id) {
            $oldestUnpaid = $this->getSupplierInstallmentQueue($purchase->supplier_id)->first();

            if ($oldestUnpaid) {
                $this->payFromPurchaseId = $oldestUnpaid->id;
                $defaultInstallment = (float) ($oldestUnpaid->installment_amount ?: $oldestUnpaid->remaining_amount);
                $this->payAmount = (string) min($defaultInstallment, $oldestUnpaid->remaining_amount);
            } else {
                $this->payAmount = (string) $purchase->remaining_amount;
            }
        } else {
            $this->payAmount = (string) $purchase->remaining_amount;
        }

        $this->payMethod = 'cash';
        $this->payNote = '';
        $this->dispatch('open-modal-pay-purchase');
    }

    public function submitPayment()
    {
        $this->authorizePayPurchases();

        $this->validate([
            'payAmount' => 'required|numeric|min:0.01',
            'payMethod' => 'required|string',
        ], [], [
            'payAmount' => __('keywords.amount'),
            'payMethod' => __('keywords.payment_method'),
        ]);

        $purchase = Purchase::with('paymentAllocations')->findOrFail($this->payPurchaseId);

        $amount = (float) $this->payAmount;

        if ($amount <= 0) {
            return;
        }

        $allocations = [];

        if ($purchase->isInstallment() && $purchase->supplier_id) {
            $queue = $this->getSupplierInstallmentQueue($purchase->supplier_id);
            $maxPayable = $queue->sum(fn (Purchase $item) => $item->remaining_amount);
            $remainingToAllocate = min($amount, $maxPayable);

            foreach ($queue as $queuedPurchase) {
                if ($remainingToAllocate <= 0) {
                    break;
                }

                $payable = min($queuedPurchase->remaining_amount, $remainingToAllocate);

                if ($payable > 0) {
                    $allocations[] = [
                        'purchase_id' => $queuedPurchase->id,
                        'amount' => $payable,
                    ];
                    $remainingToAllocate -= $payable;
                }
            }
        } else {
            $maxPayable = $purchase->remaining_amount;
            $amount = min($amount, $maxPayable);

            if ($amount > 0) {
                $allocations[] = [
                    'purchase_id' => $purchase->id,
                    'amount' => $amount,
                ];
            }
        }

        $totalAllocated = collect($allocations)->sum('amount');

        if ($totalAllocated <= 0) {
            return;
        }

        $payment = SupplierPayment::create([
            'amount' => $totalAllocated,
            'payment_method' => $this->payMethod,
            'note' => $this->payNote ?: null,
            'supplier_id' => $purchase->supplier_id,
            'user_id' => auth()->id(),
        ]);

        foreach ($allocations as $allocation) {
            SupplierPaymentAllocation::create([
                'amount' => $allocation['amount'],
                'supplier_payment_id' => $payment->id,
                'purchase_id' => $allocation['purchase_id'],
            ]);
        }

        $printAfterPayment = $this->printAfterPayment;
        $paymentId = $payment->id;

        $this->resetPayForm();
        $this->dispatch('close-modal-pay-purchase');

        if ($printAfterPayment) {
            $this->redirect(route('supplier-payments.print', $paymentId), navigate: true);
        }
    }

    protected function getSupplierInstallmentQueue(int $supplierId)
    {
        return Purchase::with('paymentAllocations')
            ->where('supplier_id', $supplierId)
            ->where('installment_months', '>', 0)
            ->orderBy('created_at')
            ->get()
            ->filter(fn (Purchase $item) => $item->remaining_amount > 0)
            ->values();
    }

    public function resetPayForm()
    {
        $this->payPurchaseId = null;
        $this->payFromPurchaseId = null;
        $this->payAmount = '';
        $this->payMethod = 'cash';
        $this->payNote = '';
        $this->printAfterPayment = false;
    }

    protected function authorizeManagePurchases(): void
    {
        abort_unless(auth()->user()?->can('manage_purchases'), 403);
    }

    protected function authorizePayPurchases(): void
    {
        abort_unless(auth()->user()?->canAny(['manage_purchases', 'pay_purchases']), 403);
    }

    // Delete
    public function setDelete($id)
    {
        $this->authorizeManagePurchases();
        $this->openDeleteModal($id, 'open-modal-delete-purchase');
    }

    public function delete()
    {
        $this->authorizeManagePurchases();

        $purchase = Purchase::with('paymentAllocations')->findOrFail($this->deleteId);
        $relatedPaymentIds = $purchase->paymentAllocations->pluck('supplier_payment_id')->unique()->all();

        $purchase->delete();

        if (! empty($relatedPaymentIds)) {
            SupplierPayment::whereIn('id', $relatedPaymentIds)
                ->doesntHave('allocations')
                ->delete();
        }

        $this->deleteId = null;
        $this->dispatch('close-modal-delete-purchase');
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.purchases.purchase-management');
    }
}
