<?php

namespace MyCertsTests;

trait RetrieveTokenTrait
{
    public function adminToken ()
    {
        $this->json('POST', '/login', [
            'email'    => AdminCredentials::EMAIL,
            'password' => AdminCredentials::PASSWORD
        ]);

        return 'Bearer '.$this->response->json('token');
    }
}