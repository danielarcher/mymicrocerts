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
            'title'                      => 'required|string',
            'description'                => 'required|string',
            'max_time_in_minutes'        => 'required|int',
            'success_score_in_percent'   => 'required|int',
            'max_attempts_per_candidate' => 'int',
            'visible_internal'           => 'bool',
            'visible_external'           => 'bool',
            'private'                    => 'string',
        ]);
        $companyId = Auth::user()->company_id;
        if ($request->get('company_id') && Auth::user()->isAdmin()) {
            $companyId = $request->get('company_id');
        }
        $entity = new Exam(array_filter([
            'company_id'                 => $companyId,
            'title'                      => $request->get('title'),
            'description'                => $request->get('description'),
            'max_time_in_minutes'        => $request->get('max_time_in_minutes'),
            'max_attempts_per_candidate' => $request->get('max_attempts_per_candidate'),
            'success_score_in_percent'   => $request->get('success_score_in_percent'),
            'visible_internal'           => $request->get('visible_internal'),
            'visible_external'           => $request->get('visible_external'),
            'private'                    => $request->get('private'),
        ]));
        $entity->save();

        if ($request->get('visible_external')) {
            $entity->access_id = base64_encode(Uuid::uuid4()->toString());
            $entity->access_password = $request->get('password') ? Hash::make($request->get('password')) : null;
            $entity->link = route('external.index', ['id' => $entity->access_id]);
            $entity->save();
        }

        return response()->json($entity, Response::HTTP_CREATED);
    }

    public function findOne($id)
    {
        return response()->json(Exam::find($id));
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

        return response()->json($response, Response::HTTP_CREATED);
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

        return response()->json($response, Response::HTTP_CREATED);
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