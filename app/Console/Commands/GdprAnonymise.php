<?php

namespace App\Console\Commands;

use App\Mail\GdprAnonymisedUsers;
use App\Models\User;
use Facades\Ohffs\Ldap\LdapService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
    public function handle(): void
    {
        $anonymisedUsers = collect([]);
        $staff = User::staff()->where('username', 'not like', 'ANON%')->get();

        $staff->each(function ($user) use ($anonymisedUsers) {
            if ($this->userIsInLdap($user->username)) {
                $user->markAsStillHere();

                return;
            }

            if ($user->leftAgesAgo()) {
                Log::info('Anonymising '.$user->username);
                $oldUsername = $user->username;
                $anonUsername = $user->anonymise();
                $anonymisedUsers->push(['originalName' => $oldUsername, 'anonName' => $anonUsername]);
            } else {
                Log::info('User seems to have left '.$user->username);
                $user->markAsLeft();
            }
        });

        if ($anonymisedUsers->count() > 0) {
            Mail::to(config('projects.gdpr_contact'))->queue(new GdprAnonymisedUsers($anonymisedUsers));
        }
    }

    public function userIsInLdap($username)
    {
        return LdapService::findUser($username);
    }
}
