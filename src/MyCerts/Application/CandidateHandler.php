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
        string $role,
        ?array $custom
    ) {
        $entity = new Candidate(array_filter([
            'company_id' => $company_id,
            'email'      => $email,
            'password'   => Hash::make($password),
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'role'       => $role,
            'custom'     => $custom,
        ]));

        $entity->save();

        return $entity;
    }

    public function patch(
        string $candidate_id,
        string $company_id,
        ?string $email,
        ?string $password,
        ?string $first_name,
        ?string $last_name,
        ?array $custom
    ) {
        $candidate = Candidate::where(['id' => $candidate_id, 'company_id' => $company_id])->firstOrFail();
        $candidate->fill(array_filter([
            'company_id' => $company_id,
            'email'      => $email,
            'password'   => Hash::make($password),
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'custom'     => $custom,
        ]));
        $candidate->save();

        return $candidate;
    }
}