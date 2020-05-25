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
use MyCerts\Domain\Model\Exam;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExternalExamController extends Controller
{
    public function index(string $id, Request $request)
    {
        $exam = Exam::where('access_id', $id)->first();
        return response()->json([
            'instructions'          => 'Proceed with first name, last name, exam password.',
            'minutes_to_completion' => $exam->max_time_in_minutes,
            'link_to_start'         => route('external.start', ['id' => $id])
        ]);
    }

    /**
     * @param         $id
     * @param Request $request
     * @return JsonResponse
     * @throws AccessDeniedToThisExam
     * @throws NoAttemptsLeftForThisExam
     * @throws UserAlreadyHaveThisCertification
     * @throws \Illuminate\Validation\ValidationException
     */
    public function start($id, Request $request)
    {
        $this->validate($request, [
            'password'   => 'required',
            'first_name' => 'required',
            'last_name'  => 'required',
            'email'      => 'required|email|unique:candidate'
        ]);
        try {
            $exam = Exam::where(['access_id' => $id])->firstOrFail();
        } catch (NotFoundHttpException $e) {
            return response()->json(['error' => 'Exam not found'],Response::HTTP_NOT_FOUND);
        }

        $this->assertPasswordIsValid($exam, $request->get('password'));

        $candidate = $this->createGuestCandidate(
            $request->get('first_name'),
            $request->get('last_name'),
            $request->get('email'),
            $exam->company_id
        );
        $certification = new Certification(new ExamValidator());
        try {
            $response = $certification->startExam($exam->id, $candidate);
        } catch (NoCreditsLeft $e) {
            return response()->json(['error' => $e->getMessage()],Response::HTTP_CONFLICT);
        } catch (AccessDeniedToThisExam $e) {
            return response()->json(['error' => $e->getMessage()],Response::HTTP_FORBIDDEN);
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
            $exam = Exam::where(['access_id' => $id])->firstOrFail();
        } catch (NotFoundHttpException $e) {
            return response()->json(['error' => 'Exam not found'],Response::HTTP_NOT_FOUND);
        }

        try {
            $this->validateReceivedUser($request);
            $certification = new Certification(new ExamValidator());
            $response = $certification->finishExam($request->get('attempt_id'), $request->get('answers'));
        } catch (ExamNotFound | AttemptNotFound $e) {
            return response()->json(['error' => $e->getMessage()],Response::HTTP_NOT_FOUND);
        } catch (ExamAlreadyFinished $e) {
            return response()->json(['error' => $e->getMessage()],Response::HTTP_CONFLICT);
        }

        return response()->json($response, Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @throws AttemptNotFound
     */
    private function validateReceivedUser(Request $request)
    {
        $candidate = $request->get('candidate_id');
        $attempt = $request->get('attempt_id');

        if (! Attempt::where(['candidate_id' => $candidate, 'id' => $attempt])->exists()) {
            throw new AttemptNotFound();
        }
    }

    /**
     * @param Exam $exam
     * @param      $password
     * @throws AccessDeniedToThisExam
     */
    private function assertPasswordIsValid(Exam $exam, $password)
    {
        if (! Hash::check($password, $exam->access_password)) {
            throw new AccessDeniedToThisExam();
        }
    }

    private function createGuestCandidate($firstName, $lastName, $email, $companyId)
    {
        $candidate = new Candidate([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'company_id' => $companyId,
        ]);
        $candidate->save();
        return $candidate;
    }

}