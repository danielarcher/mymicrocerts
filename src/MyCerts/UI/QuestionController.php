<?php

namespace MyCerts\UI;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use MyCerts\Application\QuestionHandler;
use MyCerts\Domain\Model\Option;
use MyCerts\Domain\Model\Question;

class QuestionController extends BaseController
{
    /**
     * @var QuestionHandler
     */
    private QuestionHandler $handler;

    public function __construct(QuestionHandler $handler)
    {
        $this->handler = $handler;
    }

    public function list()
    {
        if (Auth::user()->isAdmin()) {
            return response()->json(Question::with('options')->get()->makeVisible('company_id'));
        }
        return response()->json(Question::with('options')
            ->where(['company_id' => Auth::user()->company_id])
            ->paginate());
    }

    public function create(Request $request)
    {
        $question = $this->handler->create(
            $this->retrieveCompany($request)->id,
            $request->json('description'),
            $request->json('categories'),
            $request->json('options'),
            $request->json('number')
        );

        return response()->json($question, Response::HTTP_CREATED);
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
        return response('', Response::HTTP_NO_CONTENT);
    }
}