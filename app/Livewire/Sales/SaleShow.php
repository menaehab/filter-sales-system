<?php

namespace App\Livewire\Sales;

use App\Actions\CustomerPayments\CreateCustomerPaymentAction;
use App\Models\Sale;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SaleShow extends Component
{
    public Sale $sale;

    public ?int $paySaleId = null;

    public ?int $editPaymentId = null;

    public ?int $deletePaymentId = null;

    public string $payAmount = '';

    public string $payMethod = 'cash';

    public string $payNote = '';

    public string $payCreatedAt = '';

    public ?int $payFromSaleId = null;

    public bool $printAfterPayment = false;

    public function mount(Sale $sale): void
    {
        $this->sale = $sale->load([
            'customer',
            'user',
            'items.product',
            'paymentAllocations.customerPayment',
        ]);
    }

    public function openPayModal(): void
    {
        $this->authorizePaySales();

        $sale = Sale::with('paymentAllocations')->findOrFail($this->sale->id);

        if ($sale->isFullyPaid()) {
            return;
        }

        $this->paySaleId = $sale->id;
        $this->payFromSaleId = null;

        $this->payAmount = (string) $sale->remaining_amount;

        $this->payMethod = 'cash';
        $this->payNote = '';
        $this->payCreatedAt = now()->format('Y/m/d H:i');
        $this->dispatch('open-modal-pay-sale');
    }

    public function submitPayment(CreateCustomerPaymentAction $action): void
    {
        $this->authorizePaySales();

        $request = new \App\Http\Requests\CustomerPayments\CreateCustomerPaymentRequest;

        $formData = [
            'sale_id' => $this->paySaleId,
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

        $sale = Sale::with('paymentAllocations')->findOrFail($this->sale->id);

        if ($sale->isFullyPaid()) {
            return;
        }

        $payment = $action->execute($sale->id, [
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'note' => $this->payNote ?: null,
            'created_at' => $validated['created_at'] ?? null,
        ]);

        if (! $payment) {
            return;
        }

        $printAfterPayment = $this->printAfterPayment;
        $paymentId = $payment->id;

        $this->resetPayForm();
        $this->sale = $sale->fresh()->load([
            'customer',
            'user',
            'items.product',
            'paymentAllocations.customerPayment',
        ]);
        $this->dispatch('close-modal-pay-sale');

        if ($printAfterPayment) {
            $this->redirect(route('customer-payments.print', $paymentId), navigate: true);
        }
    }

    public function resetPayForm(): void
    {
        $this->paySaleId = null;
        $this->payFromSaleId = null;
        $this->payAmount = '';
        $this->payMethod = 'cash';
        $this->payNote = '';
        $this->payCreatedAt = '';
        $this->printAfterPayment = false;
        $this->editPaymentId = null;
        $this->deletePaymentId = null;
    }

    public function openEditPaymentModal(int $paymentId): void
    {
        $this->authorizePaySales();

        $payment = \App\Models\CustomerPayment::findOrFail($paymentId);

        $this->editPaymentId = $payment->id;
        $this->payAmount = (string) $payment->amount;
        $this->payMethod = $payment->payment_method;
        $this->payNote = $payment->note ?? '';
        $this->payCreatedAt = $payment->created_at?->format('Y-m-d\TH:i') ?? '';

        $this->dispatch('open-modal-edit-payment');
    }

    public function submitEditPayment(\App\Actions\CustomerPayments\UpdateCustomerPaymentAction $action): void
    {
        $this->authorizePaySales();

        $payment = \App\Models\CustomerPayment::findOrFail($this->editPaymentId);

        $request = new \App\Http\Requests\CustomerPayments\UpdateCustomerPaymentRequest;
        
        $formData = [
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

        $action->execute($payment, [
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'note' => $this->payNote ?: null,
            'created_at' => $validated['created_at'] ?? null,
        ]);

        $this->resetPayForm();
        $this->sale = Sale::with(['customer', 'user', 'items.product', 'paymentAllocations.customerPayment'])->findOrFail($this->sale->id);
        $this->dispatch('close-modal-edit-payment');
    }

    public function openDeletePaymentModal(int $paymentId): void
    {
        $this->authorizePaySales();
        $this->deletePaymentId = $paymentId;
        $this->dispatch('open-modal-delete-payment');
    }

    public function submitDeletePayment(): void
    {
        $this->authorizePaySales();

        if ($this->deletePaymentId) {
            \App\Models\CustomerPayment::find($this->deletePaymentId)?->delete();
        }

        $this->resetPayForm();
        $this->sale = Sale::with(['customer', 'user', 'items.product', 'paymentAllocations.customerPayment'])->findOrFail($this->sale->id);
        $this->dispatch('close-modal-delete-payment');
    }

    public function getCanManageCreatedAtProperty(): bool
    {
        return (bool) auth()->user()?->can('manage_created_at');
    }

    protected function authorizePaySales(): void
    {
        abort_unless(auth()->user()?->canAny(['manage_sales', 'pay_sales']), 403);
    }

    public function render()
    {
        return view('livewire.sales.sale-show', [
            'sale' => $this->sale,
            'canManageCreatedAt' => $this->canManageCreatedAt,
        ]);
    }
}
