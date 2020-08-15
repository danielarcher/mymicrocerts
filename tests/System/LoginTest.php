<?php

namespace MyCertsTests\System;

use Illuminate\Http\Response;
use MyCertsTests\TestCredentials;
use MyCertsTests\TestCase;

class LoginTest extends TestCase
{
    public function test_login_successful ()
    {
        $this->json('POST', '/login', [
            'email'    => TestCredentials::ADMIN_EMAIL,
            'password' => TestCredentials::ADMIN_PASSWORD
        ]);

        $this->assertResponseOk();
    }

    public function test_failed_login ()
    {
        $this->json('POST', '/login', [
            'email'    => $this->faker->email,
            'password' => $this->faker->password
        ]);

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }
}