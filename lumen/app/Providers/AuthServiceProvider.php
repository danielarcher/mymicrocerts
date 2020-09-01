<?php

namespace App\Providers;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use MyCerts\Domain\Model\ApiKey;
use MyCerts\Domain\Model\Candidate;
use MyCerts\Domain\Roles;

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
            if ($request->header('x-api-key')) {
                return $this->apiTokenAuth($request);
            }
            return $this->jwtTokenAuth($request);
        });
    }

    /**
     * @param Request $request
     *
     * @return Builder|Model|object|null
     */
    public function apiTokenAuth(Request $request)
    {
        Log::debug('x-api-key sent on header');
        $apiKey = ApiKey::where(['key' => $request->header('x-api-key')])->first();
        if (!$apiKey) {
            Log::debug('x-api-key not found on database');
            return null;
        }

        Log::debug('x-api-key found on database, looking for candidate');
        
        return Candidate::with('company')->where([
            'company_id' => $apiKey->company_id,
            'role'       => Roles::COMPANY
        ])->first();
    }

    /**
     * @param Request $request
     *
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function jwtTokenAuth(Request $request)
    {
        if (!$request->bearerToken()) {
            return null;
        }
        /**
         * Decode
         */
        $decoded = JWT::decode($request->bearerToken(), env('JWT_SECRET'), ['HS256']);
        /**
         * Validate
         */
        if (Carbon::createFromDate($decoded->valid_until) <= Carbon::now()) {
            return null;
        }
        return Candidate::with('company')->find($decoded->candidate->id);
    }
}
