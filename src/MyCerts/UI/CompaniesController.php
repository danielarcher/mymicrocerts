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
        return response()->json(Company::with('contracts')->paginate(2));
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name'         => 'required',
            'country'      => 'required',
            'email'        => 'required|email',
            'contact_name' => 'required',
        ]);

        $company = new Company([
            'name'     => $request->get('name'),
            'country' => $request->get('country'),
            'email' => $request->get('email'),
            'contact_name' => $request->get('contact_name'),
        ]);
        $company->save();

        return response()->json($company, Response::HTTP_CREATED);
    }

    public function findOne($id)
    {
        return response()->json(Company::with('contracts')->find($id));
    }

    public function delete($id)
    {
        if (!Company::find($id)) {
            return response()->json(['error' => 'Entity not found'], Response::HTTP_NOT_FOUND);
        }
        foreach (Company::find($id)->questions()->get() as $question) {
            $question->options()->delete();
        }
        #Company::find($id)->questions()->options()->delete();
        Company::find($id)->questions()->delete();
        Company::find($id)->exams()->delete();
        Company::find($id)->candidates()->delete();
        Company::find($id)->contracts()->delete();
        Company::destroy($id);
        return response('',Response::HTTP_NO_CONTENT);
    }
}