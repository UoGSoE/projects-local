<?php

namespace App\Providers;

use App\Models\Course;
use App\Observers\CourseObserver;
use App\Observers\ProgrammeObserver;
use App\Observers\ProjectObserver;
use App\Models\Programme;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        if ($this->app->environment('testing') and config('database.default') === 'sqlite') {
            DB::statement(DB::raw('PRAGMA foreign_keys=1'));
        }

        Project::observe(ProjectObserver::class);
        Course::observe(CourseObserver::class);
        Programme::observe(ProgrammeObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
