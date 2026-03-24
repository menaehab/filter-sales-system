<?php

namespace App\Livewire\Sales;

use App\Models\CustomerPayment;
use App\Models\CustomerPaymentAllocation;
use App\Models\Sale;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SaleShow extends Component
{
    public Sale $sale;

    public ?int $paySaleId = null;

    public string $payAmount = '';

    public string $payMethod = 'cash';

    public string $payNote = '';

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

        if ($sale->isInstallment() && $sale->customer_id) {
            $oldestUnpaid = $this->getCustomerInstallmentQueue($sale->customer_id)->first();

            if ($oldestUnpaid) {
                $this->payFromSaleId = $oldestUnpaid->id;
                $defaultInstallment = (float) ($oldestUnpaid->installment_amount ?: $oldestUnpaid->remaining_amount);
                $this->payAmount = (string) min($defaultInstallment, $oldestUnpaid->remaining_amount);
            } else {
                $this->payAmount = (string) $sale->remaining_amount;
            }
        } else {
            $this->payAmount = (string) $sale->remaining_amount;
        }

        $this->payMethod = 'cash';
        $this->payNote = '';
        $this->dispatch('open-modal-pay-sale');
    }

    public function submitPayment(): void
    {
        $this->authorizePaySales();

        $this->validate([
            'payAmount' => 'required|numeric|min:0.01',
            'payMethod' => 'required|string',
        ], [], [
            'payAmount' => __('keywords.amount'),
            'payMethod' => __('keywords.payment_method'),
        ]);

        $sale = Sale::with('paymentAllocations')->findOrFail($this->sale->id);
        $amount = (float) $this->payAmount;

        if ($amount <= 0 || $sale->isFullyPaid()) {
            return;
        }

        $allocations = [];

        if ($sale->isInstallment() && $sale->customer_id) {
            $queue = $this->getCustomerInstallmentQueue($sale->customer_id);
            $maxPayable = $queue->sum(fn (Sale $item) => $item->remaining_amount);
            $remainingToAllocate = min($amount, $maxPayable);

            foreach ($queue as $queuedSale) {
                if ($remainingToAllocate <= 0) {
                    break;
                }

                $payable = min($queuedSale->remaining_amount, $remainingToAllocate);

                if ($payable > 0) {
                    $allocations[] = [
                        'sale_id' => $queuedSale->id,
                        'amount' => $payable,
                    ];
                    $remainingToAllocate -= $payable;
                }
            }
        } else {
            $maxPayable = $sale->remaining_amount;
            $amount = min($amount, $maxPayable);

            if ($amount > 0) {
                $allocations[] = [
                    'sale_id' => $sale->id,
                    'amount' => $amount,
                ];
            }
        }

        $totalAllocated = collect($allocations)->sum('amount');

        if ($totalAllocated <= 0) {
            return;
        }

        $payment = CustomerPayment::create([
            'amount' => $totalAllocated,
            'payment_method' => $this->payMethod,
            'note' => $this->payNote ?: null,
            'customer_id' => $sale->customer_id,
            'user_id' => auth()->id(),
        ]);

        foreach ($allocations as $allocation) {
            CustomerPaymentAllocation::create([
                'amount' => $allocation['amount'],
                'customer_payment_id' => $payment->id,
                'sale_id' => $allocation['sale_id'],
            ]);
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

    protected function getCustomerInstallmentQueue(int $customerId)
    {
        return Sale::with('paymentAllocations')
            ->where('customer_id', $customerId)
            ->where('installment_months', '>', 0)
            ->orderBy('created_at')
            ->get()
            ->filter(fn (Sale $item) => $item->remaining_amount > 0)
            ->values();
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

    protected function authorizePaySales(): void
    {
        abort_unless(auth()->user()?->canAny(['manage_sales', 'pay_sales']), 403);
    }

    public function render()
    {
        return view('livewire.sales.sale-show', [
            'sale' => $this->sale,
        ]);
    }
}
