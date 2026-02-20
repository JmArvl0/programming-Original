<?php

class CrmModel
{
    public function getStats(): array
    {
        return [
            'total' => 50,
            'activePercent' => 62,
            'active' => 31,
            'vip' => 14,
            'gold' => 16,
            'silver' => 13,
            'new' => 7,
            'rating' => 5.8,
            'revenue' => 5571161,
            'avgLifetime' => 111423,
            'avgTrips' => 12.5,
            'followups' => 32
        ];
    }

    public function getCustomers(int $count = 30): array
    {
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
            $tier = $tiers[array_rand($tiers)];
            $customers[] = [
                'name' => "Customer {$i}",
                'email' => "customer{$i}@mail.com",
                'tier' => $tier,
                'tierClass' => $tierClasses[$tier],
                'tierIcon' => $tierIcons[$tier] ?? '&#9679;',
                'lifetimeValue' => rand(50000, 500000),
                'totalTrips' => rand(1, 25),
                'lastContactedDays' => rand(1, 30)
            ];
        }

        return $customers;
    }
}
