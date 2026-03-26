<?php

namespace App\Livewire\Purchases;

use App\Models\Purchase;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentAllocation;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PurchaseShow extends Component
{
    public Purchase $purchase;

    public ?int $payPurchaseId = null;

    public string $payAmount = '';

    public string $payMethod = 'cash';

    public string $payNote = '';

    public ?int $payFromPurchaseId = null;

    public bool $printAfterPayment = false;

    public function mount(Purchase $purchase): void
    {
        $this->purchase = $purchase->load([
            'supplier',
            'user',
            'items.product',
            'paymentAllocations.supplierPayment',
        ]);
    }

    public function openPayModal(): void
    {
        $this->authorizePayPurchases();

        $purchase = Purchase::with('paymentAllocations')->findOrFail($this->purchase->id);

        if ($purchase->isFullyPaid()) {
            return;
        }

        $this->payPurchaseId = $purchase->id;
        $this->payFromPurchaseId = null;

        $this->payAmount = (string) $purchase->remaining_amount;

        $this->payMethod = 'cash';
        $this->payNote = '';
        $this->dispatch('open-modal-pay-purchase');
    }

    public function submitPayment(): void
    {
        $this->authorizePayPurchases();

        $this->validate([
            'payAmount' => 'required|numeric|min:0.01',
            'payMethod' => 'required|string',
        ], [], [
            'payAmount' => __('keywords.amount'),
            'payMethod' => __('keywords.payment_method'),
        ]);

        $purchase = Purchase::with('paymentAllocations')->findOrFail($this->purchase->id);

        $amount = (float) $this->payAmount;

        if ($amount <= 0 || $purchase->isFullyPaid()) {
            return;
        }

        $allocations = [];

        $maxPayable = $purchase->remaining_amount;
        $amount = min($amount, $maxPayable);

        if ($amount > 0) {
            $allocations[] = [
                'purchase_id' => $purchase->id,
                'amount' => $amount,
            ];
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
        $this->purchase = $purchase->fresh()->load([
            'supplier',
            'user',
            'items.product',
            'paymentAllocations.supplierPayment',
        ]);
        $this->dispatch('close-modal-pay-purchase');

        if ($printAfterPayment) {
            $this->redirect(route('supplier-payments.print', $paymentId), navigate: true);
        }
    }

    public function resetPayForm(): void
    {
        $this->payPurchaseId = null;
        $this->payFromPurchaseId = null;
        $this->payAmount = '';
        $this->payMethod = 'cash';
        $this->payNote = '';
        $this->printAfterPayment = false;
    }

    protected function authorizePayPurchases(): void
    {
        abort_unless(auth()->user()?->canAny(['manage_purchases', 'pay_purchases']), 403);
    }

    public function render()
    {
        return view('livewire.purchases.purchase-show', [
            'purchase' => $this->purchase,
        ]);
    }
}
