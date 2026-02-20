<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/CrmModel.php';
require_once __DIR__ . '/../includes/auth.php';

class CrmController extends BaseController
{
    private CrmModel $crmModel;

    public function __construct()
    {
        $this->crmModel = new CrmModel();
    }

    public function index(): void
    {
        $this->render('crm/index', [
            'pageTitle' => 'CRM',
            'pageSubtitle' => 'View and Manage Operational Data',
            'stats' => $this->crmModel->getStats(),
            'customers' => $this->crmModel->getCustomers()
        ], [
            'styles' => ['css/crm.css']
        ]);
    }
}
