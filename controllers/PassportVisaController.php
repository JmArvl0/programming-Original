<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/PassportVisaModel.php';
require_once __DIR__ . '/../includes/auth.php';

class PassportVisaController extends BaseController
{
    private PassportVisaModel $passportVisaModel;

    public function __construct()
    {
        $this->passportVisaModel = new PassportVisaModel();
    }

    public function index(): void
    {
        $applicants = $this->passportVisaModel->getApplicants();

        $this->render('passport_visa/index', [
            'pageTitle' => 'Passport & Visa',
            'pageSubtitle' => 'Manage Passport & Visa Processing',
            'applicants' => $applicants,
            'stats' => $this->passportVisaModel->buildStats($applicants)
        ], [
            'styles' => ['css/passport-visa.css'],
            'scripts' => ['js/passport-visa.js']
        ]);
    }
}
