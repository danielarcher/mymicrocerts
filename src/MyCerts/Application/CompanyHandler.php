<?php

namespace MyCerts\Application;

use Cartalyst\Stripe\Stripe;
use MyCerts\Domain\Model\Company;

/**
 * Class Company
 *
 * @package MyCerts\Application
 */
class CompanyHandler
{
    /**
     * @var Stripe
     */
    private Stripe $stripe;

    public function __construct(Stripe $stripe)
    {
        $this->stripe = $stripe;
    }

    /**
     * @param string $name
     * @param string $country
     * @param string $email
     * @param string $contactName
     *
     * @return Company
     */
    public function create(string $name, string $country, string $email, string $contactName): Company
    {
        $company = new Company([
            'name'         => $name,
            'country'      => $country,
            'email'        => $email,
            'contact_name' => $contactName,
        ]);
        $company->save();

        $customer = $this->stripe->customers()->create([
            'name'  => $company->name,
            'email' => $company->email,
        ]);

        $company->stripe_customer_id = $customer['id'];
        $company->save();

        return $company;
    }
}