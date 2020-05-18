<?php

namespace MyCerts\Domain\Model;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class BaseModel extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $hidden = ['created_at','updated_at'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function (Model $model) {
            $model->setAttribute($model->getKeyName(), Uuid::uuid4());
        });
    }
}