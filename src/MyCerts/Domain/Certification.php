<?php

namespace MyCerts\Domain;

use Illuminate\Http\Response;
use MyCerts\Domain\Exception\AttemptNotFound;
use MyCerts\Domain\Exception\ExamAlreadyFinished;
use MyCerts\Domain\Exception\ExamNotFound;
use MyCerts\Domain\Exception\UserAlreadyHaveThisCertification;
use MyCerts\Domain\Model\Attempt;
use MyCerts\Domain\Model\Certificate;
use MyCerts\Domain\Model\Exam;

class Certification
{
    /**
     * @param string $examId
     * @param string $candidateId
     * @return Attempt
     * @throws UserAlreadyHaveThisCertification
     */
    public function startExam(string $examId, string $candidateId): array
    {
        $this->assertCompanyHasExamsLeft($examId);
        $this->assertCandidateHasAccessToThisExam($examId, $candidateId);
        $this->assertCandidateHasAttemptsLeft($examId, $candidateId);
        $this->assertCandidateDontHasCertificate($examId, $candidateId);

        $attempt = new Attempt([
            'exam_id'      => $examId,
            'candidate_id' => $candidateId,
        ]);
        $attempt->save();

        $exam = Exam::with('questions.options')->findOrFail($attempt->exam_id);

        return [
            'attempt' => $attempt,
            'exam' => $exam
        ];
    }

    /**
     * @param string $examId
     * @param string $candidateId
     * @throws UserAlreadyHaveThisCertification
     */
    private function assertCandidateDontHasCertificate(string $examId, string $candidateId): void
    {
        $certificate = Certificate::where('exam_id', $examId)->where('candidate_id', $candidateId)->get();
        if (!empty($certificate)) {
            #throw new UserAlreadyHaveThisCertification();
        }
    }

    private function assertCandidateHasAttemptsLeft(string $examId, string $candidateId): void
    {
        return;
    }

    private function assertCompanyHasExamsLeft(string $examId)
    {
        return;
    }

    private function assertCandidateHasAccessToThisExam(string $examId, string $candidateId)
    {
        return;
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
        $this->assertAttemptIsNotFinished($attempt);

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