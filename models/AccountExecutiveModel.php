<?php

require_once __DIR__ . '/../config/database.php';

class AccountExecutiveModel
{
    private const DEFAULT_TOTAL_CUSTOMERS = 300;
    private static ?array $cache = null;
    private ?mysqli $conn = null;

    public function __destruct()
    {
        if ($this->conn instanceof mysqli) {
            closeDBConnection($this->conn);
            $this->conn = null;
        }
    }

    public function getCustomersPage(
        int $page = 1,
        int $perPage = 10,
        string $tabFilter = 'all',
        string $paymentFilter = 'all',
        string $statusFilter = 'all',
        string $search = ''
    ): array
    {
        $dbPage = $this->getCustomersPageFromDatabase($page, $perPage, $tabFilter, $paymentFilter, $statusFilter, $search);
        if ($dbPage !== null) {
            return $dbPage;
        }

        $all = $this->getCustomers();
        $filtered = $this->applyFilters($all, $tabFilter, $paymentFilter, $statusFilter, $search);

        $totalItems = count($filtered);
        $safePerPage = max(1, min($perPage, 100));
        $totalPages = max(1, (int) ceil($totalItems / $safePerPage));
        $safePage = max(1, min($page, $totalPages));
        $offset = ($safePage - 1) * $safePerPage;

        $items = array_slice($filtered, $offset, $safePerPage);
        if ($this->normalizeValue($tabFilter) === 'refund') {
            $items = array_map(static function (array $customer): array {
                $customer['paymentStatus'] = 'Refunded';
                return $customer;
            }, $items);
        }

        return [
            'items' => $items,
            'allFiltered' => $filtered,
            'page' => $safePage,
            'perPage' => $safePerPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'offset' => $offset
        ];
    }

