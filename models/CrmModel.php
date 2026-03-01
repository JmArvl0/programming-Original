<?php
require_once __DIR__ . '/../config/database.php';

class CRMModel
{
    public static function getCompletedBookingCount(int $customerId): int
    {
        $conn = getDBConnection();
        if (!($conn instanceof mysqli)) {
            return 0;
        }

        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS cnt FROM crm_bookings WHERE customer_id = ? AND completed = 1"
        );
        $count = 0;
        if ($stmt) {
            $stmt->bind_param('i', $customerId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $count = (int) ($row['cnt'] ?? 0);
            $stmt->close();
        }
        closeDBConnection($conn);
        return $count;
    }

    public static function calculateTierByCount(int $count): string
    {
        if ($count >= 6) return 'VIP';
        if ($count >= 3) return 'Gold';
        if ($count >= 1) return 'Silver';
        return 'Newcomer';
    }

    // Dynamic approach: compute on request
    public static function getTierForCustomer(int $customerId): string
    {
        $count = self::getCompletedBookingCount($customerId);
        return self::calculateTierByCount($count);
    }

    // Stored approach: update customers.tier column (optional)
    public static function updateStoredTier(int $customerId): bool
    {
        $tier = self::getTierForCustomer($customerId);
        $conn = getDBConnection();
        if (!($conn instanceof mysqli)) {
            return false;
        }
        // Make sure customers table has `tier` VARCHAR(32) if using stored approach
        $stmt = $conn->prepare("UPDATE customers SET tier = ? WHERE id = ?");
        if (!$stmt) {
            closeDBConnection($conn);
            return false;
        }
        $stmt->bind_param('si', $tier, $customerId);
        $ok = $stmt->execute();
        $stmt->close();
        closeDBConnection($conn);
        return (bool) $ok;
    }
}
<?php

class CrmModel
{
    private const DEFAULT_TOTAL_CUSTOMERS = 250;
    private const RATING = 4.8;
    private static ?array $customersCache = null;
    private static ?array $statsCache = null;

    private function cacheFetch(string $key): ?array
    {
        if (function_exists('apcu_fetch') && ini_get('apc.enabled')) {
            $success = false;
            $value = apcu_fetch($key, $success);
            if ($success && is_array($value)) {
                return $value;
            }
        }
        return null;
    }

    private function cacheStore(string $key, array $value, int $ttl = 120): void
    {
        if (function_exists('apcu_store') && ini_get('apc.enabled')) {
            apcu_store($key, $value, $ttl);
        }
    }

    private function getAllCustomers(int $count = self::DEFAULT_TOTAL_CUSTOMERS): array
    {
        if (self::$customersCache !== null && count(self::$customersCache) === $count) {
            return self::$customersCache;
        }
        $cacheKey = "crm_customers_{$count}";
        $cached = $this->cacheFetch($cacheKey);
        if ($cached !== null) {
            self::$customersCache = $cached;
            return self::$customersCache;
        }

        $tiers = ['VIP', 'Gold', 'Silver', 'New'];
        $tierClasses = [
            'VIP' => 'badge-vip',
            'Gold' => 'badge-gold',
            'Silver' => 'badge-silver',
            'New' => 'badge-new'
        ];
        $tierIcons = [
            'VIP' => '&#9733;',
            'Gold' => '&#9670;',
            'Silver' => '&#9679;',
            'New' => '&#10148;'
        ];

        $customers = [];
        for ($i = 1; $i <= $count; $i++) {
            $tier = $tiers[$i % count($tiers)];
            $customers[] = [
                'id' => $i,
                'name' => "Customer {$i}",
                'email' => "customer{$i}@mail.com",
                'tier' => $tier,
                'tierClass' => $tierClasses[$tier],
                'tierIcon' => $tierIcons[$tier] ?? '&#9679;',
                'lifetimeValue' => 50000 + (($i * 13791) % 450001),
                'totalTrips' => 1 + ($i % 25),
                'lastContactedDays' => 1 + ($i % 30)
            ];
        }

        self::$customersCache = $customers;
        $this->cacheStore($cacheKey, self::$customersCache, 300);
        return self::$customersCache;
    }

    public function getDashboardStats(): array
    {
        if (self::$statsCache !== null) {
            return self::$statsCache;
        }
        $cachedStats = $this->cacheFetch('crm_dashboard_stats');
        if ($cachedStats !== null) {
            self::$statsCache = $cachedStats;
            return self::$statsCache;
        }

        $customers = $this->getAllCustomers();
        $total = count($customers);
        $vip = 0;
        $gold = 0;
        $silver = 0;
        $new = 0;
        $active = 0;

        foreach ($customers as $customer) {
            $tier = $customer['tier'] ?? '';
            if ($tier === 'VIP') {
                $vip++;
            } elseif ($tier === 'Gold') {
                $gold++;
            } elseif ($tier === 'Silver') {
                $silver++;
            } else {
                $new++;
            }

            if ((int) ($customer['lastContactedDays'] ?? 0) <= 15) {
                $active++;
            }
        }

        $safeTotal = max($total, 1);
        $activePercent = (int) round(($active / $safeTotal) * 100);

        self::$statsCache = [
            'total' => $total,
            'activePercent' => $activePercent,
            'active' => $active,
            'vip' => $vip,
            'gold' => $gold,
            'silver' => $silver,
            'new' => $new,
            'rating' => self::RATING,
            'vipPercent' => round(($vip / $safeTotal) * 100, 2),
            'goldPercent' => round(($gold / $safeTotal) * 100, 2),
            'silverPercent' => round(($silver / $safeTotal) * 100, 2),
            'newPercent' => round(($new / $safeTotal) * 100, 2)
        ];
        $this->cacheStore('crm_dashboard_stats', self::$statsCache, 120);

        return self::$statsCache;
    }

    public function getCustomersPage(int $page = 1, int $perPage = 10, string $tier = 'all', string $search = ''): array
    {
        $customers = $this->getAllCustomers();
        $normalizedTier = strtolower(trim($tier));
        $normalizedSearch = strtolower(trim($search));

        if ($normalizedTier !== '' && $normalizedTier !== 'all') {
            $customers = array_values(array_filter($customers, static function (array $customer) use ($normalizedTier): bool {
                return strtolower((string) ($customer['tier'] ?? '')) === $normalizedTier;
            }));
        }

        if ($normalizedSearch !== '') {
            $customers = array_values(array_filter($customers, static function (array $customer) use ($normalizedSearch): bool {
                $name = strtolower((string) ($customer['name'] ?? ''));
                $email = strtolower((string) ($customer['email'] ?? ''));
                return str_contains($name, $normalizedSearch) || str_contains($email, $normalizedSearch);
            }));
        }

        $totalItems = count($customers);
        $safePerPage = max(1, min($perPage, 100));
        $totalPages = max(1, (int) ceil($totalItems / $safePerPage));
        $safePage = max(1, min($page, $totalPages));
        $offset = ($safePage - 1) * $safePerPage;
        $items = array_slice($customers, $offset, $safePerPage);

        return [
            'items' => $items,
            'page' => $safePage,
            'perPage' => $safePerPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'offset' => $offset
        ];
    }
}
