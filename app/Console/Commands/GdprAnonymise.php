<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Facades\Ohffs\Ldap\LdapService;
use Illuminate\Support\Facades\Log;

class GdprAnonymise extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:gdpranonymise';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Anonymise accounts of staff who have left';

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
        $staff = User::staff()->where('username', 'not like', 'ANON%')->get();

        $staff->each(function ($user) {
            if ($this->userIsInLdap($user->username)) {
                $user->markAsStillHere();
                return;
            }

            Log::info('Anonymising ' . $user->username);
            if ($user->leftAgesAgo()) {
                $user->anonymise();
            } else {
                $user->markAsLeft();
            }
        });
    }

    public function userIsInLdap($username)
    {
        return LdapService::findUser($username);
    }
}
