<?php

class PassportVisaModel
{
    public function getApplicants(int $count = 15): array
    {
        $applicants = [];
        for ($i = 0; $i < $count; $i++) {
            $applicants[] = $this->decorateApplicant($this->generateApplicant($i));
        }

        return $applicants;
    }

    public function buildStats(array $applicants): array
    {
        $stats = ['total' => count($applicants), 'approved' => 0, 'review' => 0, 'new' => 0];

        foreach ($applicants as $applicant) {
            if ($applicant['application']['status'] === 'green') {
                $stats['approved']++;
            }
            if (in_array($applicant['application']['status'], ['yellow', 'blue'], true)) {
                $stats['review']++;
            }
            if (strtotime($applicant['submissionDate']) > strtotime('-7 days')) {
                $stats['new']++;
            }
        }

        return $stats;
    }

    private function normalizeStatus(string $value): string
    {
        return strtolower(trim($value));
    }

    private function badgeClassForStatus(string $value): string
    {
        $map = [
            'approved' => 'bg-success',
            'submitted' => 'bg-primary',
            'processing' => 'bg-info text-dark',
            'under review' => 'bg-warning text-dark',
            'missing' => 'bg-warning text-dark',
            'pending' => 'bg-secondary',
            'rejected' => 'bg-danger',
            'action required' => 'bg-danger',
            'not started' => 'bg-light text-dark border',
            'visa issued' => 'bg-success'
        ];

        return $map[$this->normalizeStatus($value)] ?? 'bg-secondary';
    }

    private function decorateApplicant(array $applicant): array
    {
        $applicant['documentsNormalized'] = $this->normalizeStatus($applicant['documents']['text']);
        $applicant['applicationNormalized'] = $this->normalizeStatus($applicant['application']['text']);
        $applicant['documentsBadgeClass'] = $this->badgeClassForStatus($applicant['documents']['text']);
        $applicant['applicationBadgeClass'] = $this->badgeClassForStatus($applicant['application']['text']);
        $applicant['submissionDateIso'] = date('Y-m-d', strtotime($applicant['submissionDate']));

        return $applicant;
    }

    private function generateApplicant(int $index): array
    {
        $first = ['Manuel', 'Jose', 'Manny', 'Rose Ann', 'John Mark', 'Judy Ann', 'Maria', 'James', 'Mark', 'Sarah'];
        $last = ['Cruz', 'Dela Cruz', 'Padilla', 'Pacquiao', 'Smith', 'Johnson', 'Garcia', 'Miller', 'Davis', 'Martinez'];
        $countries = ['Philippines', 'USA', 'Japan', 'Canada', 'Australia', 'UK', 'France', 'Germany'];

        $passport = [
            ['status' => 'green', 'prefix' => 'DAS', 'desc' => 'Valid'],
            ['status' => 'gray', 'prefix' => 'IN', 'desc' => 'Processing'],
            ['status' => 'yellow', 'prefix' => 'REV', 'desc' => 'Under Review'],
            ['status' => 'red', 'prefix' => 'EXP', 'desc' => 'Expired']
        ];

        $documents = [
            ['status' => 'green', 'text' => 'Approved', 'desc' => 'All documents approved'],
            ['status' => 'blue', 'text' => 'Submitted', 'desc' => 'Documents submitted'],
            ['status' => 'yellow', 'text' => 'Missing', 'desc' => 'Missing documents'],
            ['status' => 'red', 'text' => 'Rejected', 'desc' => 'Rejected'],
            ['status' => 'gray', 'text' => 'Not Started', 'desc' => 'No upload yet']
        ];

        $applications = [
            ['status' => 'green', 'text' => 'Visa Issued', 'desc' => 'Approved'],
            ['status' => 'green', 'text' => 'Approved', 'desc' => 'Approved'],
            ['status' => 'blue', 'text' => 'Processing', 'desc' => 'In process'],
            ['status' => 'yellow', 'text' => 'Under Review', 'desc' => 'Under review'],
            ['status' => 'yellow', 'text' => 'Pending', 'desc' => 'Under review'],
            ['status' => 'red', 'text' => 'Action Required', 'desc' => 'Action needed'],
            ['status' => 'gray', 'text' => 'Not Started', 'desc' => 'Not started']
        ];

        $passportRecord = $passport[array_rand($passport)];
        $documentRecord = $documents[array_rand($documents)];
        $applicationRecord = $applications[array_rand($applications)];

        return [
            'id' => $index + 1,
            'name' => $first[array_rand($first)] . ' ' . $last[array_rand($last)],
            'passport' => [
                'number' => $passportRecord['prefix'] . rand(1000, 9999) . chr(rand(65, 90)),
                'status' => $passportRecord['status'],
                'desc' => $passportRecord['desc']
            ],
            'country' => $countries[array_rand($countries)],
            'documents' => $documentRecord,
            'application' => $applicationRecord,
            'submissionDate' => date('m/d/Y', strtotime('-' . rand(0, 60) . ' days')),
            'priority' => rand(1, 10) > 7 ? 'High' : (rand(1, 10) > 4 ? 'Medium' : 'Low')
        ];
    }
}
