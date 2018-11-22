<?php

namespace App\Providers;

use App\Policies\ProjectPolicy;
use App\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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

        \Horizon::auth(function ($request) {
            return $request->user()->isAdmin();
        });

        Gate::define('accept-students', function ($user, $project) {
            if ($user->isAdmin()) {
                return true;
            }
            if ($project->students()->wherePivot('is_accepted', true)->count() >= $project->max_students) {
                return false;
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
