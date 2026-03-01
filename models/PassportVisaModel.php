<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/status_helpers.php';

class PassportVisaModel
{
    private const DEFAULT_TOTAL_APPLICANTS = 300;
    private static ?array $cache = null;

    public function getApplicantsPage(int $page = 1, int $perPage = 10, string $filter = 'all', string $search = ''): array
    {
        $all = $this->getApplicants();
        $filtered = $this->applyFilters($all, $filter, $search);

        // Sort alphabetically by applicant name to ensure consistent ordering
        usort($filtered, static function (array $a, array $b): int {
            $nameA = isset($a['name']) ? strtolower($a['name']) : '';
            $nameB = isset($b['name']) ? strtolower($b['name']) : '';
            return $nameA <=> $nameB;
        });

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

    public function getApplicantById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        foreach ($this->getApplicants() as $applicant) {
            if ((int) ($applicant['id'] ?? 0) === $id) {
                return $applicant;
            }
        }

        return null;
    }

    public function updateApplicantById(int $id, array $payload): array
    {
        if ($id <= 0) {
            return ['ok' => false, 'message' => 'Invalid applicant id.'];
        }

        $passportNumber = trim((string) ($payload['passport_number'] ?? ''));
        $country = trim((string) ($payload['country'] ?? ''));
        $documentsStatus = strtolower(trim((string) ($payload['documents_status'] ?? 'not started')));
        $applicationStatus = strtolower(trim((string) ($payload['application_status'] ?? 'not started')));
        $submissionDate = trim((string) ($payload['submission_date'] ?? ''));
        $remarks = trim((string) ($payload['remarks'] ?? ''));

        $allowedDocuments = ['approved', 'submitted', 'missing', 'rejected', 'not started'];
        $allowedApplication = ['visa issued', 'approved', 'processing', 'under review', 'pending', 'action required', 'not started'];

        // Enforce logical transitions unless explicit override is provided in payload
        $override = isset($payload['override']) && ($payload['override'] === true || $payload['override'] === '1');
        $currentApp = $this->getApplicantById($id);
        $currentDoc = strtolower(trim((string) ($currentApp['documents']['text'] ?? 'not started')));
        $currentAppStatus = strtolower(trim((string) ($currentApp['application']['text'] ?? 'not started')));

        $allowedTransitions = [
            'not started' => ['submitted'],
            'submitted' => ['under review'],
            'under review' => ['approved', 'rejected'],
            'approved' => ['visa issued'],
            'rejected' => [],
            'visa issued' => []
        ];

        if (!$override) {
            // Document status progression check (if changing)
            $targetDoc = strtolower(trim((string) ($payload['documents_status'] ?? $currentDoc)));
            if ($targetDoc !== $currentDoc) {
                $allowed = $allowedTransitions[$currentDoc] ?? [];
                if (!in_array($targetDoc, $allowed, true)) {
                    return ['ok' => false, 'message' => 'Invalid document status transition from ' . $currentDoc . ' to ' . $targetDoc];
                }
            }

            // Application status progression check
            $targetApp = strtolower(trim((string) ($payload['application_status'] ?? $currentAppStatus)));
            if ($targetApp !== $currentAppStatus) {
                $allowed = $allowedTransitions[$currentAppStatus] ?? [];
                if (!in_array($targetApp, $allowed, true)) {
                    return ['ok' => false, 'message' => 'Invalid application status transition from ' . $currentAppStatus . ' to ' . $targetApp];
                }
                // Visa Issued only allowed from Approved
                if ($targetApp === 'visa issued' && $currentAppStatus !== 'approved') {
                    return ['ok' => false, 'message' => 'Visa Issued can only be set when application is Approved'];
                }
            }
        }

        if ($documentsStatus !== '' && !in_array($documentsStatus, $allowedDocuments, true)) {
            return ['ok' => false, 'message' => 'Invalid documents status.'];
        }
        if ($applicationStatus !== '' && !in_array($applicationStatus, $allowedApplication, true)) {
            return ['ok' => false, 'message' => 'Invalid application status.'];
        }
        if ($submissionDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $submissionDate)) {
            return ['ok' => false, 'message' => 'Invalid submission date format.'];
        }

