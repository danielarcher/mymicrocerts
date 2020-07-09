<?php

namespace MyCerts\Domain\Model;

/**
 * @property int|mixed credits_used
 * @property mixed     credits_total
 * @property bool      active
 * @property string     company_id
 */
class Contract extends BaseModel
{
    protected $table = 'contract';

    protected $fillable = ['company_id', 'name','description','price', 'credits_total', 'credits_used'];

    protected $hidden = ['updated_at', 'active'];

    public function creditsAvailable()
    {
        return $this->credits_total - $this->credits_used;
    }

    public function hasCredits()
    {
        return $this->creditsAvailable() > 0;
    }

    public function subtractCredit(): self
    {
        $this->credits_used = $this->credits_used + 1;
        return $this;
    }

    public function inactivate(): self
    {
        $this->active = false;
        return $this;
    }

}