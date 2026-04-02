<?php

namespace App\Livewire\Purchases;

use App\Actions\SupplierPayments\CreateSupplierPaymentAction;
use App\Actions\Purchases\DeletePurchaseAction;
use App\Enums\PaymentMethodEnum;
use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class PurchaseManagement extends Component
{
    use HasCrudModals, HasCrudQuery, HasForm, WithSearchAndPagination;

    public string $filterPaymentType = '';

    public string $filterStatus = '';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    #[Locked]
    public ?int $payPurchaseId = null;

    public string $payAmount = '';

    public string $payMethod = 'cash';

    public string $payNote = '';

    public string $payCreatedAt = '';

    public ?int $payFromPurchaseId = null;

    public bool $printAfterPayment = false;

    public function mount(): void
    {
        $this->resetForm();
    }

    protected function rules(): array
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

        if (filled($this->dateFrom)) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if (filled($this->dateTo)) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }
    }

    public function queryString(): array
    {
        return [
            'search' => ['except' => ''],
            'filterPaymentType' => ['except' => '', 'as' => 'payment_type'],
            'filterStatus' => ['except' => '', 'as' => 'status'],
            'dateFrom' => ['except' => '', 'as' => 'from'],
            'dateTo' => ['except' => '', 'as' => 'to'],
        ];
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function purchases()
    {
        return $this->items;
    }

    #[Computed]
    public function paymentMethods(): array
    {
        return PaymentMethodEnum::values();
    }

    public function openPayModal(int $id): void
    {
        $this->authorizePayPurchases();

        $purchase = Purchase::with('paymentAllocations')->findOrFail($id);

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

        $request = new \App\Http\Requests\SupplierPayments\CreateSupplierPaymentRequest();

        // Create form data array for validation
        $formData = [
            'purchase_id' => $this->payPurchaseId,
            'amount' => $this->payAmount,
            'payment_method' => $this->payMethod,
            'note' => $this->payNote,
            'created_at' => $this->payCreatedAt,
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($formData, $request->rules(), $request->messages(), $request->attributes());

        $validated = $validator->validate();

        $payment = $action->execute($validated['purchase_id'], [
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'note' => $validated['note'] ?? null,
            'created_at' => $validated['created_at'] ?? null,
        ]);

        $printAfterPayment = $this->printAfterPayment;
        $paymentId = $payment?->id;

        $this->resetPayForm();
        $this->dispatch('close-modal-pay-purchase');

        if ($printAfterPayment && $paymentId) {
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

    protected function authorizeManagePurchases(): void
    {
        abort_unless(auth()->user()?->can('manage_purchases'), 403);
    }

    protected function authorizePayPurchases(): void
    {
        abort_unless(auth()->user()?->canAny(['manage_purchases', 'pay_purchases']), 403);
    }

    public function setDelete($id): void
    {
        $this->authorizeManagePurchases();
        $this->openDeleteModal($id, 'open-modal-delete-purchase');
    }

    public function delete(DeletePurchaseAction $action): void
    {
        $this->authorizeManagePurchases();

        $purchase = Purchase::with('paymentAllocations')->findOrFail($this->deleteId);
        $action->execute($purchase);

        $this->deleteId = null;
        $this->dispatch('close-modal-delete-purchase');
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.purchases.purchase-management', [
            'canManageCreatedAt' => $this->canManageCreatedAt,
        ]);
    }
}
