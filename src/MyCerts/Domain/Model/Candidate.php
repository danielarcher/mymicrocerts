<?php

namespace MyCerts\Domain\Model;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Lumen\Auth\Authorizable;
use MyCerts\Domain\Roles;

/**
 * @property Certificate company_id
 * @property string      email
 * @property string      password
 * @property string      first_name
 * @property string      last_name
 * @property bool        active
 * @property string      role
 * @property bool        verified
 * @property HasMany     certificates
 * @property BelongsTo     company
 */
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

    public function hasCertificateFor(string $examId)
    {
        foreach ($this->certificates as $cert) {
            if ($cert->exam_id == $examId) {
                return true;
            }
        }
        return false;
    }
}