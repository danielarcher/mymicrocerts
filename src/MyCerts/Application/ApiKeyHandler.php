<?php

namespace MyCerts\Application;

use MyCerts\Domain\Model\ApiKey;
use MyCerts\Domain\Model\Company;

class ApiKeyHandler
{
    public function list(string $companyId)
    {
        return ApiKey::where(['company_id' => $companyId])->get();
    }

    public function create(string $companyId, string $name)
    {
        $company = Company::find($companyId);
        /** @var ApiKey $apiKey */
        $apiKey = $company->apiKeys()->create(['name' => $name, 'key' => base64_encode(random_bytes(64))]);

        return $apiKey->makeVisible('key');
    }

    public function revoke(string $keyId, string $companyId)
    {
        return ApiKey::where(['id' => $keyId, 'company_id' => $companyId])->delete();
    }
}