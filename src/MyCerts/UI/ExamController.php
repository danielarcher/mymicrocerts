<?php

namespace MyCerts\UI;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use League\Fractal\Serializer\JsonApiSerializer;
use MyCerts\Domain\Certification;
use MyCerts\Domain\ExamValidator;
use MyCerts\Domain\Exception\AccessDeniedToThisExam;
use MyCerts\Domain\Exception\AttemptNotFound;
use MyCerts\Domain\Exception\ExamAlreadyFinished;
use MyCerts\Domain\Exception\ExamNotFound;
use MyCerts\Domain\Exception\NoAttemptsLeftForThisExam;
use MyCerts\Domain\Exception\NoCreditsLeft;
use MyCerts\Domain\Exception\UserAlreadyHaveThisCertification;
use MyCerts\Domain\Model\Attempt;
use MyCerts\Domain\Model\Candidate;
use MyCerts\Domain\Model\Certificate;
use MyCerts\Domain\Model\Exam;
use MyCerts\Domain\Transformers\ExamTransformer;
use MyCerts\UI\Request\ExamCreateRequest;
use Ramsey\Uuid\Uuid;

class ExamController extends Controller
{
    public function list(Request $request)
    {
        return response()->json(Exam::where('company_id', Auth::user()->company_id)->get());
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'company_id'                 => 'required|uuid',
            'title'                      => 'required|unique:exam|string',
            'description'                => 'required|string',
            'max_time_in_minutes'        => 'required|int',
            'success_score_in_percent'   => 'required|int',
            'max_attempts_per_candidate' => 'int',
            'visible_internal'           => 'bool',
            'visible_external'           => 'bool',
            'private'                    => 'string',
        ]);

        try {
            $company_id = Auth::user()->isAdmin() ? $request->get('company_id', Auth::user()->company_id) : Auth::user()->company_id;

            $exam = new Exam(array_filter([
                'company_id'                 => $company_id,
                'title'                      => $request->get('title'),
                'description'                => $request->get('description'),
                'max_time_in_minutes'        => $request->get('max_time_in_minutes'),
                'max_attempts_per_candidate' => $request->get('max_attempts_per_candidate'),
                'success_score_in_percent'   => $request->get('success_score_in_percent'),
                'visible_internal'           => $request->get('visible_internal'),
                'visible_external'           => $request->get('visible_external'),
                'private'                    => $request->get('private'),
            ]));
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
     * @return JsonResponse
     * @throws NoCreditsLeft
     */
    public function start($id, Request $request)
    {
        $certification = new Certification(new ExamValidator());
        try {
            $selectedUser = $this->validateReceivedUser($request);
            $response = $certification->startExam($id, $selectedUser);
        } catch (NoCreditsLeft | UserAlreadyHaveThisCertification | NoAttemptsLeftForThisExam $e) {
            return response()->json(['error' => $e->getMessage()],Response::HTTP_CONFLICT);
        } catch (AccessDeniedToThisExam $e) {
            return response()->json(['error' => $e->getMessage()],Response::HTTP_FORBIDDEN);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Exam not found'],Response::HTTP_NOT_FOUND);
        }

        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * @param         $id
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function finish($id, Request $request)
    {
        try {
            $certification = new Certification(new ExamValidator());
            $response = $certification->finishExam($request->get('attempt_id'), $request->get('answers'));
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()],Response::HTTP_NOT_FOUND);
        } catch (ExamAlreadyFinished $e) {
            return response()->json(['error' => $e->getMessage()],Response::HTTP_CONFLICT);
        }

        return response()->json($response, Response::HTTP_OK);
    }

    private function validateReceivedUser(Request $request)
    {
        if ($request->get('candidate_id') && Auth::user()->isAdmin()) {
            return Candidate::findOrFail($request->get('candidate_id'));
        }
        return Auth::user();
    }

    public function delete($id)
    {
        Exam::destroy($id);
        return response('',Response::HTTP_NO_CONTENT);
    }
}