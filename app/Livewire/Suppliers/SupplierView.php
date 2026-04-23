<?php

namespace App\Livewire\Suppliers;

use App\Actions\SupplierPayments\UpdateSupplierPaymentAction;
use App\Enums\PaymentMethodEnum;
use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SupplierView extends Component
{
    use HasCrudModals, HasForm, WithSearchAndPagination;

    public Supplier $supplier;

    public string $activeTab = 'purchases';

    public function mount(Supplier $supplier): void
    {
        $this->supplier = $supplier;
        $this->resetForm();
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    protected function getDefaultForm(): array
    {
        return [
            'amount' => null,
            'payment_method' => PaymentMethodEnum::CASH->value,
            'note' => '',
            'created_at' => null,
        ];
    }

    public function getPaymentMethodOptionsProperty(): array
    {
        return collect(PaymentMethodEnum::supplierMethods())
            ->mapWithKeys(fn (PaymentMethodEnum $method) => [$method->value => $method->label()])
            ->toArray();
    }

    public function openEditPayment(int $id): void
    {
        $this->authorizeManageSupplierPayments();

        $payment = $this->supplier->payments()->findOrFail($id);

        $this->openEditModal($payment->id, 'open-modal-edit-supplier-view-payment');

        $this->form = [
            'amount' => (string) $payment->amount,
            'payment_method' => $payment->payment_method,
            'note' => $payment->note ?? '',
            'created_at' => $payment->created_at?->format('Y/m/d H:i'),
        ];
    }

    public function updatePayment(UpdateSupplierPaymentAction $action): void
    {
        $this->authorizeManageSupplierPayments();

        if (blank($this->form['created_at'] ?? null)) {
            $this->form['created_at'] = null;
        }

        $request = new \App\Http\Requests\SupplierPayments\UpdateSupplierPaymentRequest;
        $rules = collect($request->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();
        $validated = $this->validate($rules, $request->messages(), $attributes);

        $payment = $this->supplier->payments()->findOrFail($this->editId);

        $action->execute($payment, $validated['form']);

        $this->resetForm();
        $this->editId = null;

        $this->dispatch('close-modal-edit-supplier-view-payment');
    }

    public function setDeletePayment(int $id): void
    {
        $this->authorizeManageSupplierPayments();

        $this->openDeleteModal($id, 'open-modal-delete-supplier-view-payment');
    }

    public function deletePayment(): void
    {
        $this->authorizeManageSupplierPayments();

        $payment = $this->supplier->payments()->find($this->deleteId);

        if ($payment) {
            $payment->delete();
        }

        $this->deleteId = null;

        $this->dispatch('close-modal-delete-supplier-view-payment');
        $this->resetPage();
    }

    public function getCanManageCreatedAtProperty(): bool
    {
        return (bool) auth()->user()?->can('manage_created_at');
    }

    private function authorizeManageSupplierPayments(): void
    {
        abort_unless(auth()->user()?->can('manage_supplier_payment_allocations'), 403);
    }

    public function getPurchasesProperty()
    {
        return $this->supplier->purchases()
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function getPaymentsProperty()
    {
        return $this->supplier->payments()
            ->with(['user', 'allocations.purchase'])
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function getReturnsProperty()
    {
        // Get purchase returns for this supplier's purchases where cash_refund = false
        return PurchaseReturn::whereIn('purchase_id', $this->supplier->purchases()->pluck('id'))
            ->where('cash_refund', false)
            ->with('user', 'purchase')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.suppliers.supplier-view', [
            'purchases' => $this->purchases,
            'payments' => $this->payments,
            'returns' => $this->returns,
        ]);
    }
}
