<?php

namespace MyCerts\UI;

use App\Http\Controllers\Controller;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use MyCerts\Domain\Model\Candidate;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->get('email');
        $pass = $request->get('password');
        $candidate = Candidate::where('email', $email)->first();
        if (!$candidate) {
            return response()->json(['error' => 'unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        if (! Hash::check($pass, $candidate->password)) {
            return response()->json(['error' => 'unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $jwt = JWT::encode(array($candidate), env('JWT_SECRET'),'HS256', Hash::make($candidate->password));
        return response()->json(['token' => $jwt], Response::HTTP_OK);
    }
}