<?php

namespace MyCerts\UI;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use MyCerts\Domain\Certification;
use MyCerts\Domain\Exception\AttemptNotFound;
use MyCerts\Domain\Exception\ExamAlreadyFinished;
use MyCerts\Domain\Exception\ExamNotFound;
use MyCerts\Domain\Model\Attempt;
use MyCerts\Domain\Model\Certificate;
use MyCerts\Domain\Model\Exam;

class ExamController extends Controller
{
    public function list(Request $request)
    {
        $candidate = Auth::user();
        return response()->json(Exam::where('company_id', $candidate->company_id)->get());
    }

    public function create(Request $request)
    {
        $entity = new Exam(array_filter([
            'company_id'                 => $request->get('company_id'),
            'title'                      => $request->get('title'),
            'description'                => $request->get('description'),
            'max_time_in_minutes'        => $request->get('max_time_in_minutes'),
            'max_attempts_per_candidate' => $request->get('max_attempts_per_candidate'),
            'success_score_in_percent'   => $request->get('success_score_in_percent'),
            'visible_internal'           => $request->get('visible_internal'),
            'visible_external'           => $request->get('visible_external'),
            'private'                    => $request->get('private'),
            'access_code'                => $request->get('access_code'),
        ]));
        $entity->save();

        return response()->json($entity, Response::HTTP_CREATED);
    }

    public function findOne($id)
    {
        return response()->json(Exam::find($id));
    }

    public function start($id, Request $request)
    {
        $certification = new Certification();
        $response = $certification->startExam($id, $request->get('candidate_id'));

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
            $certification = new Certification();
            $response = $certification->finishExam($request->get('attempt_id'), $request->get('answers'));
        } catch (ExamNotFound | AttemptNotFound $e) {
            return response()->json(['error' => $e->getMessage()],Response::HTTP_NOT_FOUND);
        } catch (ExamAlreadyFinished $e) {
            return response()->json(['error' => $e->getMessage()],Response::HTTP_CONFLICT);
        }

        return response()->json($response, Response::HTTP_CREATED);
    }


}