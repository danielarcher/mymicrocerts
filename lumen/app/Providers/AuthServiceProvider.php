<?php

namespace App\Providers;

use App\User;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use MyCerts\Domain\Model\Candidate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['auth']->viaRequest('api', function ($request) {
            $token = str_replace('Bearer ','',$request->header('Authorization'));
            if (empty($token)){
                return null;
            }
            $decoded = JWT::decode($token, env('JWT_SECRET'), array('HS256'));
            if (Carbon::createFromDate($decoded->valid_until) <= Carbon::now()) {
                return null;
            }
            return Candidate::with('company')->find($decoded->candidate->id);
        });
    }
}
