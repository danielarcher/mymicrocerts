<?php

namespace MyCerts\Application;

use Illuminate\Support\Facades\Hash;
use MyCerts\Domain\Model\Candidate;

class CandidateHandler
{
    public function create(
        string $company_id,
        string $email,
        string $password,
        string $first_name,
        string $last_name,
        string $role
    ) {
        $entity = new Candidate(array_filter([
            'company_id' => $company_id,
            'email'      => $email,
            'password'   => Hash::make($password),
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'role'       => $role,
        ]));

        $entity->save();

        return $entity;
    }
}