<?php

namespace MyCerts\Domain\Model;

/**
 * @property mixed exam_id
 * @property mixed candidate_id
 * @property mixed score_in_percent
 */
class Certificate extends BaseModel
{
    protected $table = 'certificate';

    protected $guarded = [];

    protected $casts = [
        'rewards' => 'json'
    ];

    protected $hidden = ['deleted_at','updated_at','exam_id'];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }
}