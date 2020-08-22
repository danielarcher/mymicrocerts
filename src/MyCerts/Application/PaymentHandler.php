<?php

namespace MyCerts\Application;

use Cartalyst\Stripe\Stripe;
use MyCerts\Domain\Exception\TransactionDeclinedException;
use MyCerts\Domain\Model\Contract;
use MyCerts\Domain\Model\Plan;

class PaymentHandler
{
    /**
     * @var Stripe
     */
    private Stripe $stripe;

    public function __construct(Stripe $stripe)
    {
        $this->stripe = $stripe;
    }

    public function charge(
        string $planId,
        string $companyId,
        string $stripeCustomerId,
        string $number,
        int $cvc,
        int $expMonth,
        int $expYear
    ) {
        $plan = Plan::find($planId);

        if ($plan->price > 1) {
            $token = $this->stripe->tokens()->create([
                'card' => [
                    'number'    => $number,
                    'exp_month' => $expMonth,
                    'cvc'       => $cvc,
                    'exp_year'  => $expYear,
                ],
            ]);

            $this->stripe->cards()->create($stripeCustomerId, $token['id']);

            # create a charge
            $charge = $this->stripe->charges()->create([
                'customer' => $stripeCustomerId,
                'currency' => 'USD',
                'amount'   => (float) $plan->price
            ]);

            /**
             * If transaction fails
             */
            if ($charge['status'] !== 'succeeded' || $charge['paid'] !== true) {
                throw new TransactionDeclinedException();
            }
        }

        $contract = new Contract([
            'name'          => $plan->name,
            'description'   => $plan->description,
            'price'         => $plan->price,
            'credits_total' => $plan->credits,
            'company_id'    => $companyId,
        ]);

        $contract->save();

        return $contract;
    }
}