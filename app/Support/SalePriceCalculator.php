<?php

declare(strict_types=1);

namespace App\Support;

final class SalePriceCalculator
{
    private const VAT_RATE = 14;

    private const INSTALLMENT_MONTHLY_SURCHARGE = 100;

    private const SURCHARGE_THRESHOLD_MONTHS = 3;

    private float $baseTotal = 0;

    private float $discount = 0;

    private bool $withVat = false;

    private float $downPayment = 0;

    private int $installmentMonths = 0;

    private float $interestRate = 0;

    private float $appliedCredit = 0;

    private bool $isInstallment = false;

    public static function make(): self
    {
        return new self;
    }

    public function withItems(array $items): self
    {
        $this->baseTotal = collect($items)->sum(function ($item) {
            $price = is_array($item)
                ? (float) ($item['sell_price'] ?? 0)
                : $item->sellPrice;
            $quantity = is_array($item)
                ? (int) ($item['quantity'] ?? 0)
                : $item->quantity;

            return $price * $quantity;
        });

        return $this;
    }

    public function withDiscount(float $discount): self
    {
        $this->discount = max(0, $discount);

        return $this;
    }

    public function withVat(bool $withVat = true): self
    {
        $this->withVat = $withVat;

        return $this;
    }

    public function withInstallment(float $downPayment, int $months, float $interestRate): self
    {
        $this->isInstallment = true;
        $this->downPayment = max(0, $downPayment);
        $this->installmentMonths = max(0, $months);
        $this->interestRate = max(0, min(100, $interestRate));

        return $this;
    }

    public function withAppliedCredit(float $credit): self
    {
        $this->appliedCredit = max(0, $credit);

        return $this;
    }

    public function baseTotal(): float
    {
        return $this->baseTotal;
    }

    public function discountAmount(): float
    {
        return min($this->baseTotal, $this->discount);
    }

    public function totalAfterDiscount(): float
    {
        return max(0, $this->baseTotal - $this->discountAmount());
    }

    public function vatAmount(): float
    {
        if (! $this->withVat) {
            return 0;
        }

        return round($this->totalAfterDiscount() * (self::VAT_RATE / 100), 2);
    }

    public function subtotalAfterVat(): float
    {
        return $this->totalAfterDiscount() + $this->vatAmount();
    }

    public function effectiveDownPayment(): float
    {
        if (! $this->isInstallment) {
            return max(0, $this->subtotalAfterVat() - $this->appliedCredit);
        }

        return min($this->downPayment, max(0, $this->subtotalAfterVat() - $this->appliedCredit));
    }

    public function remainingAfterDownPayment(): float
    {
        return max(0, $this->subtotalAfterVat() - $this->appliedCredit - $this->effectiveDownPayment());
    }

    public function interestAmount(): float
    {
        if (! $this->isInstallment) {
            return 0;
        }

        return round($this->remainingAfterDownPayment() * ($this->interestRate / 100), 2);
    }

    public function installmentSurchargeTotal(): float
    {
        if (! $this->isInstallment || $this->installmentMonths < self::SURCHARGE_THRESHOLD_MONTHS) {
            return 0;
        }

        return $this->installmentMonths * self::INSTALLMENT_MONTHLY_SURCHARGE;
    }

    public function installmentTotal(): float
    {
        if (! $this->isInstallment) {
            return 0;
        }

        return $this->remainingAfterDownPayment() + $this->interestAmount() + $this->installmentSurchargeTotal();
    }

    public function installmentAmount(): float
    {
        if (! $this->isInstallment || $this->installmentMonths <= 0) {
            return 0;
        }

        return round($this->installmentTotal() / $this->installmentMonths, 2);
    }

    public function grandTotal(): float
    {
        if (! $this->isInstallment) {
            return $this->subtotalAfterVat();
        }

        return $this->subtotalAfterVat() + $this->interestAmount() + $this->installmentSurchargeTotal();
    }

    public function cashAmountDue(): float
    {
        return $this->effectiveDownPayment();
    }

    public function toArray(): array
    {
        return [
            'base_total' => $this->baseTotal(),
            'discount_amount' => $this->discountAmount(),
            'total_after_discount' => $this->totalAfterDiscount(),
            'vat_amount' => $this->vatAmount(),
            'subtotal_after_vat' => $this->subtotalAfterVat(),
            'applied_credit' => $this->appliedCredit,
            'down_payment' => $this->effectiveDownPayment(),
            'remaining_after_down_payment' => $this->remainingAfterDownPayment(),
            'interest_amount' => $this->interestAmount(),
            'installment_surcharge_total' => $this->installmentSurchargeTotal(),
            'installment_total' => $this->installmentTotal(),
            'installment_amount' => $this->installmentAmount(),
            'grand_total' => $this->grandTotal(),
            'cash_amount_due' => $this->cashAmountDue(),
        ];
    }
}
