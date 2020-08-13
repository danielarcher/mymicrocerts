<?php

namespace MyCerts\Domain\Model;

use Illuminate\Database\Eloquent\Model;
use MyCerts\Domain\Exception\NoCreditsLeft;
use Ramsey\Uuid\Uuid;

class Company extends BaseModel
{
    protected $table = 'company';

    protected $fillable = ['name','country', 'email', 'contact_name'];

    protected $hidden = ['created_at','updated_at','deleted_at','email','contact_name'];

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'company_id');
    }
    public function questions()
    {
        return $this->hasMany(Question::class, 'company_id');
    }
    public function candidates()
    {
        return $this->hasMany(Candidate::class, 'company_id');
    }
    public function exams()
    {
        return $this->hasMany(Exam::class, 'company_id');
    }

    public function oldestActiveContract(): ?Contract
    {
        return Contract::where('company_id', $this->id)
            ->where('active', true)
            ->orderBy('created_at', 'asc')
            ->first();
    }

    public function hasCredits(): bool
    {
        if (empty($this->oldestActiveContract())) {
            return false;
        }
        return $this->oldestActiveContract()->hasCredits();
    }

    /**
     * @return bool
     * @throws NoCreditsLeft
     */
    public function useCredit(): bool
    {
        $contract = $this->oldestActiveContract();
        if ($contract->hasCredits()) {
            $contract->subtractCredit();
            /**
             * If contract dont have credits left, inactivate it
             */
            if (!$contract->hasCredits()) {
                $contract->inactivate()->save();
            }
            return $contract->save();
        }
        throw new NoCreditsLeft();
    }
}