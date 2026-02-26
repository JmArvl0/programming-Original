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

        $sql = 'SELECT id, full_name, destination, last_contacted_at, created_at, payment_status, status, admission_status, progress, refund_flag
                FROM customers
                ORDER BY full_name ASC';
        $result = $conn->query($sql);
        if (!($result instanceof mysqli_result)) {
            return [];
        }

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $lastContactedRaw = (string) ($row['last_contacted_at'] ?? '');
            $createdDateRaw = (string) ($row['created_at'] ?? '');
            $paymentStatus = $this->toDisplayCase((string) ($row['payment_status'] ?? 'pending'));
            $status = $this->toDisplayCase((string) ($row['status'] ?? 'pending'));
            $admissionStatus = $this->toDisplayCase((string) ($row['admission_status'] ?? 'pending'));

            $rows[] = [
                'id' => (int) ($row['id'] ?? 0),
                'name' => (string) ($row['full_name'] ?? 'Unknown Customer'),
                'destination' => (string) ($row['destination'] ?? 'N/A'),
                'lastContacted' => $lastContactedRaw !== '' ? date('m/d/Y - h:i a', strtotime($lastContactedRaw)) : 'N/A',
                'lastContactedDate' => $lastContactedRaw !== '' ? date('Y-m-d', strtotime($lastContactedRaw)) : date('Y-m-d'),
                'createdDate' => $createdDateRaw !== '' ? date('Y-m-d', strtotime($createdDateRaw)) : date('Y-m-d'),
                'paymentStatus' => $paymentStatus,
                'progress' => max(0, min(100, (int) ($row['progress'] ?? 0))),
                'status' => $status,
                'admissionStatus' => $admissionStatus,
                'refund' => ((int) ($row['refund_flag'] ?? 0)) === 1 ? 'true' : 'false'
            ];
        }

        return $rows;
    }

    private function toDisplayCase(string $value): string
    {
        $normalized = str_replace('_', ' ', trim($value));
        return ucwords(strtolower($normalized));
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
                    $matchesTab = $status === 'pending';
                    break;
                case 'ongoing':
                    // Ongoing focuses on documents that are actively processing.
                    $matchesTab = $status === 'processing';
                    break;
                case 'payment-issues':
                    // Payment Issues focuses strictly on unpaid/overdue customers.
                    $matchesTab = in_array($payment, ['unpaid', 'overdue'], true);
                    break;
                case 'finished':
                    // Finished requires both complete documents and paid payment status.
                    $matchesTab = $status === 'finished' && $payment === 'paid';
                    break;
                case 'refund':
                    // Refund tab is payment-focused; status should not restrict rows.
                    $matchesTab = $refund;
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
