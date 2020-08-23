<?php

namespace MyCerts\UI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MyCerts\Domain\Model\Company;

abstract class BaseController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Company
     */
    public function retrieveCompany(Request $request): Company
    {
        if (!Auth::user()->isAdmin()) {
            return Auth::user()->company()->first();
        }

        if ($request->json('company_id')) {
            return Company::find($request->json('company_id'));
        }

        return Auth::user()->company()->first();
    }
}