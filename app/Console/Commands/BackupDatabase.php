<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

#[Signature('backup:database {--path= : Extra folder to copy the backup to, for example a USB drive} {--keep=30 : How many backups to keep in each folder}')]
#[Description('Save a full copy of the database (tables, data and stock triggers) to a dated .sql file')]
class BackupDatabase extends Command
{
    public function handle(): int
    {
        $connection = config('database.connections.'.config('database.default'));

        $dump = Process::env(['MYSQL_PWD' => (string) $connection['password']])->run([
            config('shop.mysqldump_path'),
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
            '--routines',
            '--triggers',
            '--single-transaction',
            $connection['database'],
        ]);

        if ($dump->failed() || trim($dump->output()) === '') {
            $this->error('The backup failed. Check that mysqldump can be found (set MYSQLDUMP_PATH in .env). '.trim($dump->errorOutput()));

            return self::FAILURE;
        }

        $fileName = 'bb_electronics_'.now()->format('Y-m-d_His').'.sql';
        $folders = array_filter([storage_path('app/backups'), $this->option('path')]);

        foreach ($folders as $folder) {
            File::ensureDirectoryExists($folder);
            File::put($folder.DIRECTORY_SEPARATOR.$fileName, $dump->output());
            $this->prune($folder, (int) $this->option('keep'));
            $this->info('Saved '.$folder.DIRECTORY_SEPARATOR.$fileName);
        }

        return self::SUCCESS;
    }

    private function prune(string $folder, int $keep): void
    {
        $old = collect(File::glob($folder.DIRECTORY_SEPARATOR.'bb_electronics_*.sql'))->sort()->values();

        $old->take(max(0, $old->count() - max(1, $keep)))->each(fn (string $file) => File::delete($file));
    }
}
