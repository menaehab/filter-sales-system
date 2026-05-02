<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use Illuminate\Http\Request;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.print', ['title' => 'الأقساط المتأخرة', 'orientation' => 'landscape'])]
class OverdueInstallmentsPrint extends Component
{
    public function render(Request $request)
    {
        $ids = array_filter(array_map('intval', explode(',', (string) $request->query('ids', ''))));

        $overdueSales = Sale::query()
            ->whereNotNull('installment_months')
            ->where('installment_months', '>', 0)
            ->whereNotNull('installment_start_date')
            ->with(['customer.place', 'customer.phones', 'items.product', 'paymentAllocations'])
            ->when($ids !== [], fn ($query) => $query->whereIn('id', $ids))
            ->get()
            ->filter(function (Sale $sale): bool {
                return ! $sale->isFullyPaid()
                    && $sale->next_installment_date
                    && $sale->next_installment_date->lte(now());
            })
            ->sortBy(fn (Sale $sale) => $sale->next_installment_date)
            ->values();

        return view('livewire.sales.overdue-installments-print', compact('overdueSales'));
    }
}
