<?php

namespace MyCerts\UI;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use MyCerts\Application\QuestionHandler;
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

    public function list(Request $request)
    {
        $company = $this->retrieveCompany($request);

        if (Auth::user()->isAdmin()) {
            return response()->json(Question::with('options')->get()->makeVisible('company_id'));
        }
        return response()->json(Question::with('options')
            ->where(['company_id' => $company->id])
            ->paginate());
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'description'       => 'required|string',
            'categories'        => 'array',
            'categories.*'      => 'string',
            'options'           => 'array',
            'options.*'         => 'array',
            'options.*.text'    => 'required_with:options.*|string',
            'options.*.correct' => 'boolean',
            'number'            => 'int',
        ]);

        $question = $this->handler->create(
            $this->retrieveCompany($request)->id,
            $request->json('description'),
            $request->json('categories'),
            $request->json('options'),
            $request->json('number')
        );

        return response()->json(Question::with(['options', 'categories'])->find($question->id), Response::HTTP_CREATED);
    }

    public function patch(string $id, Request $request)
    {
        $this->validate($request, [
            'description'       => 'string',
            'categories'        => 'array',
            'categories.*'      => 'string',
            'options'           => 'array',
            'options.*'         => 'array',
            'options.*.text'    => 'required_with:options.*|string',
            'options.*.correct' => 'boolean',
            'number'            => 'int',
        ]);

        $question = $this->handler->update(
            $this->retrieveCompany($request)->id,
            $id,
            $request->json('description'),
            $request->json('categories'),
            $request->json('options'),
            $request->json('number')
        );

        return response()->json(Question::with(['options', 'categories'])->find($question->id));
    }

    public function findOne($id)
    {
        return response()->json(Question::with(['options', 'categories'])->find($id));
    }

    public function delete(string $id, Request $request)
    {
        $this->handler->delete($this->retrieveCompany($request)->id, $id);

        return response('', Response::HTTP_NO_CONTENT);
    }
}