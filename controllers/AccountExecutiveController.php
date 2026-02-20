<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/AccountExecutiveModel.php';
require_once __DIR__ . '/../includes/auth.php';

class AccountExecutiveController extends BaseController
{
    private AccountExecutiveModel $accountExecutiveModel;

    public function __construct()
    {
        $this->accountExecutiveModel = new AccountExecutiveModel();
    }

    public function index(): void
    {
        $customers = $this->accountExecutiveModel->getCustomers();

        $this->render('account_executive/index', [
            'pageTitle' => 'Account Executive',
            'pageSubtitle' => 'Handles Customer Processing',
            'customers' => $customers,
            'stats' => $this->accountExecutiveModel->buildStats($customers)
        ], [
            'styles' => ['css/account-executive.css'],
            'scripts' => ['js/account-executive.js']
        ]);
    }
}