    private function getCustomersPageFromDatabase(
        int $page,
        int $perPage,
        string $tabFilter,
        string $paymentFilter,
        string $statusFilter,
        string $search
    ): ?array {
        $conn = $this->getConnection();
        if (!($conn instanceof mysqli)) {
            return null;
        }

        $safePerPage = max(1, min($perPage, 100));
        $safePage = max(1, $page);
        $offset = ($safePage - 1) * $safePerPage;

        $docStageExpr = "
            CASE
                WHEN pa.booking_id IS NULL THEN 'not started'
                WHEN LOWER(TRIM(pa.documents_status)) IN ('approved', 'complete', 'completed', 'verified') THEN 'approved'
                WHEN LOWER(TRIM(pa.documents_status)) = 'missing' THEN 'missing'
                WHEN LOWER(TRIM(pa.documents_status)) = 'submitted' THEN 'pending'
                WHEN LOWER(TRIM(pa.documents_status)) IN ('processing', 'under review') THEN LOWER(TRIM(pa.documents_status))
                WHEN LOWER(TRIM(pa.documents_status)) IN ('rejected', 'incomplete', 'pending') THEN LOWER(TRIM(pa.documents_status))
                WHEN LOWER(TRIM(pa.documents_status)) = 'not started' THEN 'not started'
                ELSE LOWER(TRIM(COALESCE(pa.documents_status, 'not started')))
            END
        ";

        $paymentExpr = "
            CASE
                WHEN p.status IS NULL THEN LOWER(TRIM(COALESCE(b.payment_status, 'pending')))
                WHEN LOWER(TRIM(p.status)) = 'partial' THEN 'partially paid'
                WHEN LOWER(TRIM(p.status)) = 'pending' THEN 'pending'
                WHEN LOWER(TRIM(p.status)) = 'cancelled' THEN 'failed'
                ELSE LOWER(TRIM(p.status))
            END
        ";
        $hasCancelledBookingExpr = "
            EXISTS (
                SELECT 1
                FROM bookings bcx
                WHERE bcx.customer_id = c.id
                  AND LOWER(TRIM(bcx.booking_status)) = 'cancelled'
            )
        ";

        // Use account_executive -> bookings -> customers chain so this module
        // doesn't assume a direct customer_id on account_executive.
        $fromClause = "
            FROM account_executive ae
            INNER JOIN bookings b ON ae.booking_id = b.id
            INNER JOIN customers c ON b.customer_id = c.id
            LEFT JOIN tours t ON t.id = b.tour_id
            LEFT JOIN (
                SELECT pa1.*, b1.customer_id
                FROM passport_applications pa1
                INNER JOIN bookings b1 ON pa1.booking_id = b1.id
                INNER JOIN (
                    SELECT
                        b2.customer_id,
                        MAX(CONCAT(COALESCE(pa2.updated_at,'1970-01-01 00:00:00'), '-', LPAD(pa2.id,10,'0'))) AS max_key
                    FROM passport_applications pa2
                    INNER JOIN bookings b2 ON pa2.booking_id = b2.id
                    GROUP BY b2.customer_id
                ) pa_max ON b1.customer_id = pa_max.customer_id
                    AND CONCAT(COALESCE(pa1.updated_at,'1970-01-01 00:00:00'), '-', LPAD(pa1.id,10,'0')) = pa_max.max_key
            ) pa ON pa.customer_id = c.id
            LEFT JOIN (
                SELECT p1.* FROM payments p1
                INNER JOIN (
                    SELECT booking_id, MAX(CONCAT(COALESCE(updated_at,'1970-01-01 00:00:00'), '-', LPAD(id,10,'0'))) AS max_key
                    FROM payments
                    GROUP BY booking_id
                ) p_max ON p1.booking_id = p_max.booking_id
                    AND CONCAT(COALESCE(p1.updated_at,'1970-01-01 00:00:00'), '-', LPAD(p1.id,10,'0')) = p_max.max_key
            ) p ON p.booking_id = b.id
        ";

        $whereParts = [];
        $types = '';
        $params = [];

        $normalizedTab = $this->normalizeValue($tabFilter);
        $normalizedPayment = $this->normalizeValue($paymentFilter);
        $normalizedStatus = $this->normalizeValue($statusFilter);
        $normalizedSearch = trim($search);

        // Process-based tab filtering
        switch ($normalizedTab) {
            case 'new':
                $whereParts[] = "($docStageExpr = 'not started' AND $paymentExpr = 'unpaid')";
                break;
            case 'for-follow-up':
                $whereParts[] = "($docStageExpr IN ('pending', 'missing'))";
                break;
            case 'ongoing':
                $whereParts[] = "($docStageExpr IN ('processing', 'under review'))";
                break;
            case 'payment-issues':
                $whereParts[] = "($paymentExpr IN ('unpaid', 'overdue', 'failed'))";
                break;
            case 'finished':
                $whereParts[] = "($docStageExpr IN ('approved', 'complete', 'completed', 'verified') AND $paymentExpr = 'paid')";
                break;
            case 'refund':
                $whereParts[] = "($paymentExpr = 'refunded' OR LOWER(TRIM(COALESCE(ae.case_status, b.booking_status, ''))) = 'refund')";
                break;
            case 'cancel-booking':
                $whereParts[] = "($hasCancelledBookingExpr OR LOWER(TRIM(COALESCE(ae.case_status, b.booking_status, ''))) = 'cancelled')";
                break;
            case 'all':
            default:
                break;
        }

        // Keep payment/status dropdown compatibility while avoiding conflicting tab logic.
        $ignorePaymentFilter = in_array($normalizedTab, ['new', 'for-follow-up', 'ongoing', 'payment-issues', 'refund', 'cancel-booking'], true);
        if (!$ignorePaymentFilter && $normalizedPayment !== '' && $normalizedPayment !== 'all') {
            $whereParts[] = "($paymentExpr = ?)";
            $types .= 's';
            $params[] = $normalizedPayment;
        }

        $ignoreStatusFilter = in_array($normalizedTab, ['new', 'for-follow-up', 'ongoing', 'payment-issues', 'refund', 'cancel-booking'], true);
        if (!$ignoreStatusFilter && $normalizedStatus !== '' && $normalizedStatus !== 'all') {
            $whereParts[] = "(LOWER(TRIM(COALESCE(ae.case_status, b.booking_status, ''))) = ?)";
            $types .= 's';
            $params[] = $normalizedStatus;
        }

        if ($normalizedSearch !== '') {
            $whereParts[] = "(c.full_name LIKE ? OR t.destination LIKE ? OR COALESCE(c.email, '') LIKE ?)";
            $types .= 'sss';
            $searchLike = '%' . $normalizedSearch . '%';
            $params[] = $searchLike;
            $params[] = $searchLike;
            $params[] = $searchLike;
        }

        $whereSql = $whereParts === [] ? '' : (' WHERE ' . implode(' AND ', $whereParts));

        // Count query
        $countSql = "SELECT COUNT(*) AS total_count $fromClause $whereSql";
        $countStmt = $conn->prepare($countSql);
        if (!$countStmt) {
            return null;
        }
        if ($types !== '') {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalItems = (int) (($countResult && ($row = $countResult->fetch_assoc())) ? ($row['total_count'] ?? 0) : 0);
        $countStmt->close();

        $totalPages = max(1, (int) ceil($totalItems / $safePerPage));
        $safePage = max(1, min($safePage, $totalPages));
        $offset = ($safePage - 1) * $safePerPage;

        // Paged rows query — join bookings and tours; remove non-existent customer columns
        $selectSql = "
            SELECT
                c.id,
                c.full_name,
                c.email,
                t.destination AS destination,
                b.booking_status AS booking_status,
                b.payment_status AS booking_payment_status,
                b.created_at AS booking_created_at,
                ae.case_status AS case_status,
                COALESCE(pa.documents_status, 'not started') AS passport_documents_status,
                COALESCE(pa.application_status, 'not started') AS passport_application_status,
                p.status AS latest_payment_status,
                $docStageExpr AS document_stage,
                $paymentExpr AS effective_payment_status,
                $hasCancelledBookingExpr AS has_cancelled_booking
            $fromClause
            $whereSql
            ORDER BY c.full_name ASC
            LIMIT ? OFFSET ?
        ";
        $selectStmt = $conn->prepare($selectSql);
        if (!$selectStmt) {
            return null;
        }

        $selectTypes = $types . 'ii';
        $selectParams = $params;
        $selectParams[] = $safePerPage;
        $selectParams[] = $offset;
        $selectStmt->bind_param($selectTypes, ...$selectParams);
        $selectStmt->execute();
        $result = $selectStmt->get_result();

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $bookingCreatedRaw = (string) ($row['booking_created_at'] ?? '');
            $effectivePaymentStatus = (string) ($row['effective_payment_status'] ?? ($row['latest_payment_status'] ?? $row['booking_payment_status'] ?? 'pending'));
            $paymentStatus = $this->toDisplayCase($this->normalizePaymentStatus($effectivePaymentStatus));
            $status = $this->toDisplayCase((string) ($row['booking_status'] ?? 'pending'));
            $documentsStatus = $this->toDisplayCase((string) ($row['document_stage'] ?? 'not started'));
            $admissionStatus = 'N/A';
            $hasCancelledBooking = ((int) ($row['has_cancelled_booking'] ?? 0)) === 1
                || strtolower(trim((string) ($row['booking_status'] ?? ''))) === 'cancelled';

            if ($normalizedTab === 'refund') {
                $paymentStatus = 'Refunded';
            }
            if ($hasCancelledBooking) {
                $paymentStatus = 'Cancelled';
                $documentsStatus = 'Cancelled';
            }

            $items[] = [
                'id' => (int) ($row['id'] ?? 0),
                'name' => (string) ($row['full_name'] ?? 'Unknown Customer'),
                'email' => (string) ($row['email'] ?? ''),
                'destination' => (string) ($row['destination'] ?? 'N/A'),
                'lastContacted' => 'N/A',
                'lastContactedDate' => $bookingCreatedRaw !== '' ? date('Y-m-d', strtotime($bookingCreatedRaw)) : date('Y-m-d'),
                'createdDate' => $bookingCreatedRaw !== '' ? date('Y-m-d', strtotime($bookingCreatedRaw)) : date('Y-m-d'),
                'paymentStatus' => $paymentStatus,
                'paymentSource' => isset($row['latest_payment_status']) && $row['latest_payment_status'] !== null ? 'payments' : 'bookings',
                'progress' => 0,
                'status' => $status,
                'documentsStatus' => $documentsStatus,
                'admissionStatus' => $admissionStatus,
                'refund' => ((string) ($row['case_status'] ?? '')) === 'refund' ? 'true' : 'false'
            ];
        }
        $selectStmt->close();

        // Build filtered status dataset for stats cards (across all filtered rows, not just current page).
        $allFiltered = [];
        $statsSql = "
            SELECT
                $paymentExpr AS effective_payment_status
            $fromClause
            $whereSql
        ";
        $statsStmt = $conn->prepare($statsSql);
        if ($statsStmt) {
            if ($types !== '') {
                $statsStmt->bind_param($types, ...$params);
            }
            $statsStmt->execute();
            $statsResult = $statsStmt->get_result();
            while ($statsRow = $statsResult->fetch_assoc()) {
                $paymentStatus = $this->toDisplayCase((string) ($statsRow['effective_payment_status'] ?? 'unpaid'));
                if ($normalizedTab === 'refund') {
                    $paymentStatus = 'Refunded';
                }
                $allFiltered[] = [
                    'paymentStatus' => $paymentStatus,
                    'admissionStatus' => 'Pending',
                    'refund' => 'false'
                ];
            }
            $statsStmt->close();
        }

        return [
            'items' => $items,
            'allFiltered' => $allFiltered,
            'page' => $safePage,
            'perPage' => $safePerPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'offset' => $offset
        ];
    }

