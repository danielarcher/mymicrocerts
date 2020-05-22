<?php

namespace MyCerts\Domain;

use Illuminate\Http\Response;
use Mattiasgeniar\Percentage\Percentage;
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
use MyCerts\Domain\Model\Company;
use MyCerts\Domain\Model\Exam;

class Certification
{
    /**
     * @var ExamValidator
     */
    private $validator;

    public function __construct(ExamValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param string    $examId
     * @param Candidate $candidate
     * @return array
     * @throws AccessDeniedToThisExam
     * @throws NoAttemptsLeftForThisExam
     * @throws NoCreditsLeft
     * @throws UserAlreadyHaveThisCertification
     */
    public function startExam(string $examId, Candidate $candidate): array
    {
        /** @var Exam $exam */
        $exam = Exam::with('company')->findOrFail($examId);

        /**
         * Validate user can proceed
         */
        $this->validator->assertExamCanBeStarted($exam, $candidate);

        $attempt = new Attempt([
            'exam_id'      => $examId,
            'candidate_id' => $candidate->id,
        ]);
        $attempt->save();

        /**
         * Withdraw one credit
         */
        $exam->company->useCredit();

        $exam = Exam::with('questions.options')->findOrFail($attempt->exam_id);

        return [
            'attempt' => $attempt,
            'exam' => $exam
        ];
    }


    /**
     * @param string $attemptId
     * @param array  $answers
     * @return array
     * @throws ExamAlreadyFinished
     */
    public function finishExam(string $attemptId, array $answers): array
    {
        $attempt = Attempt::findOrFail($attemptId);
        $exam = Exam::with('questions')->findOrFail($attempt->exam_id);

        $this->validator->assertExamCanBeFinished($exam, $attempt);

        $score   = $exam->calculateScore($answers);
        $attempt = $this->saveAttempt($attempt, $score, $exam);

        $response = ['attempt' => $attempt];
        if ($attempt->approved) {
            $response['certificate'] =  $this->generateCertificate($attempt);
        }
        return $response;
    }

    /**
     * @param Attempt $attempt
     * @param int     $score
     * @param Exam    $exam
     * @return Attempt
     * @throws \Exception
     */
    public function saveAttempt(Attempt $attempt, int $score, Exam $exam): Attempt
    {
        $attempt->score_absolute   = $score;
        $attempt->score_in_percent = Percentage::calculate($score, $exam->questions()->count());
        $attempt->finished_at      = new \DateTimeImmutable();
        $attempt->approved         = $exam->checkIsApproved($score);
        $attempt->save();

        return $attempt;
    }

    /**
     * @param Attempt $attempt
     * @return Certificate
     */
    public function generateCertificate(Attempt $attempt): Certificate
    {
        $certificate = new Certificate([
            'exam_id'          => $attempt->exam_id,
            'candidate_id'     => $attempt->candidate_id,
            'score_in_percent' => $attempt->score_in_percent,
        ]);
        $certificate->save();
        return $certificate;
    }

    private function findAttempt(string $attemptId): Attempt
    {
        $attempt = Attempt::findOrFail($attemptId);
        if (empty($attempt)) {
            throw new AttemptNotFound();
        }
        return $attempt;
    }

}