<?php

class AccountExecutiveModel
{
    public function getCustomers(int $count = 20): array
    {
        $customers = [];
        for ($i = 0; $i < $count; $i++) {
            $customers[] = $this->generateCustomer($i);
        }

        usort($customers, static function (array $left, array $right): int {
            return strcmp($left['name'], $right['name']);
        });

        return array_map([$this, 'decorateCustomer'], $customers);
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
        $createdDaysAgo = rand(0, 60);
        $lastContactedDaysAgo = rand(0, 14);
        $createdDate = date('Y-m-d', strtotime("-{$createdDaysAgo} days"));
        $lastContactedDate = date(
            'Y-m-d H:i:s',
            strtotime("-{$lastContactedDaysAgo} days -" . rand(0, 23) . " hours -" . rand(0, 59) . " minutes")
        );

        return [
            'id' => $index + 1,
            'name' => $names[array_rand($names)],
            'destination' => $destinations[array_rand($destinations)],
            'lastContacted' => date('m/d/Y - h:i a', strtotime($lastContactedDate)),
            'lastContactedDate' => date('Y-m-d', strtotime($lastContactedDate)),
            'createdDate' => $createdDate,
            'paymentStatus' => $payments[array_rand($payments)],
            'progress' => rand(10, 100),
            'status' => $statuses[array_rand($statuses)],
            'admissionStatus' => rand(0, 1) ? 'Admitted' : 'Pending',
            'refund' => rand(1, 10) > 7 ? 'true' : 'false'
        ];
    }

    private function decorateCustomer(array $customer): array
    {
        $customer['paymentStatusNormalized'] = $this->normalizeValue($customer['paymentStatus']);
        $customer['statusNormalized'] = $this->normalizeValue($customer['status']);
        $customer['paymentBadgeClass'] = $this->paymentBadgeClass($customer['paymentStatus']);
        $customer['statusBadgeClass'] = $this->statusBadgeClass($customer['status']);

        return $customer;
    }

    private function normalizeValue(string $value): string
    {
        return strtolower(trim($value));
    }

    private function paymentBadgeClass(string $paymentStatus): string
    {
        return match ($this->normalizeValue($paymentStatus)) {
            'paid' => 'bg-success',
            'unpaid', 'overdue' => 'bg-danger',
            'partially paid' => 'bg-warning text-dark',
            default => 'bg-secondary'
        };
    }

    private function statusBadgeClass(string $status): string
    {
        return match ($this->normalizeValue($status)) {
            'pending' => 'bg-warning text-dark',
            'processing' => 'bg-primary',
            'cancelled' => 'bg-danger',
            'finished' => 'bg-success',
            default => 'bg-secondary'
        };
    }
}
