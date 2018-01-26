<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
        'App\Project' => 'App\Policies\ProjectPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

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
