<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('suppliers:installments-remind')->daily();
Schedule::command('filters:candle-remind')->daily();
Schedule::command('customers:installment-remind')->daily();
Schedule::command('products:low-stock-alert')->daily();
