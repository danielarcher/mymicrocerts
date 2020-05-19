<?php

namespace MyCerts\Domain\Model;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Company extends BaseModel
{
    protected $table = 'company';

    protected $fillable = ['name','country','email','contact_name'];

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'company_id');
    }
}