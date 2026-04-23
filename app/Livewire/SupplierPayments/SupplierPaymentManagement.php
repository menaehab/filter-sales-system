<?php

namespace App\Livewire\SupplierPayments;

use App\Actions\SupplierPayments\DeleteSupplierPaymentAction;
use App\Actions\SupplierPayments\UpdateSupplierPaymentAction;
use App\Enums\PaymentMethodEnum;
use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\SupplierPayment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SupplierPaymentManagement extends Component
{
    use HasCrudModals, HasCrudQuery, HasForm, WithSearchAndPagination;

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public function mount(): void
    {
        $this->resetForm();
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

    protected function getModelClass(): string
    {
        return SupplierPayment::class;
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    protected function getSearchableFields(): array
    {
        return ['supplier.name', 'user.name', 'payment_method', 'note', 'allocations.purchase.number'];
    }

    protected function getWithRelations(): array
    {
        return ['supplier', 'user', 'allocations.purchase'];
    }

    #[Computed]
    public function supplierPayments(): LengthAwarePaginator
    {
        return $this->items;
    }

    #[Computed]
    public function paymentMethodOptions(): array
    {
        return collect(PaymentMethodEnum::supplierMethods())
            ->mapWithKeys(fn (PaymentMethodEnum $method) => [$method->value => $method->label()])
            ->toArray();
    }

    public function openEdit(int $id): void
    {
        $this->authorizeManageSupplierPayments();

        $payment = SupplierPayment::findOrFail($id);

        $this->openEditModal($payment->id, 'open-modal-edit-supplier-payment');

        $this->form = [
            'amount' => (string) $payment->amount,
            'payment_method' => $payment->payment_method,
            'note' => $payment->note ?? '',
            'created_at' => $payment->created_at?->format('Y/m/d H:i'),
        ];
    }

    public function updateSupplierPayment(UpdateSupplierPaymentAction $action): void
    {
        $this->authorizeManageSupplierPayments();

        if (blank($this->form['created_at'] ?? null)) {
            $this->form['created_at'] = null;
        }

        $request = new \App\Http\Requests\SupplierPayments\UpdateSupplierPaymentRequest;
        $rules = collect($request->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();
        $validated = $this->validate($rules, $request->messages(), $attributes);

        $payment = SupplierPayment::findOrFail($this->editId);
        $action->execute($payment, $validated['form']);

        $this->resetForm();
        $this->editId = null;

        $this->dispatch('close-modal-edit-supplier-payment');
        $this->resetPage();
    }

    public function setDelete(int $id): void
    {
        $this->authorizeManageSupplierPayments();
        $this->openDeleteModal($id, 'open-modal-delete-supplier-payment');
    }

    public function delete(DeleteSupplierPaymentAction $action): void
    {
        $this->authorizeManageSupplierPayments();

        $payment = SupplierPayment::find($this->deleteId);

        if ($payment) {
            $action->execute($payment);
        }

        $this->deleteId = null;
        $this->dispatch('close-modal-delete-supplier-payment');
        $this->resetPage();
    }

    public function getCanManageCreatedAtProperty(): bool
    {
        return (bool) auth()->user()?->can('manage_created_at');
    }

    public function render()
    {
        return view('livewire.supplier-payments.supplier-payment-management');
    }

    private function authorizeManageSupplierPayments(): void
    {
        abort_unless(auth()->user()?->can('manage_supplier_payment_allocations'), 403);
    }
}
