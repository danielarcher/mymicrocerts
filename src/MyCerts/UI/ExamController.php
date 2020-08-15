<?php

namespace MyCerts\UI;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Http\ResponseFactory;
use MyCerts\Domain\Certification;
use MyCerts\Domain\ExamValidator;
use MyCerts\Domain\Exception\AccessDeniedToThisExam;
use MyCerts\Domain\Exception\ExamAlreadyFinished;
use MyCerts\Domain\Exception\NoAttemptsLeftForThisExam;
use MyCerts\Domain\Exception\NoCreditsLeft;
use MyCerts\Domain\Exception\UserAlreadyHaveThisCertification;
use MyCerts\Domain\Model\Candidate;
use MyCerts\Domain\Model\Exam;
use Ramsey\Uuid\Uuid;

/**
 * Class ExamController
 *
 * @package MyCerts\UI
 */
class ExamController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        return response()->json(Exam::where('company_id', Auth::user()->company_id)->get());
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
            'private'                    => 'string',
        ]);

        try {
            $exam = new Exam(array_filter([
                'company_id'                 => Auth::user()->company_id,
                'title'                      => $request->json('title'),
                'description'                => $request->json('description'),
                'max_time_in_minutes'        => $request->json('max_time_in_minutes', 60),
                'max_attempts_per_candidate' => $request->json('max_attempts_per_candidate', 3),
                'success_score_in_percent'   => $request->json('success_score_in_percent'),
                'visible_internal'           => $request->json('visible_internal'),
                'visible_external'           => $request->json('visible_external'),
                'private'                    => $request->json('private'),
            ]));
            if (Auth::user()->isAdmin()) {
                $exam->company_id = $request->json('company_id', Auth::user()->company_id);
            }

            $exam->save();

            if ($request->get('visible_external')) {
                $exam->access_id       = base64_encode(Uuid::uuid4()->toString());
                $exam->access_password = $request->get('password') ? Hash::make($request->get('password')) : null;
                $exam->link            = route('external.index', ['id' => $exam->access_id]);
                $exam->save();
            }

            if ($request->get('fixed_questions')) {
                $exam->fixedQuestions()->sync($request->get('fixed_questions'));
            }
            if ($request->get('questions_per_categories')) {
                $exam->questionsPerCategory()->sync($request->get('questions_per_categories'));
            }

            return response()->json($exam, Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response($e->getMessage());
        }
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
        $certification = new Certification(new ExamValidator());
        try {
            $selectedUser = $this->validateReceivedUser($request);
            $response     = $certification->startExam($id, $selectedUser);
        } catch (NoCreditsLeft | UserAlreadyHaveThisCertification | NoAttemptsLeftForThisExam $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (AccessDeniedToThisExam $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Exam not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     *
     * @return Authenticatable|null
     */
    private function validateReceivedUser(Request $request)
    {
        if ($request->get('candidate_id') && Auth::user()->isAdmin()) {
            return Candidate::findOrFail($request->get('candidate_id'));
        }
        return Auth::user();
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
            $response      = $certification->finishExam($request->get('attempt_id'), $request->get('answers'));
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