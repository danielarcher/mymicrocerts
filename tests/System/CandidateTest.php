<?php

namespace MyCertsTests\System;

use MyCertsTests\TestCase;

class CandidateTest extends TestCase
{
    public function test_it_should_create()
    {
        $name = $this->faker->firstName;
        $pass = $this->faker->password;
        $this->json('POST', '/api/candidate', [
            "email"            => $this->faker->email,
            "password"         => $pass,
            "confirm_password" => $pass,
            "first_name"       => $name,
            "last_name"        => $this->faker->lastName,
        ], ['Authorization' => $this->companyToken()]);

        $this->assertResponseCreated();
        $this->seeInDatabase('candidate', ['first_name' => $name, 'deleted_at' => null]);
    }

    public function test_it_should_create_with_custom()
    {
        $name = $this->faker->firstName;
        $pass = $this->faker->password;
        $this->json('POST', '/api/candidate', [
            "email"            => $this->faker->email,
            "password"         => $pass,
            "confirm_password" => $pass,
            "first_name"       => $name,
            "last_name"        => $this->faker->lastName,
            "custom"           => [
                'myattr' => 123
            ]
        ], ['Authorization' => $this->companyToken()]);

        $this->assertResponseCreated();
        $this->seeInDatabase('candidate', ['first_name' => $name, 'custom->myattr' => 123, 'deleted_at' => null]);
    }

    public function test_it_should_update_with_custom()
    {
        $name = $this->faker->firstName;
        $pass = $this->faker->password;
        $this->json('POST', '/api/candidate', [
            "email"            => $this->faker->email,
            "password"         => $pass,
            "confirm_password" => $pass,
            "first_name"       => $name,
            "last_name"        => $this->faker->lastName,
            "custom"           => [
                'myattr' => 123
            ]
        ], ['Authorization' => $this->companyToken()]);
        $candidate = $this->response->getOriginalContent();

        $this->json('PATCH', '/api/candidate/'.$candidate->id, [
            "custom"           => [
                'myattr' => 321
            ]
        ], ['Authorization' => $this->companyToken()]);

        $this->assertResponseOk();
        $this->seeInDatabase('candidate', ['first_name' => $name, 'custom->myattr' => 321, 'deleted_at' => null]);
    }

    public function test_it_should_delete()
    {
        $name = $this->faker->firstName;
        $pass = $this->faker->password;
        $this->json('POST', '/api/candidate', [
            "email"            => $this->faker->email,
            "password"         => $pass,
            "confirm_password" => $pass,
            "first_name"       => $name,
            "last_name"        => $this->faker->lastName,
        ], ['Authorization' => $this->companyToken()]);
        
        $candidate = $this->response->getOriginalContent();

        $this->json('DELETE', '/api/candidate/'.$candidate->id, [], ['Authorization' => $this->companyToken()]);
        $this->assertResponseNoContent();
        $this->notSeeInDatabase('candidate', ['first_name' => $name, 'deleted_at' => null]);
    }

}