<?php

namespace MyCerts\UI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use MyCerts\Domain\Model\Category;
use MyCerts\Domain\Model\Option;
use MyCerts\Domain\Model\Question;
use Ramsey\Uuid\Uuid;

class QuestionController extends Controller
{
    public function list()
    {
        if (Auth::user()->isAdmin()) {
            return response()->json(Question::with('options')->get()->makeVisible('company_id'));
        }
        return response()->json(Question::with('options')->where(['company_id' => Auth::user()->company_id])->paginate());
    }

    public function create(Request $request)
    {
        try {
            if (Auth::user()->isAdmin()) {
                return response()->json(['error' => 'Only Company owners can create questions'],
                    Response::HTTP_FORBIDDEN);
            }
            $question = new Question(array_filter([
                'company_id'  => Auth::user()->company_id,
                'number'      => $request->get('number'),
                'description' => $request->get('description'),
            ]));

            $question->save();
            $question->categories()->sync($request->get('categories'));

            $options = $request->get('options');
            array_walk($options, function (&$answer) use ($question) {
                $option = new Option([
                    'question_id' => $question->id,
                    'text'        => $answer['text'],
                    'correct'     => $answer['correct'] ?? false,
                ]);
                $option->save();
            });
            return response()->json($question, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response($e->getMessage());
        }
    }

    public function findOne($id)
    {
        return response()->json(Question::find($id));
    }

    public function delete($id)
    {
        if (!Question::find($id)) {
            return response()->json(['error' => 'Entity not found'], Response::HTTP_NOT_FOUND);
        }
        Question::find($id)->options()->delete();
        Question::destroy($id);
        return response('',Response::HTTP_NO_CONTENT);
    }
}