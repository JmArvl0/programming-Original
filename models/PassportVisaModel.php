<?php

class PassportVisaModel
{
    private const DEFAULT_TOTAL_APPLICANTS = 300;
    private static ?array $cache = null;

    public function getApplicantsPage(int $page = 1, int $perPage = 10, string $filter = 'all', string $search = ''): array
    {
        $all = $this->getApplicants();
        $filtered = $this->applyFilters($all, $filter, $search);

        $totalItems = count($filtered);
        $safePerPage = max(1, min($perPage, 100));
        $totalPages = max(1, (int) ceil($totalItems / $safePerPage));
        $safePage = max(1, min($page, $totalPages));
        $offset = ($safePage - 1) * $safePerPage;

        return [
            'items' => array_slice($filtered, $offset, $safePerPage),
            'allFiltered' => $filtered,
            'page' => $safePage,
            'perPage' => $safePerPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'offset' => $offset
        ];
    }

    public function getApplicants(int $count = self::DEFAULT_TOTAL_APPLICANTS): array
    {
        if (self::$cache !== null && count(self::$cache) === $count) {
            return self::$cache;
        }

        $applicants = [];
        for ($i = 0; $i < $count; $i++) {
            $applicants[] = $this->generateApplicant($i);
        }

        self::$cache = $applicants;
        return self::$cache;
    }

    public function buildStats(array $applicants): array
    {
        $stats = ['total' => count($applicants), 'approved' => 0, 'review' => 0, 'new' => 0];

        foreach ($applicants as $applicant) {
            if (($applicant['application']['status'] ?? '') === 'green') {
                $stats['approved']++;
            }
            if (in_array(($applicant['application']['status'] ?? ''), ['yellow', 'blue'], true)) {
                $stats['review']++;
            }
            if (strtotime((string) ($applicant['submissionDate'] ?? '')) > strtotime('-7 days')) {
                $stats['new']++;
            }
        }

        return $stats;
    }

    private function applyFilters(array $applicants, string $filter, string $search): array
    {
        $normalizedFilter = strtolower(trim($filter));
        $normalizedSearch = strtolower(trim($search));
        $today = strtotime(date('Y-m-d'));

        return array_values(array_filter($applicants, static function (array $applicant) use ($normalizedFilter, $normalizedSearch, $today): bool {
            $documents = strtolower(trim((string) ($applicant['documents']['text'] ?? '')));
            $application = strtolower(trim((string) ($applicant['application']['text'] ?? '')));
            $submissionDate = strtotime((string) ($applicant['submissionDateIso'] ?? ''));

            $matchesFilter = true;
            switch ($normalizedFilter) {
                case 'new':
                    if ($submissionDate === false) {
                        $matchesFilter = false;
                        break;
                    }
                    $diffDays = ($today - $submissionDate) / 86400;
                    $matchesFilter = $diffDays >= 0 && $diffDays <= 7;
                    break;
                case 'documents-issue':
                    $matchesFilter = in_array($documents, ['missing', 'rejected'], true);
                    break;
                case 'under-processing':
                    $matchesFilter = $documents === 'submitted' || in_array($application, ['processing', 'under review'], true);
                    break;
                case 'for-action':
                    $matchesFilter = in_array($application, ['action required', 'pending'], true);
                    break;
                case 'approved':
                    $matchesFilter = $documents === 'approved' || $application === 'approved';
                    break;
                case 'completed':
                    $matchesFilter = $application === 'visa issued';
                    break;
                case 'all':
                default:
                    $matchesFilter = true;
                    break;
            }

            if (!$matchesFilter) {
                return false;
            }

            if ($normalizedSearch === '') {
                return true;
            }

            $haystack = strtolower(implode(' ', [
                (string) ($applicant['name'] ?? ''),
                (string) ($applicant['country'] ?? ''),
                (string) ($applicant['passport']['number'] ?? ''),
                (string) ($applicant['documents']['text'] ?? ''),
                (string) ($applicant['application']['text'] ?? '')
            ]));

            return str_contains($haystack, $normalizedSearch);
        }));
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

        $passportRecord = $passport[$index % count($passport)];
        $documentRecord = $documents[$index % count($documents)];
        $applicationRecord = $applications[$index % count($applications)];

        return [
            'id' => $index + 1,
            'name' => $first[$index % count($first)] . ' ' . $last[($index * 3) % count($last)],
            'passport' => [
                'number' => $passportRecord['prefix'] . str_pad((string) (1000 + (($index * 17) % 9000)), 4, '0', STR_PAD_LEFT) . chr(65 + ($index % 26)),
                'status' => $passportRecord['status'],
                'desc' => $passportRecord['desc']
            ],
            'country' => $countries[$index % count($countries)],
            'documents' => $documentRecord,
            'application' => $applicationRecord,
            'submissionDate' => date('m/d/Y', strtotime('-' . ($index % 61) . ' days')),
            'submissionDateIso' => date('Y-m-d', strtotime('-' . ($index % 61) . ' days')),
            'priority' => ($index % 10) > 7 ? 'High' : (($index % 10) > 4 ? 'Medium' : 'Low')
        ];
    }
}
