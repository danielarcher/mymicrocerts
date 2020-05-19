<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MyCerts\Domain\Model\Candidate;
use MyCerts\Domain\Roles;

class AdminOnly
{
   /**
     * Handle an incoming request.
     *
     * @param Request  $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var Candidate $user */
        $user = Auth::user();
        if (false === $user->isAdmin()) {
            return response('Unauthorized.', 401);
        }
        return $next($request);
    }
}
