<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class BackupDatabaseTest extends TestCase
{
    private string $folder;

    /** @var list<string> */
    private array $existingBackups = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->folder = storage_path('framework/testing/backups');
        File::deleteDirectory($this->folder);
        $this->existingBackups = File::glob(storage_path('app/backups/bb_electronics_*.sql'));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->folder);
        foreach (array_diff(File::glob(storage_path('app/backups/bb_electronics_*.sql')), $this->existingBackups) as $file) {
            File::delete($file);
        }

        parent::tearDown();
    }

    public function test_it_saves_the_dump_to_a_dated_file_in_both_folders(): void
    {
        Process::fake(['*' => Process::result('CREATE TABLE sale (id int);')]);

        $this->artisan('backup:database', ['--path' => $this->folder])->assertSuccessful();

        $copies = File::glob($this->folder.'/bb_electronics_*.sql');
        $this->assertCount(1, $copies);
        $this->assertStringContainsString('CREATE TABLE sale (id int);', File::get($copies[0]));
        $this->assertCount(count($this->existingBackups) + 1, File::glob(storage_path('app/backups/bb_electronics_*.sql')));
    }

    public function test_it_keeps_only_the_newest_backups(): void
    {
        Process::fake(['*' => Process::result('dump')]);
        File::ensureDirectoryExists($this->folder);

        foreach (['2026-01-01_000000', '2026-01-02_000000', '2026-01-03_000000'] as $stamp) {
            File::put($this->folder."/bb_electronics_{$stamp}.sql", 'old');
        }

        $this->artisan('backup:database', ['--path' => $this->folder, '--keep' => 2])->assertSuccessful();

        $names = array_map('basename', File::glob($this->folder.'/bb_electronics_*.sql'));
        $this->assertCount(2, $names);
        $this->assertNotContains('bb_electronics_2026-01-01_000000.sql', $names);
        $this->assertNotContains('bb_electronics_2026-01-02_000000.sql', $names);
    }

    public function test_it_reports_a_failed_dump_and_writes_nothing(): void
    {
        Process::fake(['*' => Process::result('', 'mysqldump: command not found', 1)]);

        $this->artisan('backup:database', ['--path' => $this->folder])->assertFailed();

        $this->assertSame([], File::glob($this->folder.'/*.sql'));
    }
}
