<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\InvalidDateException;
use App\Exceptions\InvalidFileCountException;

class DownloadDatabase extends Command
{
    /**
     * @var string
     */
    private $backupFile = 'dbbackup.zip';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download-database {--date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download the latest API database backup from S3';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (file_exists(storage_path('app/' . $this->backupFile))) {
            unlink(storage_path('app/' . $this->backupFile));
        }

        $date = Carbon::now()->format('Y-m-d');

        try {
            if ($this->option('date') && Carbon::parse($this->option('date'))) {
                $date = Carbon::parse($this->option('date'))->format('Y-m-d');
            }
        } catch (\Exception $e) {
            throw new InvalidDateException();
        }

        $files = Storage::disk('s3')->files(config('filesystems.disks.s3.path') . "/$date");

        if (count($files) == 1) {
            $s3File = Storage::disk('s3')->get($files[0]);
            $localStorage = Storage::disk('local');
            $localStorage->put($this->backupFile, $s3File);
        } else {
            throw new InvalidFileCountException('Expected 1 file, found ' . count($files));
        }
    }
}
