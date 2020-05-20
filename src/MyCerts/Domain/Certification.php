<?php

namespace MyCerts\Domain;

use Illuminate\Http\Response;
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
        $this->assertCompanyHasExamsLeft($exam);
        $this->assertCandidateHasAccessToThisExam($exam, $candidate);
        $this->assertCandidateHasAttemptsLeft($exam, $candidate);
        $this->assertCandidateDontHasCertificate($examId, $candidate);

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
     * @param string    $examId
     * @param Candidate $candidate
     * @throws UserAlreadyHaveThisCertification
     */
    private function assertCandidateDontHasCertificate(string $examId, Candidate $candidate): void
    {
        if ($candidate->hasCertificateFor($examId)) {
            throw new UserAlreadyHaveThisCertification();
        }
        #$certificate = Certificate::where('exam_id', $examId)->where('candidate_id', $candidateId)->first();
        #if (!empty($certificate)) {

        #}
    }

    private function assertCandidateHasAttemptsLeft(Exam $exam, Candidate $candidate): void
    {
        $currentAttempts = Attempt::where(['candidate_id' => $candidate->id, 'exam_id' => $exam->id])->count();
        if ($currentAttempts >= $exam->max_attempts_per_candidate) {
            throw new NoAttemptsLeftForThisExam();
        }
        return;
    }

    /**
     * @param Exam $exam
     * @throws NoCreditsLeft
     */
    private function assertCompanyHasExamsLeft(Exam $exam)
    {
        if (! $exam->company->hasCredits()) {
            throw new NoCreditsLeft();
        }
        return;
    }

    private function assertCandidateHasAccessToThisExam(Exam $exam, Candidate $candidate)
    {
        if ($exam->private) {
            throw new AccessDeniedToThisExam();
        }
        if ($exam->company_id === $candidate->company_id || $candidate->isAdmin()) {
            return;
        }
        if ($exam->visible_external) {
            return;
        }
        throw new AccessDeniedToThisExam();
    }

    /**
     * @param string $attemptId
     * @param array  $answers
     * @return array
     * @throws AttemptNotFound
     * @throws ExamAlreadyFinished
     */
    public function finishExam(string $attemptId, array $answers): array
    {
        $attempt = $this->findAttempt($attemptId);
        #$this->assertAttemptIsNotFinished($attempt);

        $exam = Exam::with('questions')->findOrFail($attempt->exam_id);

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
        $scoreInPercent = $this->transformScoreInPercent($score,$exam->questions()->count());
        $attempt->score_absolute   = $score;
        $attempt->score_in_percent = $scoreInPercent;
        $attempt->finished_at      = new \DateTimeImmutable();
        $attempt->approved         = $exam->checkIsApproved($scoreInPercent);
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
        $attempt = Attempt::find($attemptId);
        if (empty($attempt)) {
            throw new AttemptNotFound();
        }
        return $attempt;
    }

    private function assertAttemptIsNotFinished(Attempt $attempt)
    {
        if (!is_null($attempt->finished_at)) {
            throw new ExamAlreadyFinished();
        }
    }

    private function transformScoreInPercent(int $score, int $questionCount)
    {
        return ($score > 0) ? round(($score / $questionCount) * 100) : 0;
    }
}