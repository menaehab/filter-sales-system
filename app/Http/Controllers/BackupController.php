<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        Artisan::call('app:backup');

        $file = collect(Storage::files('backups'))->sortDesc()->first();

        if (! $file) {
            abort(404, 'Backup file not found.');
        }

        return Storage::download($file);
    }
}
