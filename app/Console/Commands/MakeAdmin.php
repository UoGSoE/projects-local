<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:makeadmin {username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a given user an admin';

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
        $user = User::where('username', '=', $this->argument('username'))->firstOrFail();

        $user->makeAdmin();
    }
}
