<?php

namespace App\Livewire\Sales;

use App\Actions\CustomerPayments\CreateCustomerPaymentAction;
use App\Actions\Sales\DeleteSaleAction;
use App\Enums\SaleStatusEnum;
use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class SaleManagement extends Component
{
    use HasCrudModals, HasCrudQuery, HasForm, WithSearchAndPagination;

    public string $filterPaymentType = '';

    public string $filterStatus = '';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    // Payment modal state - only primitive types
    #[Locked]
    public ?int $paySaleId = null;

    public string $payAmount = '';

    public string $payMethod = 'cash';

    public string $payNote = '';

    public ?int $payFromSaleId = null;

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
        return Sale::class;
    }

    protected function getSearchableFields(): array
    {
        return ['dealer_name', 'user_name', 'number', 'customer.name'];
    }

    protected function getWithRelations(): array
    {
        return ['items', 'paymentAllocations', 'customer'];
    }

    protected function applyAdditionalFilters(Builder $query): void
    {
        if (filled($this->filterPaymentType)) {
            $query->where('payment_type', $this->filterPaymentType);
        }

        $this->applyStatusFilter($query);
        $this->applyDateFilters($query);
    }

    private function applyStatusFilter(Builder $query): void
    {
        match ($this->filterStatus) {
            SaleStatusEnum::PAID->value => $query->whereRaw(
                'total_price <= COALESCE((SELECT SUM(amount) FROM customer_payment_allocations WHERE customer_payment_allocations.sale_id = sales.id), 0)'
            ),
            SaleStatusEnum::PARTIAL->value => $query->where('installment_months', '>', 0)
                ->whereRaw('COALESCE((SELECT SUM(amount) FROM customer_payment_allocations WHERE customer_payment_allocations.sale_id = sales.id), 0) > 0')
                ->whereRaw('total_price > COALESCE((SELECT SUM(amount) FROM customer_payment_allocations WHERE customer_payment_allocations.sale_id = sales.id), 0)'),
            SaleStatusEnum::UNPAID->value => $query->where(fn ($q) => $q->where('installment_months', '>', 0))
                ->whereRaw('COALESCE((SELECT SUM(amount) FROM customer_payment_allocations WHERE customer_payment_allocations.sale_id = sales.id), 0) = 0'),
            default => null,
        };
    }

    private function applyDateFilters(Builder $query): void
    {
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

    // ==========================================
    // COMPUTED PROPERTIES
    // ==========================================

    #[Computed]
    public function sales()
    {
        return $this->items;
    }


    // ==========================================
    // PAYMENT MODAL ACTIONS
    // ==========================================

    public function openPayModal(int $id): void
    {
        $this->authorizePaySales();

        $sale = Sale::with('paymentAllocations')->findOrFail($id);

        $this->paySaleId = $sale->id;
        $this->payFromSaleId = null;

        $this->payAmount = (string) $sale->remaining_amount;

        $this->payMethod = 'cash';
        $this->payNote = '';
        $this->dispatch('open-modal-pay-sale');
    }

    public function submitPayment(CreateCustomerPaymentAction $action): void
    {
        $this->authorizePaySales();

        $this->validate([
            'payAmount' => 'required|numeric|min:0.01',
            'payMethod' => 'required|string',
        ], [], [
            'payAmount' => __('keywords.amount'),
            'payMethod' => __('keywords.payment_method'),
        ]);

        $sale = Sale::with('paymentAllocations')->findOrFail($this->paySaleId);

        if ($sale->isFullyPaid()) {
            return;
        }

        $payment = $action->execute($sale->id, [
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
        $this->printAfterPayment = false;
    }

    // ==========================================
    // DELETE ACTIONS
    // ==========================================

    public function setDelete(int $id): void
    {
        $this->authorizeManageSales();
        $this->openDeleteModal($id, 'open-modal-delete-sale');
    }

    public function delete(DeleteSaleAction $action): void
    {
        $this->authorizeManageSales();

        $sale = Sale::with('items', 'paymentAllocations')->findOrFail($this->deleteId);

        $action->execute($sale);

        $this->deleteId = null;
        $this->dispatch('close-modal-delete-sale');
        $this->resetPage();
    }

    // ==========================================
    // AUTHORIZATION
    // ==========================================

    protected function authorizeManageSales(): void
    {
        abort_unless(auth()->user()?->can('manage_sales'), 403);
    }

    protected function authorizePaySales(): void
    {
        abort_unless(auth()->user()?->canAny(['manage_sales', 'pay_sales']), 403);
    }

    public function render()
    {
        return view('livewire.sales.sale-management');
    }
}
