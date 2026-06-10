<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
class BackupCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'app:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup command';
    public function handle()
    {
        $script = base_path('backup.bat');

        if (!file_exists($script)) {
            $this->error('Backup script not found: ' . $script);
            return;
        }

        exec('cmd /c ""' . $script . '""', $output, $status);

        $this->info('Backup finished with status: ' . $status);

        $causer = app()->runningInConsole() ? null : auth()->user();

        activity()
            ->causedBy($causer)
            ->event('backup')
            ->withProperties([
                'name' => 'database',
            ])
            ->log(__('keywords.backup'));
    }
}
