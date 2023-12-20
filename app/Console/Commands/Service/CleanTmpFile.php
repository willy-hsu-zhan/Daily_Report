<?php

namespace App\Console\Commands\Service;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanTmpFile extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:clean-tmp-file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tmpFolderPath = storage_path('app/livewire-tmp');

        if( File::exists($tmpFolderPath) )
        {
            File::cleanDirectory($tmpFolderPath);
            $this->info('暫存檔案已被刪除！');
        }
        else
        {
            $this->info('沒有暫存檔案！');
        }
    }
}
