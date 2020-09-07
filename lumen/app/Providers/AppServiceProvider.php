<?php

namespace App\Providers;

use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(IdeHelperServiceProvider::class);
        }
    }

    public function boot()
    {
        DB::listen(function ($query) {
            Log::debug('query log',
                [
                    'sql'      => $query->sql,
                    'bindings' => $query->bindings,
                    'time'     => $query->time
                ]
            );
        });
    }
}
