<?php

namespace MyCerts\UI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MyCerts\Domain\Model\Company;

class CompaniesController extends Controller
{
    public function list()
    {
        return response()->json(Company::with('plans')->paginate(2));
    }

    public function create(Request $request)
    {
        $company = new Company([
            'name'     => $request->get('name'),
            'country' => $request->get('country'),
            'plan_id' => $request->get('plan_id'),
        ]);
        $company->save();

        return response()->json($company, Response::HTTP_CREATED);
    }

    public function findOne($id)
    {
        return response()->json(Company::with('plans')->findOrFail($id));
    }
}