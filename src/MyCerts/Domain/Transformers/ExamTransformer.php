<?php

namespace MyCerts\Domain\Transformers;

use League\Fractal\TransformerAbstract;
use MyCerts\Domain\Model\Exam;

class ExamTransformer extends TransformerAbstract
{
    /**
     * @param Exam $exam
     * @return array
     */
    public function transform(Exam $exam)
    {
        return [
            'id'       => $exam->id,
            'title'       => $exam->title,
            'description' => $exam->description,
            'meta' => [
                'created_at' => $exam->created_at,
                'updated_at' => $exam->updated_at
            ]
        ];
    }
}