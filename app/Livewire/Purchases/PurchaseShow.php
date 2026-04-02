<?php

namespace App\Livewire\Purchases;

use App\Actions\SupplierPayments\CreateSupplierPaymentAction;
use App\Models\Purchase;
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

    public function submitPayment(CreateSupplierPaymentAction $action): void
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

        if ($purchase->isFullyPaid()) {
            return;
        }

        $payment = $action->execute($purchase->id, [
            'amount' => $this->payAmount,
            'payment_method' => $this->payMethod,
            'note' => $this->payNote ?: null,
        ]);

        if (! $payment) {
            return;
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
