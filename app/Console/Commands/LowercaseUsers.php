<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class LowercaseUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lowercase:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert all user usernames & emails to lowercase';

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
     */
    public function handle(): int
    {
        User::all()->each(function ($user) {
            $user->update([
                'username' => strtolower($user->username),
                'email' => strtolower($user->email),
            ]);
        });
    }
}
