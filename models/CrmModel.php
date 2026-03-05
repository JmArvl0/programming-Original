<?php
require_once __DIR__ . '/../config/database.php';

class CrmTierModel
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

// Fix for undefined function 'apcu_fetch' and 'apcu_store'
if (!function_exists('apcu_fetch')) {
    function apcu_fetch($key, &$success = null) {
        $success = false;
        return null;
    }
}

if (!function_exists('apcu_store')) {
    function apcu_store($key, $value, $ttl = 0) {
        return false;
    }
}

// Fix for redeclared class
if (!class_exists('CrmModel')) {
    class CrmModel
    {
        private const DEFAULT_TOTAL_CUSTOMERS = 250;
        private const RATING = 4.8;
        
        // FIX 1: Make these non-static since we're using $this
        private ?array $customersCache = null;
        private ?array $statsCache = null;

        private function cacheFetch(string $key): ?array
        {
            if (function_exists('apcu_fetch') && ini_get('apc.enabled')) {
                $success = false;
                $value = @apcu_fetch($key, $success);
                if ($success && is_array($value)) {
                    return $value;
                }
            }
            return null;
        }

        private function cacheStore(string $key, array $value, int $ttl = 120): void
        {
            if (function_exists('apcu_store') && ini_get('apc.enabled')) {
                @apcu_store($key, $value, $ttl);
            }
        }

        private function getAllCustomers(int $count = self::DEFAULT_TOTAL_CUSTOMERS): array
        {
            // FIX 2: Use $this->customersCache instead of self::$customersCache
            if ($this->customersCache !== null && count($this->customersCache) === $count) {
                return $this->customersCache;
            }
            
            $cacheKey = "crm_customers_{$count}";
            $cached = $this->cacheFetch($cacheKey);
            if ($cached !== null) {
                $this->customersCache = $cached;
                return $this->customersCache;
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
                $tierKey = $tiers[$i % count($tiers)];
                
                // FIX 3: Use null coalescing operators to prevent undefined indexes
                $customers[] = [
                    'id' => $i,
                    'name' => "Customer {$i}",
                    'email' => "customer{$i}@mail.com",
                    'tier' => $tierKey,
                    'tierClass' => $tierClasses[$tierKey] ?? 'badge-default', // Added fallback
                    'tierIcon' => $tierIcons[$tierKey] ?? '&#9679;',
                    'lifetimeValue' => 50000 + (($i * 13791) % 450001),
                    'totalTrips' => 1 + ($i % 25),
                    'lastContactedDays' => 1 + ($i % 30)
                ];
            }

            // FIX 4: Use $this->customersCache instead of self::$customersCache
            $this->customersCache = $customers;
            $this->cacheStore($cacheKey, $this->customersCache, 300);
            return $this->customersCache;
        }

        public function getDashboardStats(): array
        {
            // FIX 5: Use $this->statsCache instead of self::$statsCache
            if ($this->statsCache !== null) {
                return $this->statsCache;
            }
            
            $cachedStats = $this->cacheFetch('crm_dashboard_stats');
            if ($cachedStats !== null) {
                $this->statsCache = $cachedStats;
                return $this->statsCache;
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

            // FIX 6: Use $this->statsCache instead of self::$statsCache
            $this->statsCache = [
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
            
            $this->cacheStore('crm_dashboard_stats', $this->statsCache, 120);

            return $this->statsCache;
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
}
