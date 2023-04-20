<?php

namespace App\Console\Commands;

use App\Imports\OldDataImporter;
use Illuminate\Console\Command;

class ImportOldData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:import-old {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import old project system data from a json file';

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
    public function handle(): void
    {
        if (! file_exists($this->argument('filename'))) {
            $this->error('File does not exist: '.$this->argument('filename'));
            exit(1);
        }

        $jsonString = file_get_contents($this->argument('filename'));

        (new OldDataImporter($jsonString))->import();
    }
}
