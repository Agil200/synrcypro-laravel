<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AtrProductionReset extends Command
{
    protected $signature = 'atr:production-reset
        {--mode=production-ready : transactional | production-ready}
        {--execute : Benar-benar jalankan penghapusan. Tanpa opsi ini hanya dry-run}
        {--allow-production : Izinkan command berjalan jika APP_ENV=production}';

    protected $description =
        'Reset data TEST ATR dengan backup otomatis tanpa menghapus master Database Karyawan dan master kategori/rule PIC Roster.';

    /**
     * Tabel transaksi ATR yang memang harus kosong sebelum data real masuk.
     */
    private const TRANSACTION_TABLES = [
        'atr_coaching_histories',
        'atr_coaching_attachments',
        'atr_coaching_counselings',
        'atr_records',
        'atr_imports',
    ];

    /**
     * Data operasional PIC yang boleh dikosongkan pada mode production-ready.
     * MASTER group dan rule TIDAK termasuk di sini.
     */
    private const PRODUCTION_READY_EXTRA_TABLES = [
        'atr_pic_monthly_rosters',
        'atr_pic_roster_histories',
    ];

    /**
     * Tabel master yang sengaja dipertahankan.
     */
    private const PRESERVED_MASTER_TABLES = [
        'atr_pic_roster_groups',
        'atr_pic_roster_rules',
    ];

    public function handle(): int
    {
        $mode = strtolower(
            trim((string) $this->option('mode'))
        );

        if (! in_array(
            $mode,
            ['transactional', 'production-ready'],
            true
        )) {
            $this->error(
                'Mode tidak valid. Gunakan transactional atau production-ready.'
            );

            return self::FAILURE;
        }

        if (
            app()->environment('production')
            && ! $this->option('allow-production')
        ) {
            $this->error(
                'DIBLOKIR: APP_ENV=production. Command ini ditujukan untuk cleanup sebelum data real.'
            );
            $this->line(
                'Jika memang sengaja dijalankan di production, tambahkan --allow-production.'
            );

            return self::FAILURE;
        }

        $tablesToClear = self::TRANSACTION_TABLES;

        if ($mode === 'production-ready') {
            $tablesToClear = array_merge(
                $tablesToClear,
                self::PRODUCTION_READY_EXTRA_TABLES
            );
        }

        $counts = $this->tableCounts($tablesToClear);
        $preservedCounts = $this->tableCounts(
            self::PRESERVED_MASTER_TABLES
        );

        $this->newLine();
        $this->info('ATR CLEANUP — PREVIEW');
        $this->line('Mode       : ' . $mode);
        $this->line(
            'Environment: ' . app()->environment()
        );

        $this->newLine();
        $this->warn('AKAN DIKOSONGKAN');
        $this->table(
            ['TABEL', 'JUMLAH DATA'],
            collect($counts)
                ->map(
                    fn (int $count, string $table): array =>
                        [$table, number_format($count)]
                )
                ->values()
                ->all()
        );

        $this->newLine();
        $this->info('MASTER YANG DIPERTAHANKAN');
        $this->table(
            ['TABEL', 'JUMLAH DATA'],
            collect($preservedCounts)
                ->map(
                    fn (int $count, string $table): array =>
                        [$table, number_format($count)]
                )
                ->values()
                ->all()
        );

        $this->newLine();
        $this->line(
            'Database Karyawan / users / migrations / template Excel: TIDAK disentuh.'
        );

        if (! $this->option('execute')) {
            $this->newLine();
            $this->warn(
                'DRY-RUN SAJA — BELUM ADA DATA YANG DIHAPUS.'
            );
            $this->line(
                'Untuk reset bersih sebelum data real:'
            );
            $this->line(
                'php artisan atr:production-reset --mode=production-ready --execute'
            );

            return self::SUCCESS;
        }

        if (
            ! $this->confirm(
                'Lanjutkan cleanup ATR sesuai preview di atas?',
                false
            )
        ) {
            $this->warn('Cleanup dibatalkan.');

            return self::SUCCESS;
        }

        try {
            $backupPath = $this->createBackup(
                $tablesToClear
            );
        } catch (Throwable $exception) {
            report($exception);

            $this->error(
                'BACKUP GAGAL. Cleanup dibatalkan demi keamanan.'
            );
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info(
            'Backup berhasil: ' . $backupPath
        );

        try {
            DB::transaction(
                function () use ($mode): void {
                    /*
                     * Urutan delete dibuat eksplisit agar aman terhadap FK.
                     */
                    $this->deleteIfExists(
                        'atr_coaching_histories'
                    );
                    $this->deleteIfExists(
                        'atr_coaching_attachments'
                    );
                    $this->deleteIfExists(
                        'atr_coaching_counselings'
                    );
                    $this->deleteIfExists(
                        'atr_records'
                    );
                    $this->deleteIfExists(
                        'atr_imports'
                    );

                    if ($mode === 'production-ready') {
                        $this->deleteIfExists(
                            'atr_pic_monthly_rosters'
                        );
                        $this->deleteIfExists(
                            'atr_pic_roster_histories'
                        );
                    }
                }
            );
        } catch (Throwable $exception) {
            report($exception);

            $this->error(
                'DELETE DATABASE GAGAL. Transaction dibatalkan.'
            );
            $this->error($exception->getMessage());
            $this->warn(
                'Backup tetap tersimpan di: ' . $backupPath
            );

            return self::FAILURE;
        }

        /*
         * Karena semua transaksi ATR sudah kosong, file transaksi
         * terkait juga aman dibersihkan. Backup sudah dibuat sebelumnya.
         */
        $this->cleanupAtrStorage();

        /*
         * Bersihkan cache rule/category PIC agar UI membaca kondisi terbaru.
         */
        foreach ([
            'atr_pic_roster_categories:v1',
            'atr_pic_roster_categories:v2',
            'atr_pic_roster_categories:v3',
        ] as $cacheKey) {
            Cache::forget($cacheKey);
        }

        $afterCounts = $this->tableCounts(
            $tablesToClear
        );

        $this->newLine();
        $this->info('HASIL SETELAH CLEANUP');
        $this->table(
            ['TABEL', 'SISA DATA'],
            collect($afterCounts)
                ->map(
                    fn (int $count, string $table): array =>
                        [$table, number_format($count)]
                )
                ->values()
                ->all()
        );

        $this->newLine();
        $this->info(
            'SELESAI — ATR siap menerima data real.'
        );

        if ($mode === 'production-ready') {
            $this->warn(
                'PIC bulanan sudah dikosongkan. Isi PIC untuk periode real melalui Pengaturan PIC Roster.'
            );
        }

        $this->line(
            'Master kategori/rule PIC Roster tetap dipertahankan.'
        );
        $this->line(
            'Backup: ' . $backupPath
        );

        return self::SUCCESS;
    }

    private function tableCounts(
        array $tables
    ): array {
        $result = [];

        foreach ($tables as $table) {
            $result[$table] =
                Schema::hasTable($table)
                    ? DB::table($table)->count()
                    : 0;
        }

        return $result;
    }

    private function deleteIfExists(
        string $table
    ): void {
        if (! Schema::hasTable($table)) {
            return;
        }

        DB::table($table)->delete();
    }

    /**
     * Backup membuat:
     * - JSON seluruh row tabel yang akan dikosongkan
     * - JSON master PIC yang dipertahankan
     * - copy file ATR di storage
     * - copy database.sqlite bila project memakai SQLite
     */
    private function createBackup(
        array $tablesToClear
    ): string {
        $timestamp = now()->format(
            'Ymd_His'
        );

        $backupDirectory =
            'atr-backups/pre-production-'
            . $timestamp;

        Storage::disk('local')
            ->makeDirectory($backupDirectory);

        $databaseBackup = [
            'created_at' => now()->toIso8601String(),
            'environment' => app()->environment(),
            'connection' => DB::connection()->getDriverName(),
            'tables_to_clear' => [],
            'preserved_master' => [],
        ];

        foreach ($tablesToClear as $table) {
            if (! Schema::hasTable($table)) {
                $databaseBackup[
                    'tables_to_clear'
                ][$table] = [];

                continue;
            }

            $databaseBackup[
                'tables_to_clear'
            ][$table] = DB::table($table)
                ->orderBy('id')
                ->get()
                ->map(
                    fn ($row) => (array) $row
                )
                ->all();
        }

        foreach (
            self::PRESERVED_MASTER_TABLES
            as $table
        ) {
            if (! Schema::hasTable($table)) {
                $databaseBackup[
                    'preserved_master'
                ][$table] = [];

                continue;
            }

            $databaseBackup[
                'preserved_master'
            ][$table] = DB::table($table)
                ->orderBy('id')
                ->get()
                ->map(
                    fn ($row) => (array) $row
                )
                ->all();
        }

        $json = json_encode(
            $databaseBackup,
            JSON_PRETTY_PRINT
            | JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            throw new \RuntimeException(
                'Gagal membuat backup JSON.'
            );
        }

        Storage::disk('local')->put(
            $backupDirectory
            . '/atr-database-backup.json',
            $json
        );

        /*
         * Copy semua file ATR yang ada sebelum cleanup.
         * Backup directory sengaja berada di luar folder "atr".
         */
        if (
            Storage::disk('local')
                ->exists('atr')
        ) {
            foreach (
                Storage::disk('local')
                    ->allFiles('atr')
                as $sourcePath
            ) {
                $targetPath =
                    $backupDirectory
                    . '/storage/'
                    . $sourcePath;

                Storage::disk('local')
                    ->makeDirectory(
                        dirname($targetPath)
                    );

                if (
                    ! Storage::disk('local')
                        ->copy(
                            $sourcePath,
                            $targetPath
                        )
                ) {
                    throw new \RuntimeException(
                        'Gagal backup file ATR: '
                        . $sourcePath
                    );
                }
            }
        }

        /*
         * User saat ini memakai SQLite.
         * JSON di atas tetap menjadi backup utama yang portable.
         * Copy SQLite ini menjadi lapisan keamanan tambahan.
         */
        if (
            DB::connection()->getDriverName()
            === 'sqlite'
        ) {
            $databaseFile = DB::connection()
                ->getDatabaseName();

            if (
                is_string($databaseFile)
                && $databaseFile !== ''
                && File::exists($databaseFile)
            ) {
                try {
                    DB::statement(
                        'PRAGMA wal_checkpoint(FULL)'
                    );
                } catch (Throwable) {
                    // Database mungkin tidak memakai WAL.
                }

                $sqliteTarget =
                    Storage::disk('local')->path(
                        $backupDirectory
                        . '/database.sqlite'
                    );

                File::ensureDirectoryExists(
                    dirname($sqliteTarget)
                );

                if (
                    ! File::copy(
                        $databaseFile,
                        $sqliteTarget
                    )
                ) {
                    throw new \RuntimeException(
                        'Gagal membuat copy database.sqlite.'
                    );
                }
            }
        }

        Storage::disk('local')->put(
            $backupDirectory
            . '/RESTORE-NOTE.txt',
            implode(PHP_EOL, [
                'SYNRGYPRO ATR PRE-PRODUCTION BACKUP',
                'Dibuat: '
                    . now()->format('Y-m-d H:i:s'),
                '',
                'Isi backup:',
                '- atr-database-backup.json',
                '- database.sqlite (jika memakai SQLite)',
                '- storage/atr/... (arsip import, coaching, signature, preview yang tersedia)',
                '',
                'Master kategori/rule PIC tidak dihapus oleh command cleanup.',
                'Jangan restore file database saat aplikasi sedang dipakai.',
            ])
        );

        return Storage::disk('local')
            ->path($backupDirectory);
    }

    private function cleanupAtrStorage(): void
    {
        /*
         * Seluruh DB transaksi ATR sudah kosong.
         * Folder berikut hanya menyimpan artefak transaksi/preview.
         */
        foreach ([
            'atr/coaching',
            'atr/imports',
            'atr/previews',
            'atr/preview',
            'atr/tmp',
            'atr/temp',
        ] as $directory) {
            if (
                Storage::disk('local')
                    ->exists($directory)
            ) {
                Storage::disk('local')
                    ->deleteDirectory(
                        $directory
                    );
            }
        }
    }
}
