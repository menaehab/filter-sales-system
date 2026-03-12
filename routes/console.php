<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('installments:remind')->daily();
