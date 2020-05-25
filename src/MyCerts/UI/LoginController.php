<?php

namespace MyCerts\UI;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use MyCerts\Domain\Model\Candidate;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $this->validate($request, [
            'email'         => 'required|email',
            'password'      => 'required',
        ]);
        /**
         * Validate user
         */
        $candidate = Candidate::where('email', $request->get('email'))->first();
        if (!$candidate) {
            return response()->json(['error' => 'unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        /**
         * Validate password hash
         */
        if (! Hash::check($request->get('password'), $candidate->password)) {
            return response()->json(['error' => 'unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        /**
         * Generate token
         */
        $tokenData = [
            'candidate'   => $candidate,
            'valid_until' => Carbon::now()->addMinutes(config('mycerts.session_lifetime_in_minutes'))
        ];
        $jwt = JWT::encode($tokenData, env('JWT_SECRET'),'HS256', Hash::make($candidate->password));
        return response()->json(['token' => $jwt], Response::HTTP_OK);
    }
}