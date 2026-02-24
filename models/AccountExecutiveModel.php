<?php

class AccountExecutiveModel
{
    private const DEFAULT_TOTAL_CUSTOMERS = 300;
    private static ?array $cache = null;

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

    public function getCustomers(int $count = self::DEFAULT_TOTAL_CUSTOMERS): array
    {
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

        return [
            'id' => $index + 1,
            'name' => $names[$index % count($names)],
            'destination' => $destinations[($index * 2) % count($destinations)],
            'lastContacted' => date('m/d/Y - h:i a', strtotime($lastContactedDate)),
            'lastContactedDate' => date('Y-m-d', strtotime($lastContactedDate)),
            'createdDate' => $createdDate,
            'paymentStatus' => $payments[$index % count($payments)],
            'progress' => 10 + (($index * 11) % 91),
            'status' => $statuses[($index * 3) % count($statuses)],
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
            $progress = (int) ($customer['progress'] ?? 0);
            $refund = $this->normalizeValue((string) ($customer['refund'] ?? '')) === 'true';
            $createdDate = strtotime((string) ($customer['createdDate'] ?? ''));
            $lastContactedDate = strtotime((string) ($customer['lastContactedDate'] ?? ''));
            $lastContactedDays = ($lastContactedDate !== false) ? (($today - $lastContactedDate) / 86400) : INF;

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
                    $matchesTab = $status === 'pending' && $progress < 50 && $lastContactedDays > 3;
                    break;
                case 'ongoing':
                    $matchesTab = $status === 'processing' && $progress >= 20 && $progress <= 99;
                    break;
                case 'payment-issues':
                    $matchesTab = in_array($payment, ['unpaid', 'partially paid', 'overdue'], true);
                    break;
                case 'finished':
                    $matchesTab = $status === 'finished' || $progress === 100;
                    break;
                case 'refund':
                    $matchesTab = $refund || ($status === 'cancelled' && in_array($payment, ['paid', 'partially paid'], true));
                    break;
                case 'all':
                default:
                    $matchesTab = true;
                    break;
            }

            if (!$matchesTab) {
                return false;
            }

            if ($normalizedPayment !== 'all' && $payment !== $normalizedPayment) {
                return false;
            }

            if ($normalizedStatus !== 'all' && $status !== $normalizedStatus) {
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
