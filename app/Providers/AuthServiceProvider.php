<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use App\Policies\ProjectPolicy;
use App\Providers\LdapLocalUserProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
        'App\Project' => ProjectPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        \Auth::provider('ldapeloquent', function ($app, array $config) {
            return new LdapLocalUserProvider($app['hash'], $config['model']);
        });

        Gate::define('accept-students', function ($user, $project) {
            if ($user->isAdmin()) {
                return true;
            }
            if ($project->category == 'undergrad') {
                return true;
            }
            return false;
        });

        Gate::define('accept-onto-project', function ($user, $student, $project) {
            if ($user->isAdmin()) {
                return true;
            }
            if ($student->isFirstChoice($project)) {
                return true;
            }
            return false;
        });
    }
}
