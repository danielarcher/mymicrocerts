<?php

namespace MyCertsTests\System;

use Illuminate\Http\Response;
use MyCertsTests\RetrieveTokenTrait;
use MyCertsTests\TestCase;

class BuyCreditsTest extends TestCase
{
    use RetrieveTokenTrait;

    public function test_it_should_buy_credits()
    {
        $response = json_decode($this->json('GET', '/api/plans')->response->content(), true);
        $planId = $response['data']['0']['id'];
        $this->json('POST', "/api/plans/{$planId}/buy", $this->faker->creditCardDetails, ['Authorization' => $this->companyToken()]);

        $response = json_decode($this->response->content(), true);

        $this->assertResponseStatus(Response::HTTP_CREATED);
        $this->seeInDatabase('contract', $response);
    }
}