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

    public string $payCreatedAt = '';

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
        $this->payCreatedAt = now()->format('Y-m-d\TH:i');
        $this->dispatch('open-modal-pay-purchase');
    }

    public function submitPayment(CreateSupplierPaymentAction $action): void
    {
        $this->authorizePayPurchases();

        $request = new \App\Http\Requests\SupplierPayments\CreateSupplierPaymentRequest;

        $formData = [
            'purchase_id' => $this->payPurchaseId,
            'amount' => $this->payAmount,
            'payment_method' => $this->payMethod,
            'note' => $this->payNote,
            'created_at' => $this->payCreatedAt,
        ];

        $validator = \Illuminate\Support\Facades\Validator::make(
            $formData,
            $request->rules(),
            $request->messages(),
            $request->attributes()
        );

        $validated = $validator->validate();

        $purchase = Purchase::with('paymentAllocations')->findOrFail($this->purchase->id);

        if ($purchase->isFullyPaid()) {
            return;
        }

        $payment = $action->execute($purchase->id, [
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'note' => $validated['note'] ?? null,
            'created_at' => $validated['created_at'] ?? null,
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
        $this->payCreatedAt = '';
        $this->printAfterPayment = false;
    }

    public function getCanManageCreatedAtProperty(): bool
    {
        return (bool) auth()->user()?->can('manage_created_at');
    }

    protected function authorizePayPurchases(): void
    {
        abort_unless(auth()->user()?->canAny(['manage_purchases', 'pay_purchases']), 403);
    }

    public function render()
    {
        return view('livewire.purchases.purchase-show', [
            'purchase' => $this->purchase,
            'canManageCreatedAt' => $this->canManageCreatedAt,
        ]);
    }
}
