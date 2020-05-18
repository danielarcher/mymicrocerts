<?php

namespace MyCerts\UI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MyCerts\Domain\Model\Plan;

class PlansController extends Controller
{
    public function list()
    {
        return response()->json(Plan::where('active', true)->get());
    }

    public function create(Request $request)
    {
        $plan = new Plan([
            'name'            => $request->get('name'),
            'description'     => $request->get('description'),
            'price'           => $request->get('price'),
            'max_users'       => $request->get('max_users'),
            'exams_per_month' => $request->get('exams_per_month'),
        ]);
        $plan->save();

        return response()->json($plan, Response::HTTP_CREATED);
    }

    public function findOne($id)
    {
        return response()->json(Plan::find($id));
    }
}