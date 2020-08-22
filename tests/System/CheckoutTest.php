<?php

namespace MyCertsTests\System;

use Illuminate\Http\Response;
use MyCertsTests\TestCase;

class CheckoutTest extends TestCase
{
    public function test_should_successfully_create_company_and_user_on_checkout()
    {
        $plan = $this->json('GET', '/api/plans')->response->getOriginalContent()->first();

        $this->json(
            'post',
            '/checkout',
            [
                "company" => [
                    "name"    => $this->faker->company,
                    "country" => $this->faker->countryCode,
                ],
                "user"    => [
                    "email"            => $this->faker->email,
                    "password"         => $this->faker->password,
                    "confirm_password" => $this->faker->password,
                    "first_name"       => $this->faker->firstName,
                    "last_name"        => $this->faker->lastName,
                ],
                "payment" => [
                    "plan_id"   => $plan->id,
                    "number"    => "4242424242424242",
                    "cvc"       => "314",
                    "exp_month" => "10",
                    "exp_year"  => "2021",
                ]
            ]
        );

        $this->assertResponseStatus(Response::HTTP_CREATED);
    }
}