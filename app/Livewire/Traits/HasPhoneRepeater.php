<?php

namespace App\Livewire\Traits;

trait HasPhoneRepeater
{
    public function addPhoneRow(string $field): void
    {
        $phones = data_get($this, $field, []);

        if (! is_array($phones)) {
            $phones = [];
        }

        $phones[] = ['number' => ''];

        data_set($this, $field, $phones);
    }

    public function removePhoneRow(string $field, int $index): void
    {
        $phones = data_get($this, $field, []);

        if (! is_array($phones)) {
            $phones = [];
        }

        if (count($phones) <= 1) {
            $phones = [['number' => '']];
            data_set($this, $field, $phones);

            return;
        }

        unset($phones[$index]);

        data_set($this, $field, array_values($phones));
    }
}
