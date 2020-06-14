<?php

namespace MyCerts\UI\Request;

use Illuminate\Http\Request;

class ExamCreateRequest extends Request
{
    public function rules()
    {
        return [
            'company_id'                 => 'required|uuid',
            'title'                      => 'required|unique:exam|string',
            'description'                => 'required|string',
            'max_time_in_minutes'        => 'required|int',
            'success_score_in_percent'   => 'required|int',
            'max_attempts_per_candidate' => 'int',
            'visible_internal'           => 'bool',
            'visible_external'           => 'bool',
            'private'                    => 'string',
        ];
    }
}