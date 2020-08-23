<?php

namespace MyCerts\UI;

use App\Http\Controllers\Controller;
use Cartalyst\Stripe\Stripe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Http\ResponseFactory;
use MyCerts\Application\PaymentHandler;
use MyCerts\Domain\Exception\TransactionDeclinedException;
use MyCerts\Domain\Model\Candidate;
use MyCerts\Domain\Model\Company;
use MyCerts\Domain\Model\Contract;
use MyCerts\Domain\Model\Plan;

/**
 * Class PlansController
 *
 * @package MyCerts\UI
 */
class PlansController extends BaseController
{

    /**
     * @var PaymentHandler
     */
    private PaymentHandler $paymentHandler;

    public function __construct(PaymentHandler $paymentHandler)
    {
        $this->paymentHandler = $paymentHandler;
    }

    /**
     * @return JsonResponse
     */
    public function list()
    {
        $plans = Cache::get('plans', function() {
            return Plan::where('active', true)->orderBy('price', 'asc')->paginate(self::DEFAULT_PAGINATION_LENGHT);
        });

        return response()->json($plans);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $plan = new Plan([
            'name'                  => $request->json('name'),
            'description'           => $request->json('description'),
            'price'                 => $request->json('price'),
            'credits'               => $request->json('credits'),
            'api_requests_per_hour' => $request->json('api_requests_per_hour'),
        ]);
        $plan->save();

        return response()->json($plan, Response::HTTP_CREATED);
    }

    /**
     * @param string  $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws TransactionDeclinedException
     * @throws ValidationException
     */
    public function buy(string $id, Request $request)
    {
        $plan = Plan::find($id);

        $this->validate($request, [
            'number'    => 'string',
            'exp_month' => 'integer',
            'cvc'       => 'integer',
            'exp_year'  => 'integer',
        ]);

        $company = $this->retrieveCompany($request);

        $contract = $this->paymentHandler->charge(
            $plan->id,
            $company->id,
            $company->stripe_customer_id,
            $request->json('number'),
            $request->json('cvc'),
            $request->json('exp_month'),
            $request->json('exp_year')
        );

        return response()->json($contract, Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param Company $company
     * @param float   $price
     *
     * @return void
     * @throws TransactionDeclinedException
     * @throws ValidationException
     */
    public function chargeUser(Request $request, Company $company, float $price): void
    {
        $this->validate($request, [
            'number'    => 'required|string',
            'exp_month' => 'required|integer',
            'cvc'       => 'required|integer',
            'exp_year'  => 'required|integer',
        ]);

        $stripe = new Stripe();
        /** @var Candidate $user */

        $token = $stripe->tokens()->create([
            'card' => [
                'number'    => $request->json('number'),
                'exp_month' => $request->json('exp_month'),
                'cvc'       => $request->json('cvc'),
                'exp_year'  => $request->json('exp_year'),
            ],
        ]);

        $stripe->cards()->create(Auth::user()->company()->first()->stripe_customer_id, $token['id']);

        # create a charge
        $charge = $stripe->charges()->create([
            'customer' => Auth::user()->company()->first()->stripe_customer_id,
            'currency' => 'USD',
            'amount'   => (float) $price
        ]);

        /**
         * If transaction fails
         */
        if ($charge['status'] !== 'succeeded' || $charge['paid'] !== true) {
            throw new TransactionDeclinedException();
        }
    }

    public function createContract(Plan $plan, Company $company): Contract
    {
        $contract = new Contract([
            'name'          => $plan->name,
            'description'   => $plan->description,
            'price'         => $plan->price,
            'credits_total' => $plan->credits,
            'company_id'    => $company->id,
        ]);

        $contract->save();

        return $contract;
    }

    /**
     * @param $id
     *
     * @return JsonResponse
     */
    public function findOne($id): JsonResponse
    {
        return response()->json(Plan::find($id));
    }

    /**
     * @param $id
     *
     * @return Response|ResponseFactory
     */
    public function delete($id)
    {
        Plan::destroy($id);

        return response('', Response::HTTP_NO_CONTENT);
    }
}