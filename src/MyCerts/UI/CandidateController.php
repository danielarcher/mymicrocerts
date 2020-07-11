<?php

namespace MyCerts\UI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use MyCerts\Domain\Model\Candidate;
use MyCerts\Domain\Roles;

class CandidateController extends Controller
{
    public function list()
    {
        $nonAdminRestriction = Auth::user()->isAdmin() ? [] : ['company_id'=>Auth::user()->company_id];

        return response()->json(Candidate::where($nonAdminRestriction)->paginate(self::DEFAULT_PAGINATION_LENGHT));
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'confirm_password' => 'required',
            'super_user'       => 'bool',
            'company_id'       => 'required|uuid',
            'password'         => 'required',
            'first_name'       => 'required',
            'last_name'        => 'required',
            'email'            => 'required|email|unique:candidate'
        ]);

        $user = Auth::user();
        if ($request->get('password') !== $request->get('confirm_password')) {
            return response()->json(['error'=>'password do not match'], Response::HTTP_BAD_REQUEST);
        }

        $role = Roles::CANDIDATE;
        if ($user->role == Roles::ADMIN) {
            $role = $request->get('super_user') ? Roles::COMPANY : Roles::CANDIDATE;
        }

        $entity = new Candidate(array_filter([
            'company_id' => $request->get('company_id'),
            'email'      => $request->get('email'),
            'password'   => Hash::make($request->get('password')),
            'first_name' => $request->get('first_name'),
            'last_name'  => $request->get('last_name'),
            'role'       => $role,
        ]));
        $entity->save();

        return response()->json($entity, Response::HTTP_CREATED);
    }

    public function createGuest(Request $request)
    {
        if ($request->get('password') !== $request->get('confirm_password')) {
            return response()->json(['error'=>'password do not match'], Response::HTTP_BAD_REQUEST);
        }

        $entity = new Candidate(array_filter([
            'email'      => $request->get('email'),
            'password'   => Hash::make($request->get('password')),
            'first_name' => $request->get('first_name'),
            'last_name'  => $request->get('last_name'),
            'active'     => $request->get('active'),
        ]));
        $entity->save();

        return response()->json($entity, Response::HTTP_CREATED);
    }

    public function findOne($id)
    {
        return response()->json(Candidate::with('certificates')->find($id));
    }

    public function findMe()
    {
        return response()->json(Candidate::with('certificates')->find(Auth::user()->id));
    }

    public function delete($id)
    {
        if (!Candidate::find($id)) {
            return response()->json(['error' => 'Entity not found'], Response::HTTP_NOT_FOUND);
        }
        Candidate::find($id)->certificates()->delete();
        Candidate::destroy($id);
        return response('',Response::HTTP_NO_CONTENT);
    }
}