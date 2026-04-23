<?php

namespace App\Livewire\Customers;

use App\Actions\CustomerPayments\UpdateCustomerPaymentAction;
use App\Enums\PaymentMethodEnum;
use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Customer;
use App\Models\SaleReturn;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CustomerView extends Component
{
    use HasCrudModals, HasForm, WithSearchAndPagination;

    public Customer $customer;

    public string $activeTab = 'sales';

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;
        $this->resetForm();

        if (auth()->user()->can('view_only_customers_in_his_places')) {
            if (! auth()->user()->places->pluck('id')->contains($customer->place_id)) {
                abort(403);
            }
        }
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
        return collect(PaymentMethodEnum::customerMethods())
            ->mapWithKeys(fn (PaymentMethodEnum $method) => [$method->value => $method->label()])
            ->toArray();
    }

    public function openEditPayment(int $id): void
    {
        $this->authorizeManageCustomerPayments();

        $payment = $this->customer->payments()->findOrFail($id);

        $this->openEditModal($payment->id, 'open-modal-edit-customer-view-payment');

        $this->form = [
            'amount' => (string) $payment->amount,
            'payment_method' => $payment->payment_method,
            'note' => $payment->note ?? '',
            'created_at' => $payment->created_at?->format('Y/m/d H:i'),
        ];
    }

    public function updatePayment(UpdateCustomerPaymentAction $action): void
    {
        $this->authorizeManageCustomerPayments();

        if (blank($this->form['created_at'] ?? null)) {
            $this->form['created_at'] = null;
        }

        $request = new \App\Http\Requests\CustomerPayments\UpdateCustomerPaymentRequest;
        $rules = collect($request->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();
        $validated = $this->validate($rules, $request->messages(), $attributes);

        $payment = $this->customer->payments()->findOrFail($this->editId);

        $action->execute($payment, $validated['form']);

        $this->resetForm();
        $this->editId = null;

        $this->dispatch('close-modal-edit-customer-view-payment');
    }

    public function setDeletePayment(int $id): void
    {
        $this->authorizeManageCustomerPayments();

        $this->openDeleteModal($id, 'open-modal-delete-customer-view-payment');
    }

    public function deletePayment(): void
    {
        $this->authorizeManageCustomerPayments();

        $payment = $this->customer->payments()->find($this->deleteId);

        if ($payment) {
            $payment->delete();
        }

        $this->deleteId = null;

        $this->dispatch('close-modal-delete-customer-view-payment');
        $this->resetPage();
    }

    public function getCanManageCreatedAtProperty(): bool
    {
        return (bool) auth()->user()?->can('manage_created_at');
    }

    private function authorizeManageCustomerPayments(): void
    {
        abort_unless(auth()->user()?->can('manage_customer_payment_allocations'), 403);
    }

    public function getSalesProperty()
    {
        return $this->customer->sales()
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function getPaymentsProperty()
    {
        return $this->customer->payments()
            ->with(['user', 'allocations.sale'])
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function getReturnsProperty()
    {
        // Get sale returns for this customer's sales where cash_refund = false.
        return SaleReturn::whereIn('sale_id', $this->customer->sales()->pluck('id'))
            ->where('cash_refund', false)
            ->with('user', 'sale')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function getFiltersProperty()
    {
        return $this->customer->waterFilters()
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.customers.customer-view', [
            'sales' => $this->sales,
            'payments' => $this->payments,
            'returns' => $this->returns,
            'filters' => $this->filters,
        ]);
    }
}
