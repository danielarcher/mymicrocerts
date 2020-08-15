<?php

namespace MyCertsTests;

trait RetrieveTokenTrait
{
    public function adminToken ()
    {
        $this->json('POST', '/login', [
            'email'    => TestCredentials::ADMIN_EMAIL,
            'password' => TestCredentials::ADMIN_PASSWORD
        ]);

        return 'Bearer '.$this->response->json('token');
    }
    public function companyToken ()
    {
        $this->json('POST', '/login', [
            'email'    => TestCredentials::COMPANY_EMAIL,
            'password' => TestCredentials::COMPANY_PASSWORD
        ]);

        return 'Bearer '.$this->response->json('token');
    }
    public function userToken ()
    {
        $this->json('POST', '/login', [
            'email'    => TestCredentials::USER_EMAIL,
            'password' => TestCredentials::USER_PASSWORD
        ]);

        return 'Bearer '.$this->response->json('token');
    }
    public function guestToken ()
    {
        $this->json('POST', '/login', [
            'email'    => TestCredentials::GUEST_EMAIL,
            'password' => TestCredentials::GUEST_PASSWORD
        ]);

        return 'Bearer '.$this->response->json('token');
    }
}