    public function getCustomers(int $count = self::DEFAULT_TOTAL_CUSTOMERS): array
    {
        $dbCustomers = $this->getCustomersFromDatabase();
        if (!empty($dbCustomers)) {
            return $dbCustomers;
        }

        if (self::$cache !== null && count(self::$cache) === $count) {
            return self::$cache;
        }

        $customers = [];
        for ($i = 0; $i < $count; $i++) {
            $customers[] = $this->generateCustomer($i);
        }

        usort($customers, static function (array $left, array $right): int {
            return strcmp($left['name'], $right['name']);
        });

        self::$cache = $customers;
        return self::$cache;
    }

    private function getConnection(): ?mysqli
    {
        if ($this->conn instanceof mysqli && !$this->conn->connect_error) {
            return $this->conn;
        }

        try {
            $this->conn = getDBConnection();
        } catch (Throwable $exception) {
            $this->conn = null;
        }

        return $this->conn;
    }

    private function getCustomersFromDatabase(): array
    {
        $conn = $this->getConnection();
        if (!($conn instanceof mysqli)) {
            return [];
        }

        $sql = "SELECT pa1.* FROM account_executive ae
JOIN bookings b ON ae.booking_id = b.id
JOIN customers c ON b.customer_id = c.id
LEFT JOIN passport_applications pa1 ON pa1.booking_id = ae.booking_id
WHERE c.id = ?";
        $result = $conn->query($sql);
        if (!($result instanceof mysqli_result)) {
            return [];
        }

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $bookingCreatedRaw = (string) ($row['booking_created_at'] ?? '');
            $customerCreatedRaw = (string) ($row['created_at'] ?? '');
            $effectivePaymentStatus = (string) ($row['latest_payment_status'] ?? $row['booking_payment_status'] ?? 'pending');
            $paymentStatus = $this->toDisplayCase($this->normalizePaymentStatus($effectivePaymentStatus));

            $status = $this->toDisplayCase((string) ($row['case_status'] ?? $row['booking_status'] ?? 'pending'));
            $documentsStatusRaw = (string) ($row['passport_documents_status'] ?? 'not started');
            $documentsStatus = $this->toDisplayCase($documentsStatusRaw);
            $hasCancelledBooking = ((int) ($row['has_cancelled_booking'] ?? 0)) === 1
                || strtolower(trim((string) ($row['booking_status'] ?? ''))) === 'cancelled';
            if ($hasCancelledBooking) {
                $paymentStatus = 'Cancelled';
                $documentsStatus = 'Cancelled';
            }

            $rows[] = [
                'id' => (int) ($row['id'] ?? 0),
                'name' => (string) ($row['full_name'] ?? 'Unknown Customer'),
                'destination' => (string) ($row['destination'] ?? 'N/A'),
                'lastContacted' => 'N/A',
                'lastContactedDate' => $bookingCreatedRaw !== '' ? date('Y-m-d', strtotime($bookingCreatedRaw)) : date('Y-m-d'),
                'createdDate' => $customerCreatedRaw !== '' ? date('Y-m-d', strtotime($customerCreatedRaw)) : date('Y-m-d'),
                'paymentStatus' => $paymentStatus,
                'paymentSource' => isset($row['latest_payment_status']) && $row['latest_payment_status'] !== null
                    ? 'payments'
                    : 'bookings',
                'progress' => 0,
                'status' => $status,
                'documentsStatus' => $documentsStatus,
                'admissionStatus' => 'Pending',
                'refund' => 'false'
            ];
        }

