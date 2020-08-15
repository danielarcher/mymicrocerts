<?php

namespace MyCertsTests\System;

use Illuminate\Http\Response;
use MyCertsTests\TestCase;

class CompanyTest extends TestCase
{
    public function test_create_new_company ()
    {
        $this->json('POST', '/api/company', [
            "name"         => $this->faker->name,
            "country"      => $this->faker->countryCode,
            "email"        => $this->faker->companyEmail,
            "contact_name" => $this->faker->name,
        ], ['Authorization' => $this->adminToken()]);

        $this->assertResponseStatus(Response::HTTP_CREATED);
    }
}