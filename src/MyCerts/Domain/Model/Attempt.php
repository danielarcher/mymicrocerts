<?php

namespace MyCerts\Domain\Model;

class Attempt extends BaseModel
{
    protected $table = 'attempt';

    protected $fillable = [
        'exam_id',
        'candidate_id',
        'score_in_percent',
        'score_absolute',
        'finished_at',
        'approved',
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