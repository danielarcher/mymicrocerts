<?php

namespace MyCerts\UI;

use App\Http\Controllers\Controller;
use Faker\Factory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MyCerts\Application\CandidateHandler;
use MyCerts\Application\CompanyHandler;
use MyCerts\Application\ExamHandler;
use MyCerts\Application\PaymentHandler;
use MyCerts\Application\QuestionHandler;
use MyCerts\Domain\Model\Category;
use MyCerts\Domain\Model\Exam;
use MyCerts\Domain\Roles;
use Symfony\Component\Yaml\Yaml;

class CheckoutController extends Controller
{
    /**
     * @var CompanyHandler
     */
    private CompanyHandler $companyHandler;
    /**
     * @var CandidateHandler
     */
    private CandidateHandler $candidateHandler;
    /**
     * @var PaymentHandler
     */
    private PaymentHandler $paymentHandler;
    /**
     * @var QuestionHandler
     */
    private QuestionHandler $questionHandler;
    /**
     * @var ExamHandler
     */
    private ExamHandler $examHandler;

    public function __construct(
        CompanyHandler $companyHandler,
        CandidateHandler $candidateHandler,
        PaymentHandler $paymentHandler,
        QuestionHandler $questionHandler,
        ExamHandler $examHandler
    ) {
        $this->companyHandler   = $companyHandler;
        $this->candidateHandler = $candidateHandler;
        $this->paymentHandler   = $paymentHandler;
        $this->questionHandler  = $questionHandler;
        $this->examHandler = $examHandler;
    }

    public function payment(Request $request)
    {
        $this->validate($request, [
            "company"         => 'required|array',
            "company.name"    => 'required|string',
            "company.country" => 'required|string',

            "user"                  => 'required|array',
            "user.email"            => 'required|string',
            "user.password"         => 'required|string',
            "user.confirm_password" => 'required|string',
            "user.first_name"       => 'required|string',
            "user.last_name"        => 'required|string',

            "payment"           => 'required|array',
            "payment.plan_id"   => 'required|string',
            "payment.number"    => 'required|string',
            "payment.cvc"       => 'required|integer',
            "payment.exp_month" => 'required|integer',
            "payment.exp_year"  => 'required|integer',
        ]);

        $company = $this->companyHandler->create(
            $request->json('company.name'),
            $request->json('company.country'),
            $request->json('user.email'),
            $request->json('user.first_name') . ' ' . $request->json('user.last_name')
        );

        $candidate = $this->candidateHandler->create(
            $company->id,
            $request->json('user.email'),
            $request->json('user.password'),
            $request->json('user.first_name'),
            $request->json('user.last_name'),
            Roles::COMPANY,
            $request->json('user.custom')
        );

        $contract = $this->paymentHandler->charge(
            $request->json('payment.plan_id'),
            $company->id,
            $company->stripe_customer_id,
            $request->json('payment.number'),
            $request->json('payment.cvc'),
            $request->json('payment.exp_month'),
            $request->json('payment.exp_year'),
        );

        return response()->json(compact('company', 'candidate', 'contract'), Response::HTTP_CREATED);
    }

    public function populate(string $companyId)
    {
        $files = glob(__DIR__ . '/../../../lumen/resources/questions/*');
        foreach ($files as $file) {
            $this->importFile($file, $companyId);
        }
        $categories = Category::where(['company_id' => $companyId])->get();

        foreach ($categories as $category) {
            $payload = [
                "company_id"               => $companyId,
                "title"                    => 'PHP: ' . $category->name,
                "description"              => Factory::create()->paragraph,
                "visible_external"         => false,
                "success_score_in_percent" => 100,
                "max_time_in_minutes"      => 5,
                "questions_per_categories" => [
                    [
                        "category_id"           => $category->id,
                        "quantity_of_questions" => Factory::create()->numberBetween(1, 4)
                    ]
                ],
            ];
            $this->examHandler->create(
                $companyId,
                'PHP: ' . $category->name,
                Factory::create()->paragraph,
                100,
                5,
                999,
                true,
                false,
                false,
                null,
                null,
                [
                    [
                        "category_id"           => $category->id,
                        "quantity_of_questions" => Factory::create()->numberBetween(1, 4)
                    ]
                ],
                null,
                [
                    'badge_name' => 'PHP '.$category->name.' Jedi',
                    'image' => Factory::create()->imageUrl()
                ]
            );
        }

        return response()->json('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param $file
     * @param $companyId
     */
    public function importFile($file, $companyId): void
    {
        $yaml = Yaml::parseFile($file);

        $category = new Category(array_filter([
            'company_id' => $companyId,
            'name'       => $yaml['category'],
        ]));
        $category->save();

        foreach ($yaml['questions'] as $question) {
            $options = array_map(function ($array) {
                return [
                    'text'    => $array['value'],
                    'correct' => $array['correct']
                ];
            }, $question['answers']);
            $this->questionHandler->create(
                $companyId,
                $question['question'],
                [(string) $category->id],
                $options,
                null
            );
        }
    }
}