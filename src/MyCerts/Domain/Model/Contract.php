<?php

namespace MyCerts\Domain\Model;

class Contract extends BaseModel
{
    protected $table = 'contract';

    protected $fillable = ['company_id', 'name','description','price', 'credits_total', 'credits_used'];

    protected $hidden = ['updated_at', 'active'];

    public function creditsAvailable()
    {
        return $this->credits_total - $this->credits_used;
    }
}