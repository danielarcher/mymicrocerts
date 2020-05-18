<?php

namespace MyCerts\UI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MyCerts\Domain\Model\Option;
use MyCerts\Domain\Model\Question;
use Ramsey\Uuid\Uuid;

class QuestionController extends Controller
{
    public function list()
    {
        return response()->json(Question::with('options')->get());
    }

    public function create(Request $request)
    {
        $question = new Question(array_filter([
            'exam_id'     => $request->get('exam_id'),
            'number'      => $request->get('number'),
            'description' => $request->get('description'),
        ]));
        $question->save();

        $options = $request->get('options');
        array_walk($options, function(&$answer) use ($question) {
            $option = new Option([
                'question_id' => $question->id,
                'text' => $answer['text'],
                'correct' => $answer['correct'] ?? false,
            ]);
            $option->save();
        });

        return response()->json($question, Response::HTTP_CREATED);
    }

    public function findOne($id)
    {
        return response()->json(Question::find($id));
    }
}