<?php

namespace MyCerts\Domain\Model;

class Certificate extends BaseModel
{
    protected $table = 'certificate';

    protected $fillable = [
        'exam_id',
        'candidate_id',
        'score_in_percent',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }
}