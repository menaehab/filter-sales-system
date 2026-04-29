<?php

namespace App\Http\Requests\Sales;

use App\Enums\PaymentTypeEnum;
use App\Enums\WaterQualityTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAny(['manage_sales', 'add_sales']) ?? false;
    }

    public function rules(): array
    {
        $rules = [
            'customer_id' => ['required', 'exists:customers,id'],
            'payment_type' => ['required', 'in:'.implode(',', array_column(PaymentTypeEnum::cases(), 'value'))],
            'down_payment' => ['required_if:payment_type,installment', 'nullable', 'numeric', 'min:0'],
            'installment_months' => ['required_if:payment_type,installment', 'nullable', 'integer', 'min:1', 'max:60'],
            'interest_rate' => ['required_if:payment_type,installment', 'nullable', 'numeric', 'min:0', 'max:100'],
            'installment_start_date' => ['nullable', 'date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'with_vat' => ['boolean'],
            'dealer_name' => ['nullable', 'string', 'max:255'],
            'created_at' => ['nullable', 'date'],
            'includeWaterReading' => ['boolean'],
            'cart' => ['required', 'array', 'min:1'],
            'cart.*.product_id' => ['required', 'exists:products,id'],
            'cart.*.sell_price' => ['required', 'numeric', 'min:0.01'],
            'cart.*.cost_price' => ['nullable', 'numeric'],
            'cart.*.quantity' => ['required', 'integer', 'min:1'],
        ];

        // Water reading validation
        if ($this->boolean('includeWaterReading')) {
            if (! $this->boolean('createNewFilter')) {
                $rules['water_filter_id'] = ['required', 'exists:water_filters,id'];
            } else {
                $rules['customer_id'] = [...$rules['customer_id'], Rule::unique('water_filters', 'customer_id')];
                $rules['newFilter.filter_model'] = ['required', 'string', 'max:255'];
                $rules['newFilter.address'] = ['required', 'string', 'max:255'];
                $rules['newFilter.is_installed'] = ['required', 'boolean'];
                $rules['newFilter.installed_at'] = ['nullable', 'date', 'required_if:newFilter.is_installed,1'];
            }

            $waterQualityValues = implode(',', array_column(WaterQualityTypeEnum::cases(), 'value'));
            $waterReading = (array) data_get($this->all(), 'waterReading', []);
            $hasWaterReadingInput = filled(data_get($waterReading, 'technician_name'))
                || filled(data_get($waterReading, 'tds'))
                || filled(data_get($waterReading, 'water_quality'))
                || (bool) data_get($waterReading, 'before_installment', false)
                || $this->boolean('includeAfterInstallationReading');

            if ($hasWaterReadingInput) {
                $rules['waterReading.technician_name'] = ['required', 'string', 'max:255'];
                $rules['waterReading.tds'] = ['required', 'numeric', 'min:0'];
                $rules['waterReading.water_quality'] = ['required', 'in:'.$waterQualityValues];
            }

            $rules['waterReading.before_installment'] = ['boolean'];
            $rules['includeAfterInstallationReading'] = ['boolean'];

            if (
                $hasWaterReadingInput
                && (bool) data_get($waterReading, 'before_installment', false)
                && $this->boolean('includeAfterInstallationReading')
            ) {
                $rules['afterWaterReading.technician_name'] = ['required', 'string', 'max:255'];
                $rules['afterWaterReading.tds'] = ['required', 'numeric', 'min:0'];
                $rules['afterWaterReading.water_quality'] = ['required', 'in:'.$waterQualityValues];
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'customer_id' => __('keywords.customer'),
            'payment_type' => __('keywords.payment_type'),
            'down_payment' => __('keywords.down_payment'),
            'installment_months' => __('keywords.installment_months'),
            'interest_rate' => __('keywords.interest_rate'),
            'installment_start_date' => __('keywords.installment_start_date'),
            'with_vat' => __('keywords.with_vat'),
            'dealer_name' => __('keywords.dealer_name'),
            'created_at' => __('keywords.created_at'),
            'cart' => __('keywords.cart'),
            'cart.*.product_id' => __('keywords.product'),
            'cart.*.sell_price' => __('keywords.sell_price'),
            'cart.*.quantity' => __('keywords.quantity'),
            'water_filter_id' => __('keywords.water_filter'),
            'newFilter.filter_model' => __('keywords.filter_model'),
            'newFilter.address' => __('keywords.filter_address'),
            'newFilter.is_installed' => __('keywords.is_installed'),
            'newFilter.installed_at' => __('keywords.installed_at'),
            'waterReading.technician_name' => __('keywords.technician_name'),
            'waterReading.tds' => __('keywords.tds_reading'),
            'waterReading.water_quality' => __('keywords.water_quality'),
            'waterReading.before_installment' => __('keywords.before_installment'),
            'includeAfterInstallationReading' => __('keywords.add_after_installment_reading'),
            'afterWaterReading.technician_name' => __('keywords.technician_name'),
            'afterWaterReading.tds' => __('keywords.tds_reading'),
            'afterWaterReading.water_quality' => __('keywords.water_quality'),
        ];
    }
}
