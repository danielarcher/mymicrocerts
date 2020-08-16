<?php

namespace MyCerts\UI;

use App\Http\Controllers\Controller;
use Cartalyst\Stripe\Stripe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Http\ResponseFactory;
use MyCerts\Domain\Model\Company;
use MyCerts\Domain\Model\Contract;

/**
 * Class CompaniesController
 *
 * @package MyCerts\UI
 */
class CompaniesController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function list()
    {
        return response()->json(Company::with('contracts')->paginate(self::DEFAULT_PAGINATION_LENGHT));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'name'         => 'required',
            'country'      => 'required',
            'email'        => 'required|email',
            'contact_name' => 'required',
        ]);

        $company = new Company([
            'name'         => $request->json('name'),
            'country'      => $request->json('country'),
            'email'        => $request->json('email'),
            'contact_name' => $request->json('contact_name'),
        ]);
        $company->save();

        $customer = (new Stripe())->customers()->create([
            'name' => $company->name,
            'email' => $company->email,
        ]);

        $company->stripe_customer_id = $customer['id'];
        $company->save();

        return response()->json($company, Response::HTTP_CREATED);
    }

    /**
     * @param $id
     *
     * @return JsonResponse
     */
    public function findOne($id)
    {
        return response()->json(Company::find($id));
    }

    /**
     * @return JsonResponse
     */
    public function contracts()
    {
        return response()->json(Contract::where(['company_id'=>Auth::user()->company_id])->get());
    }

    /**
     * @param $id
     *
     * @return JsonResponse|Response|ResponseFactory
     */
    public function delete($id)
    {
        if (!Company::find($id)) {
            return response()->json(['error' => 'Entity not found'], Response::HTTP_NOT_FOUND);
        }
        foreach (Company::find($id)->questions()->get() as $question) {
            $question->options()->delete();
        }
        Company::find($id)->questions()->delete();
        Company::find($id)->exams()->delete();
        Company::find($id)->candidates()->delete();
        Company::find($id)->contracts()->delete();
        Company::destroy($id);
        return response('', Response::HTTP_NO_CONTENT);
    }
}