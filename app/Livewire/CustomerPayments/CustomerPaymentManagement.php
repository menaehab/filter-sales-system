<?php

namespace App\Livewire\CustomerPayments;

use App\Actions\CustomerPayments\UpdateCustomerPaymentAction;
use App\Enums\PaymentMethodEnum;
use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\CustomerPayment;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'customer_payments'])]
class CustomerPaymentManagement extends Component
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
        return CustomerPayment::class;
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
        return ['customer.name', 'user.name', 'payment_method', 'note', 'allocations.sale.number'];
    }

    protected function getWithRelations(): array
    {
        return ['customer', 'user', 'allocations.sale'];
    }

    protected function applyAdditionalFilters($query): void
    {
        if (filled($this->dateFrom)) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if (filled($this->dateTo)) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }
    }

    #[Computed]
    public function customerPayments()
    {
        return $this->items;
    }

    #[Computed]
    public function paymentMethodOptions(): array
    {
        return collect(PaymentMethodEnum::customerMethods())
            ->mapWithKeys(fn (PaymentMethodEnum $method) => [$method->value => $method->label()])
            ->toArray();
    }

    public function openEdit(int $id): void
    {
        $this->authorizeManageCustomerPayments();

        $payment = CustomerPayment::findOrFail($id);

        $this->openEditModal($payment->id, 'open-modal-edit-customer-payment');

        $this->form = [
            'amount' => (string) $payment->amount,
            'payment_method' => $payment->payment_method,
            'note' => $payment->note ?? '',
            'created_at' => $payment->created_at?->format('Y/m/d H:i'),
        ];
    }

    public function updateCustomerPayment(UpdateCustomerPaymentAction $action): void
    {
        $this->authorizeManageCustomerPayments();

        if (blank($this->form['created_at'] ?? null)) {
            $this->form['created_at'] = null;
        }

        $request = new \App\Http\Requests\CustomerPayments\UpdateCustomerPaymentRequest;
        $rules = collect($request->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();
        $validated = $this->validate($rules, $request->messages(), $attributes);

        $payment = CustomerPayment::findOrFail($this->editId);
        $action->execute($payment, $validated['form']);

        $this->resetForm();
        $this->editId = null;

        $this->dispatch('close-modal-edit-customer-payment');
        $this->resetPage();
    }

    public function setDelete($id): void
    {
        $this->authorizeManageCustomerPayments();
        $this->openDeleteModal($id, 'open-modal-delete-customer-payment');
    }

    public function delete(): void
    {
        $this->authorizeManageCustomerPayments();

        CustomerPayment::find($this->deleteId)?->delete();

        $this->deleteId = null;
        $this->dispatch('close-modal-delete-customer-payment');
        $this->resetPage();
    }

    public function getCanManageCreatedAtProperty(): bool
    {
        return (bool) auth()->user()?->can('manage_created_at');
    }

    public function render()
    {
        return view('livewire.customer-payments.customer-payment-management');
    }

    private function authorizeManageCustomerPayments(): void
    {
        abort_unless(auth()->user()?->can('manage_customer_payment_allocations'), 403);
    }
}
