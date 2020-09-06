<?php

namespace MyCerts\Domain\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

/**
 * @property string id
 * @property string created_at
 * @property string updated_at
 * @property string deleted_at
 **/
class BaseModel extends Model
{
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $hidden = ['created_at','updated_at','deleted_at'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function (Model $model) {
            $model->setAttribute($model->getKeyName(), Uuid::uuid4());
        });
    }
}