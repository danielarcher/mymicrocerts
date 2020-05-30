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
            'title'       => $exam->title,
            'description' => $exam->description,
        ];
    }
}