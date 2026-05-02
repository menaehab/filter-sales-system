<?php

namespace App\Livewire\Sales;

use App\Actions\CustomerPayments\CreateCustomerPaymentAction;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Sale;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class OverdueInstallments extends Component
{
    use WithSearchAndPagination;

    // Payment modal state
    #[Locked]
    public ?int $paySaleId = null;

    public string $payAmount = '';

    public string $payMethod = 'cash';

    public string $payNote = '';

    public string $payCreatedAt = '';

    public bool $printAfterPayment = false;

    public function updatingSearch(): void
    {
        $this->paySaleId = null;
        $this->resetPage();
    }

    #[Computed]
    public function overdueSales()
    {
        $allOverdue = Sale::query()
            ->whereNotNull('installment_months')
            ->where('installment_months', '>', 0)
            ->whereNotNull('installment_start_date')
            ->with(['customer.place', 'customer.phones', 'items.product', 'paymentAllocations'])
            ->when(filled($this->search), function ($query) {
                $query->where(function ($q) {
                    $q->where('number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', function ($cq) {
                            $cq->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('code', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->get()
            ->filter(function (Sale $sale): bool {
                return ! $sale->isFullyPaid()
                    && $sale->next_installment_date
                    && $sale->next_installment_date->lte(now());
            })
            ->sortBy(fn (Sale $sale) => $sale->next_installment_date)
            ->values();

        $page = $this->getPage();
        $perPage = $this->perPage;

        return new LengthAwarePaginator(
            $allOverdue->slice(($page - 1) * $perPage, $perPage)->values(),
            $allOverdue->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }

    // ==========================================
    // PAYMENT MODAL ACTIONS
    // ==========================================

    public function openPayModal(int $id): void
    {
        $this->authorizePaySales();

        $sale = Sale::with('paymentAllocations')->findOrFail($id);

        if ($sale->isFullyPaid()) {
            return;
        }

        $this->paySaleId = $sale->id;
        $this->payAmount = (string) $sale->installment_amount;
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
            'sale_id'        => $this->paySaleId,
            'amount'         => $this->payAmount,
            'payment_method' => $this->payMethod,
            'note'           => $this->payNote,
            'created_at'     => $this->payCreatedAt,
        ];

        $validator = Validator::make(
            $formData,
            $request->rules(),
            $request->messages(),
            $request->attributes()
        );

        $validated = $validator->validate();

        $sale = Sale::with('paymentAllocations')->findOrFail($this->paySaleId);

        if ($sale->isFullyPaid()) {
            return;
        }

        $payment = $action->execute($sale->id, [
            'amount'         => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'note'           => $this->payNote ?: null,
            'created_at'     => $validated['created_at'] ?? null,
        ]);

        if (! $payment) {
            return;
        }

        $printAfterPayment = $this->printAfterPayment;
        $paymentId = $payment->id;

        $this->resetPayForm();
        $this->dispatch('close-modal-pay-sale');

        // Force re-compute overdue sales
        unset($this->overdueSales);

        if ($printAfterPayment) {
            $this->redirect(route('customer-payments.print', $paymentId), navigate: true);
        }
    }

    public function resetPayForm(): void
    {
        $this->paySaleId        = null;
        $this->payAmount        = '';
        $this->payMethod        = 'cash';
        $this->payNote          = '';
        $this->payCreatedAt     = '';
        $this->printAfterPayment = false;
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
        return view('livewire.sales.overdue-installments', [
            'overdueSales'     => $this->overdueSales,
            'canManageCreatedAt' => $this->canManageCreatedAt,
        ]);
    }
}
