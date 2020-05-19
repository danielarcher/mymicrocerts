<?php

namespace MyCerts\Domain\Model;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Laravel\Lumen\Auth\Authorizable;
use MyCerts\Domain\Roles;

class Candidate extends BaseModel implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    protected $table = 'candidate';

    protected $fillable = [
        'company_id',
        'email',
        'password',
        'first_name',
        'last_name',
        'active',
        'role',
    ];

    protected $hidden = ['created_at','updated_at', 'active', 'password', 'role', 'verified', 'company_id'];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'candidate_id');
    }

    public function isAdmin()
    {
        return $this->role === Roles::ADMIN;
    }

    public function isCompanyOwner()
    {
        return $this->role === Roles::COMPANY;
    }
}