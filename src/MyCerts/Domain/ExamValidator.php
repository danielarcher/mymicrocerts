<?php

namespace MyCerts\Domain;

use MyCerts\Domain\Exception\AccessDeniedToThisExam;
use MyCerts\Domain\Exception\ExamAlreadyFinished;
use MyCerts\Domain\Exception\NoAttemptsLeftForThisExam;
use MyCerts\Domain\Exception\NoCreditsLeft;
use MyCerts\Domain\Exception\UserAlreadyHaveThisCertification;
use MyCerts\Domain\Model\Attempt;
use MyCerts\Domain\Model\Candidate;
use MyCerts\Domain\Model\Exam;

class ExamValidator
{
    /**
     * @param Exam      $exam
     * @param Candidate $candidate
     * @throws AccessDeniedToThisExam
     * @throws NoAttemptsLeftForThisExam
     * @throws NoCreditsLeft
     * @throws UserAlreadyHaveThisCertification
     */
    public function assertExamCanBeStarted(Exam $exam, Candidate $candidate)
    {
        $this->companyHasExamsLeft($exam);
        $this->candidateHasAccessToThisExam($exam, $candidate);
        $this->candidateHasAttemptsLeft($exam, $candidate);
        $this->candidateDontHasCertificate($exam->id, $candidate);
    }

    /**
     * @param Exam    $exam
     * @param Attempt $attempt
     * @throws ExamAlreadyFinished
     */
    public function assertExamCanBeFinished(Exam $exam, Attempt $attempt)
    {
        $this->attemptIsNotFinished($attempt);
    }

    /**
     * @param Exam $exam
     * @throws NoCreditsLeft
     */
    protected function companyHasExamsLeft(Exam $exam)
    {
        if (! $exam->company->hasCredits()) {
            throw new NoCreditsLeft();
        }
        return;
    }

    protected function candidateHasAccessToThisExam(Exam $exam, Candidate $candidate)
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

    protected function attemptIsNotFinished(Attempt $attempt)
    {
        if (!is_null($attempt->finished_at)) {
            throw new ExamAlreadyFinished();
        }
    }

    /**
     * @param string    $examId
     * @param Candidate $candidate
     * @throws UserAlreadyHaveThisCertification
     */
    protected function candidateDontHasCertificate(string $examId, Candidate $candidate): void
    {
        if ($candidate->hasCertificateFor($examId)) {
            throw new UserAlreadyHaveThisCertification();
        }
    }

    protected function candidateHasAttemptsLeft(Exam $exam, Candidate $candidate): void
    {
        $currentAttempts = Attempt::where(['candidate_id' => $candidate->id, 'exam_id' => $exam->id])->count();
        if ($currentAttempts >= $exam->max_attempts_per_candidate) {
            throw new NoAttemptsLeftForThisExam();
        }
    }
}