        $updated = false;
        try {
            $conn = getDBConnection();
            if ($conn instanceof mysqli) {
                $sql = "UPDATE passport_applications
                        SET passport_number = ?,
                            country = ?,
                            documents_status = ?,
                            application_status = ?,
                            submission_date = ?,
                            remarks = ?
                        WHERE customer_id = ?
                        ORDER BY updated_at DESC, id DESC
                        LIMIT 1";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $submissionDateValue = $submissionDate === '' ? null : $submissionDate;
                    $stmt->bind_param(
                        'ssssssi',
                        $passportNumber,
                        $country,
                        $documentsStatus,
                        $applicationStatus,
                        $submissionDateValue,
                        $remarks,
                        $id
                    );
                    $stmt->execute();
                    $affectedRows = $stmt->affected_rows;
                    $stmt->close();

                    // No existing row for this customer: create one.
                    if ($affectedRows === 0) {
                        $insertSql = "INSERT INTO passport_applications
                            (customer_id, passport_number, country, documents_status, application_status, submission_date, remarks)
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $insertStmt = $conn->prepare($insertSql);
                        if ($insertStmt) {
                            $insertStmt->bind_param(
                                'issssss',
                                $id,
                                $passportNumber,
                                $country,
                                $documentsStatus,
                                $applicationStatus,
                                $submissionDateValue,
                                $remarks
                            );
                            $insertStmt->execute();
                            $insertStmt->close();
                        }
                    }
                    $updated = true;
                }
                closeDBConnection($conn);
            }
        } catch (Throwable $exception) {
            // Fallback to cache update below.
        }

        // Update current in-memory list to keep UI in sync immediately.
        $all = $this->getApplicants();
        foreach ($all as &$applicant) {
            if ((int) ($applicant['id'] ?? 0) !== $id) {
                continue;
            }

            $applicant['passport']['number'] = $passportNumber !== '' ? $passportNumber : ($applicant['passport']['number'] ?? 'N/A');
            $applicant['country'] = $country !== '' ? $country : ($applicant['country'] ?? 'N/A');
            $applicant['documents']['text'] = $this->toDisplayCase($documentsStatus !== '' ? $documentsStatus : (string) ($applicant['documents']['text'] ?? 'not started'));
            $applicant['application']['text'] = $this->toDisplayCase($applicationStatus !== '' ? $applicationStatus : (string) ($applicant['application']['text'] ?? 'not started'));
            $applicant['submissionDateIso'] = $submissionDate !== '' ? $submissionDate : ($applicant['submissionDateIso'] ?? date('Y-m-d'));
            $applicant['submissionDate'] = date('m/d/Y', strtotime((string) $applicant['submissionDateIso']));
            $applicant['remarks'] = $remarks;
            break;
        }
        unset($applicant);
        self::$cache = $all;

        $updatedApplicant = $this->getApplicantById($id);

        return [
            'ok' => true,
            'message' => $updated
                ? 'Passport/Visa application updated successfully.'
                : 'Application updated in current view.',
            'data' => $updatedApplicant
        ];
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
        $fullName = $first[$index % count($first)] . ' ' . $last[($index * 3) % count($last)];
        $issueDateIso = date('Y-m-d', strtotime('-' . (365 + ($index % 720)) . ' days'));
        $expiryDateIso = date('Y-m-d', strtotime('+'. (180 + ($index % 1080)) . ' days'));

        return [
            'id' => $index + 1,
            'name' => $fullName,
            'email' => strtolower(str_replace(' ', '.', $fullName)) . '@mail.com',
            'phone' => '09' . str_pad((string) (100000000 + (($index * 7391) % 900000000)), 9, '0', STR_PAD_LEFT),
            'passport' => [
                'number' => $passportRecord['prefix'] . str_pad((string) (1000 + (($index * 17) % 9000)), 4, '0', STR_PAD_LEFT) . chr(65 + ($index % 26)),
                'status' => $passportRecord['status'],
                'desc' => $passportRecord['desc'],
                'issueDate' => date('m/d/Y', strtotime($issueDateIso)),
                'issueDateIso' => $issueDateIso,
                'expiryDate' => date('m/d/Y', strtotime($expiryDateIso)),
                'expiryDateIso' => $expiryDateIso
            ],
            'country' => $countries[$index % count($countries)],
            'documents' => $documentRecord,
            'application' => $applicationRecord,
            'submissionDate' => date('m/d/Y', strtotime('-' . ($index % 61) . ' days')),
            'submissionDateIso' => date('Y-m-d', strtotime('-' . ($index % 61) . ' days')),
            'remarks' => $index % 3 === 0 ? 'Awaiting final visa interview schedule.' : 'Documents are currently being validated.',
            'passportImagePath' => 'assets/LOGO.png',
            'oneByOneImagePath' => 'assets/LOGO.png'
        ];
    }

    private function toDisplayCase(string $value): string
    {
        $normalized = str_replace('_', ' ', trim($value));
        return ucwords(strtolower($normalized));
    }
}