        return $rows;
    }

    private function toDisplayCase(string $value): string
    {
        $normalized = str_replace('_', ' ', trim($value));
        return ucwords(strtolower($normalized));
    }

    private function normalizePaymentStatus(string $status): string
    {
        $normalized = strtolower(trim($status));
        return match ($normalized) {
            'partial' => 'partially paid',
            default => $normalized === '' ? 'pending' : $normalized,
        };
    }

    public function buildStats(array $customers): array
    {
        $stats = ['paid' => 0, 'admitted' => 0, 'pending' => 0, 'unpaid' => 0];

        foreach ($customers as $customer) {
            if ($customer['paymentStatus'] === 'Paid') {
                $stats['paid']++;
            }
            if ($customer['admissionStatus'] === 'Admitted') {
                $stats['admitted']++;
            }
            if ($customer['admissionStatus'] === 'Pending') {
                $stats['pending']++;
            }
            if ($customer['paymentStatus'] !== 'Paid') {
                $stats['unpaid']++;
            }
        }

        return $stats;
    }

    private function generateCustomer(int $index): array
    {
        $names = ['Robert Brown', 'Emily Davis', 'Jane Doe', 'Sarah Johnson', 'John Smith'];
        $destinations = ['USA', 'France', 'Canada', 'Australia', 'Japan', 'UK'];
        $payments = ['Paid', 'Unpaid', 'Overdue', 'Partially Paid'];
        $statuses = ['Processing', 'Pending', 'Cancelled', 'Finished'];
        $createdDaysAgo = $index % 61;
        $lastContactedDaysAgo = ($index * 3) % 15;
        $createdDate = date('Y-m-d', strtotime("-{$createdDaysAgo} days"));
        $lastContactedDate = date(
            'Y-m-d H:i:s',
            strtotime(
                "-{$lastContactedDaysAgo} days -" . ($index % 24) . " hours -" . (($index * 7) % 60) . " minutes"
            )
        );

        $resolvedStatus = $statuses[($index * 3) % count($statuses)];
        $resolvedPayment = $payments[$index % count($payments)];
        if ($createdDaysAgo <= 7) {
            // New clients should appear with early-stage document and payment indicators.
            $resolvedStatus = 'Pending';
            $resolvedPayment = ($index % 2 === 0) ? 'Unpaid' : 'Partially Paid';
        }

        return [
            'id' => $index + 1,
            'name' => $names[$index % count($names)],
            'destination' => $destinations[($index * 2) % count($destinations)],
            'lastContacted' => date('m/d/Y - h:i a', strtotime($lastContactedDate)),
            'lastContactedDate' => date('Y-m-d', strtotime($lastContactedDate)),
            'createdDate' => $createdDate,
            'paymentStatus' => $resolvedPayment,
            'progress' => 10 + (($index * 11) % 91),
            'status' => $resolvedStatus,
            'admissionStatus' => ($index % 2) === 0 ? 'Admitted' : 'Pending',
            'refund' => ($index % 10) > 7 ? 'true' : 'false'
        ];
    }

    private function applyFilters(
        array $customers,
        string $tabFilter,
        string $paymentFilter,
        string $statusFilter,
        string $search
    ): array
    {
        $normalizedTab = $this->normalizeValue($tabFilter);
        $normalizedPayment = $this->normalizeValue($paymentFilter);
        $normalizedStatus = $this->normalizeValue($statusFilter);
        $normalizedSearch = $this->normalizeValue($search);
        $today = strtotime(date('Y-m-d'));

        return array_values(array_filter($customers, function (array $customer) use (
            $normalizedTab,
            $normalizedPayment,
            $normalizedStatus,
            $normalizedSearch,
            $today
        ): bool {
            $payment = $this->normalizeValue((string) ($customer['paymentStatus'] ?? ''));
            $status = $this->normalizeValue((string) ($customer['status'] ?? ''));
            $documentsStatus = $this->normalizeValue((string) ($customer['documentsStatus'] ?? ''));
            $refund = $this->normalizeValue((string) ($customer['refund'] ?? '')) === 'true';
            $createdDate = strtotime((string) ($customer['createdDate'] ?? ''));

            $matchesTab = true;
            switch ($normalizedTab) {
                case 'new':
                    if ($createdDate === false) {
                        $matchesTab = false;
                        break;
                    }
                    $createdDays = ($today - $createdDate) / 86400;
                    $matchesTab = $createdDays >= 0 && $createdDays <= 7;
                    break;
                case 'for-follow-up':
                    // Follow-up focuses on pending document status regardless of payment.
                    $matchesTab = in_array($documentsStatus, ['pending', 'missing', 'not started'], true);
                    break;
                case 'ongoing':
                    // Ongoing focuses on documents that are actively processing.
                    $matchesTab = in_array($documentsStatus, ['submitted', 'processing', 'under review'], true);
                    break;
                case 'payment-issues':
                    // Payment Issues focuses strictly on unpaid/overdue customers.
                    $matchesTab = in_array($payment, ['unpaid', 'overdue'], true);
                    break;
                case 'finished':
                    // Finished requires both complete documents and paid payment status.
                    $matchesTab = in_array($documentsStatus, ['approved', 'complete', 'completed'], true) && $payment === 'paid';
                    break;
                case 'refund':
                    // Refund tab is payment-focused; status should not restrict rows.
                    $matchesTab = $refund;
                    break;
                case 'cancel-booking':
                    // Cancel booking tab focuses on cancelled bookings or cancelled account status.
                    $matchesTab = $status === 'cancelled';
                    break;
                case 'all':
                default:
                    $matchesTab = true;
                    break;
            }

            if (!$matchesTab) {
                return false;
            }

            $ignorePaymentFilter = in_array($normalizedTab, ['new', 'for-follow-up', 'ongoing', 'refund'], true);
            if (!$ignorePaymentFilter && $normalizedPayment !== 'all' && $payment !== $normalizedPayment) {
                return false;
            }

            $ignoreStatusFilter = in_array($normalizedTab, ['new', 'for-follow-up', 'ongoing', 'payment-issues', 'refund'], true);
            if (!$ignoreStatusFilter && $normalizedStatus !== 'all' && $status !== $normalizedStatus) {
                return false;
            }

            if ($normalizedSearch === '') {
                return true;
            }

            $haystack = $this->normalizeValue(implode(' ', [
                (string) ($customer['name'] ?? ''),
                (string) ($customer['destination'] ?? ''),
                (string) ($customer['paymentStatus'] ?? ''),
                (string) ($customer['status'] ?? '')
            ]));

            return str_contains($haystack, $normalizedSearch);
        }));
    }

    public function normalizeValue(string $value): string
    {
        return strtolower(trim($value));
    }
}
