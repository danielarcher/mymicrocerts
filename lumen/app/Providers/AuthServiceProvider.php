<?php

namespace App\Providers;

use App\User;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use MyCerts\Domain\Model\Candidate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['auth']->viaRequest('api', function (Request $request) {
            if (!$request->bearerToken())  return null;
            /**
             * Decode
             */
            $decoded = JWT::decode($request->bearerToken(), env('JWT_SECRET'), array('HS256'));
            /**
             * Validate
             */
            if (Carbon::createFromDate($decoded->valid_until) <= Carbon::now()) {
                return null;
            }
            return Candidate::with('company')->find($decoded->candidate->id);
        });
    }
}
