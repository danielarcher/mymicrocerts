<?php

namespace MyCerts\UI;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Http\ResponseFactory;
use MyCerts\Application\ExamHandler;
use MyCerts\Domain\Certification;
use MyCerts\Domain\ExamValidator;
use MyCerts\Domain\Exception\ExamAlreadyFinished;
use MyCerts\Domain\Exception\NoCreditsLeft;
use MyCerts\Domain\Model\Candidate;
use MyCerts\Domain\Model\Exam;

/**
 * Class ExamController
 *
 * @package MyCerts\UI
 */
class ExamController extends BaseController
{
    /**
     * @var ExamHandler
     */
    private ExamHandler $handler;

    public function __construct(ExamHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        $company = $this->retrieveCompany($request);
        return response()->json(Exam::where(['company_id' => $company->id])->paginate(self::DEFAULT_PAGINATION_LENGHT));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse|Response|ResponseFactory
     * @throws ValidationException
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'title'                      => 'required|unique:exam|string',
            'description'                => 'required|string',
            'success_score_in_percent'   => 'required|int',
            'company_id'                 => 'uuid',
            'max_time_in_minutes'        => 'int',
            'max_attempts_per_candidate' => 'int',
            'visible_internal'           => 'bool',
            'visible_external'           => 'bool',
            'private'                    => 'bool',

            'questions_per_categories'                         => 'array',
            'questions_per_categories.*.category_id'           => 'required_with:questions_per_categories.*|string',
            'questions_per_categories.*.quantity_of_questions' => 'required_with:questions_per_categories.*|int',

            'fixed_questions'   => 'array',
            'fixed_questions.*' => 'string',
        ]);

        $exam = $this->handler->create(
            $this->retrieveCompany($request)->id,
            $request->json('title'),
            $request->json('description'),
            $request->json('success_score_in_percent'),
            $request->json('max_time_in_minutes', 60),
            $request->json('max_attempts_per_candidate', 3),
            $request->json('visible_internal'),
            $request->json('visible_external'),
            $request->json('private'),
            $request->json('password'),
            $request->json('fixed_questions'),
            $request->json('questions_per_categories')
        );

        return response()->json($exam, Response::HTTP_CREATED);

    }

    /**
     * @param Request $request
     *
     * @return JsonResponse|Response|ResponseFactory
     * @throws ValidationException
     */
    public function patch(string $id, Request $request)
    {
        $this->validate($request, [
            'title'                      => 'string',
            'description'                => 'string',
            'success_score_in_percent'   => 'int',
            'company_id'                 => 'uuid',
            'max_time_in_minutes'        => 'int',
            'max_attempts_per_candidate' => 'int',
            'visible_internal'           => 'bool',
            'visible_external'           => 'bool',
            'private'                    => 'bool',

            'questions_per_categories'                         => 'array',
            'questions_per_categories.*.category_id'           => 'required_with:questions_per_categories.*|string',
            'questions_per_categories.*.quantity_of_questions' => 'required_with:questions_per_categories.*|int',

            'fixed_questions'   => 'array',
            'fixed_questions.*' => 'string',
        ]);

        $exam = $this->handler->update(
            $this->retrieveCompany($request)->id,
            $id,
            $request->json('title'),
            $request->json('description'),
            $request->json('max_time_in_minutes', 60),
            $request->json('max_attempts_per_candidate', 3),
            $request->json('success_score_in_percent'),
            $request->json('visible_internal'),
            $request->json('visible_external'),
            $request->json('private'),
            $request->json('password'),
            $request->json('fixed_questions'),
            $request->json('questions_per_categories')
        );

        return response()->json($exam);

    }

    /**
     * @param $id
     *
     * @return JsonResponse|Response|ResponseFactory
     */
    public function findOne($id)
    {
        /** @var Exam $exam */
        try {
            $exam = Exam::with('questionsPerCategory', 'fixedQuestions')->find($id);
        } catch (Exception $e) {
            return response($e);
        }
        $exam->numberOfQuestions = $exam->numberOfQuestions();
        return response()->json($exam);
    }

    /**
     * @param         $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws NoCreditsLeft
     */
    public function start($id, Request $request)
    {
        $response = $this->handler->startExam($id, $this->validateReceivedUser($request));

        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     *
     * @return Model|HasMany|Candidate|object|null
     */
    private function validateReceivedUser(Request $request)
    {
        return $this
            ->retrieveCompany($request)
            ->candidates()
            ->where(['id' => $request->json('candidate_id')])
            ->first();
    }

    /**
     * @param         $id
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function finish($id, Request $request)
    {
        try {
            $certification = new Certification(new ExamValidator());
            $response      = $certification->finishExam($request->json('attempt_id'), $request->json('answers'));
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (ExamAlreadyFinished $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }

        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * @param $id
     *
     * @return Response|ResponseFactory
     */
    public function delete($id)
    {
        Exam::destroy($id);
        return response('', Response::HTTP_NO_CONTENT);
    }
}