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

    public function test_valid_error_response_structure_for_invalid_credentials()
    {
        $this->json('POST', '/login', [
            'email'    => $this->faker->email,
            'password' => $this->faker->password
        ]);

        $this->assertErrorStructure();
    }

    public function test_valid_error_response_structure_for_missing_fields()
    {
        $this->json('POST', '/login', [
            'email'    => $this->faker->email
        ]);

        $this->assertValidationErrorStructure();

        $this->json('POST', '/login', [
            'password'    => $this->faker->password
        ]);

        $this->assertValidationErrorStructure();
    }
}