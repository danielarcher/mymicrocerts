<?php

namespace MyCerts\UI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use MyCerts\Domain\Model\Contract;
use MyCerts\Domain\Model\Plan;

class PlansController extends Controller
{
    public function list()
    {
        return response()->json(Plan::where('active', true)->orderBy('price','asc')->get());
    }

    public function create(Request $request)
    {
        $plan = new Plan([
            'name'                  => $request->get('name'),
            'description'           => $request->get('description'),
            'price'                 => $request->get('price'),
            'credits'     => $request->get('credits'),
            'api_requests_per_hour' => $request->get('api_requests_per_hour'),
        ]);
        $plan->save();

        return response()->json($plan, Response::HTTP_CREATED);
    }

    public function buy($id, Request $request)
    {
        $plan     = Plan::find($id);
        $contract = new Contract([
            'name'          => $plan->name,
            'description'   => $plan->description,
            'price'         => $plan->price,
            'credits_total' => $plan->credits,
            'company_id'    => Auth::user()->company_id,
        ]);
        if (Auth::user()->isAdmin()) {
            $contract->company_id = $request->get('company_id');
        }
        $contract->save();

        return response()->json($contract, Response::HTTP_CREATED);
    }

    public function findOne($id)
    {
        return response()->json(Plan::find($id));
    }

    public function delete($id)
    {
        Plan::destroy($id);
        return response('', Response::HTTP_NO_CONTENT);
    }
}