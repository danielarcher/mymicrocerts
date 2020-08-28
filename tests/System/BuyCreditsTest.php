<?php

namespace MyCertsTests\System;

use Illuminate\Http\Response;
use MyCertsTests\TestCase;

class BuyCreditsTest extends TestCase
{
    public function test_it_should_buy_credits()
    {
        /**
         * get plan
         */
        $this->json('GET', '/api/plans')->response->content();
        $plan = $this->response->getOriginalContent()->first();

        $this->json('POST', "/api/plans/{$plan->id}/buy", $this->faker->creditCardDetails, ['Authorization' => $this->companyToken()]);

        $this->assertResponseStatus(Response::HTTP_CREATED);
    }
}