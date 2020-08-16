<?php

namespace MyCertsTests\System;

use Illuminate\Http\Response;
use MyCertsTests\TestCase;
use MyCertsTests\TestCredentials;

class LoginTest extends TestCase
{
    public function test_login_successful()
    {
        $this->json('POST', '/login', [
            'email'    => TestCredentials::ADMIN_EMAIL,
            'password' => TestCredentials::ADMIN_PASSWORD
        ]);

        $this->assertResponseOk();
    }

    public function test_failed_login()
    {
        $this->json('POST', '/login', [
            'email'    => $this->faker->email,
            'password' => $this->faker->password
        ]);

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_valid_error_response_structure()
    {
        $this->json('POST', '/login', [
            'email'    => $this->faker->email,
            'password' => $this->faker->password
        ]);

        $expectedStructure = [
            'errors' => [
                [
                    'description',
                    'code'
                ]
            ]
        ];
        $returnArray = json_decode($this->response->content(), true);
        $this->assertEquals($expectedStructure, $this->array_keys_recursive($returnArray));
    }
}