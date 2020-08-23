<?php

namespace MyCerts\Application;

use Cartalyst\Stripe\Stripe;
use MyCerts\Domain\Exception\TransactionDeclinedException;
use MyCerts\Domain\Model\Company;
use MyCerts\Domain\Model\Contract;
use MyCerts\Domain\Model\Plan;

/**
 * Class PaymentHandler
 *
 * @package MyCerts\Application
 */
class PaymentHandler
{
    /**
     * @var Stripe
     */
    private Stripe $stripe;

    /**
     * PaymentHandler constructor.
     *
     * @param Stripe $stripe
     */
    public function __construct(Stripe $stripe)
    {
        $this->stripe = $stripe;
    }

    /**
     * @param Plan   $plan
     * @param string $companyId
     *
     * @return Contract
     */
    public function freeCharge(Plan $plan, string $companyId): Contract
    {
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

    /**
     * @param string      $planId
     * @param string      $companyId
     * @param string|null $stripeCustomerId
     * @param string|null $number
     * @param int|null    $cvc
     * @param int|null    $expMonth
     * @param int|null    $expYear
     *
     * @return Contract
     * @throws TransactionDeclinedException
     */
    public function charge(
        string $planId,
        string $companyId,
        ?string $stripeCustomerId,
        ?string $number,
        ?int $cvc,
        ?int $expMonth,
        ?int $expYear
    ): Contract {
        $plan = Plan::find($planId);

        if ($plan->price == 0) {
            return $this->freeCharge($plan, $companyId);
        }

        /**
         * Generate credit card token (payment template)
         */
        $token = $this->stripe->tokens()->create([
            'card' => [
                'number'    => $number,
                'exp_month' => $expMonth,
                'cvc'       => $cvc,
                'exp_year'  => $expYear,
            ],
        ]);

        /**
         * Add a credit card to user, using the token
         */
        $this->stripe->cards()->create($stripeCustomerId, $token['id']);

        /**
         * Charge the user
         */
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