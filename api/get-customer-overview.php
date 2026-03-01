<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

function respond(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function normalizeStatus(string $value): string
{
    $v = strtolower(trim($value));
    $success = [
        'approved', 'complete', 'completed', 'paid', 'verified', 'visa issued',
        'confirmed', 'active', 'open', 'admitted', 'finished', 'submitted'
    ];
    $danger = [
        'rejected', 'denied', 'failed', 'cancelled', 'overdue', 'missing', 'action required'
    ];

    if (in_array($v, $success, true)) {
        return 'complete';
    }
    if (in_array($v, $danger, true)) {
        return 'rejected';
    }
    return 'pending';
}

function engagementStatus(?string $latestInteraction, ?string $lastContacted): string
{
    $latest = $latestInteraction ?: $lastContacted;
    if ($latest === null || $latest === '') {
        return 'pending';
    }

    $interactionTime = strtotime($latest);
    if ($interactionTime === false) {
        return 'pending';
    }

    $daysDiff = (time() - $interactionTime) / 86400;
    if ($daysDiff <= 7) {
        return 'active';
    }
    if ($daysDiff <= 30) {
        return 'follow-up';
    }
    return 'inactive';
}

try {
    $customerId = isset($_GET['customer_id']) ? (int) $_GET['customer_id'] : 0;
    if ($customerId <= 0) {
        respond(400, ['success' => false, 'message' => 'Invalid customer_id']);
    }

    $conn = getDBConnection();

    // Basic customer info
    $stmt = $conn->prepare(
        "SELECT id, full_name, email, status, tier, payment_status, last_contacted_at
         FROM customers
         WHERE id = ?
         LIMIT 1"
    );
    if (!$stmt) {
        throw new RuntimeException('Unable to prepare customer query.');
    }
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$customer) {
        closeDBConnection($conn);
        respond(404, ['success' => false, 'message' => 'Customer not found']);
    }

    // CRM summary
    $stmt = $conn->prepare(
        "SELECT COUNT(ci.id) AS total_transactions, MAX(ci.created_at) AS latest_interaction
         FROM crm_interactions ci
         WHERE ci.customer_id = ?"
    );
    if (!$stmt) {
        throw new RuntimeException('Unable to prepare CRM summary query.');
    }
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $crmRow = $stmt->get_result()->fetch_assoc() ?: [];
    $stmt->close();

    $crm = [
        'customer_tier_level' => (string) ($customer['tier'] ?? 'new'),
        'engagement_status' => engagementStatus(
            $crmRow['latest_interaction'] ?? null,
            $customer['last_contacted_at'] ?? null
        ),
        'total_transactions' => (int) ($crmRow['total_transactions'] ?? 0)
    ];

    // Passport & Visa summary (latest application + document rollup)
    $stmt = $conn->prepare(
        "SELECT id, documents_status, application_status
         FROM passport_applications
         WHERE customer_id = ?
         ORDER BY updated_at DESC, id DESC
         LIMIT 1"
    );
    if (!$stmt) {
        throw new RuntimeException('Unable to prepare Passport/Visa summary query.');
    }
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $passportApp = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $documentSubmissionStatus = 'pending';
    $verificationStatus = 'pending';
    $complianceStatus = 'pending';

    if ($passportApp) {
        $documentsStatusRaw = (string) ($passportApp['documents_status'] ?? 'not started');
        $applicationStatusRaw = (string) ($passportApp['application_status'] ?? 'not started');

        $documentSubmissionStatus = normalizeStatus($documentsStatusRaw) === 'complete'
            ? 'complete'
            : (normalizeStatus($documentsStatusRaw) === 'rejected' ? 'rejected' : 'pending');

        $verificationStatus = normalizeStatus($applicationStatusRaw) === 'complete'
            ? 'complete'
            : (normalizeStatus($applicationStatusRaw) === 'rejected' ? 'rejected' : 'pending');

        $passportApplicationId = (int) $passportApp['id'];
        $stmt = $conn->prepare(
            "SELECT
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_count,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count,
                SUM(CASE WHEN status = 'missing' THEN 1 ELSE 0 END) AS missing_count,
                COUNT(*) AS total_count
             FROM passport_documents
             WHERE passport_application_id = ?"
        );
        if ($stmt) {
            $stmt->bind_param('i', $passportApplicationId);
            $stmt->execute();
            $docSummary = $stmt->get_result()->fetch_assoc() ?: [];
            $stmt->close();

            $rejectedCount = (int) ($docSummary['rejected_count'] ?? 0);
            $missingCount = (int) ($docSummary['missing_count'] ?? 0);
            $approvedCount = (int) ($docSummary['approved_count'] ?? 0);
            $totalCount = (int) ($docSummary['total_count'] ?? 0);

            if ($rejectedCount > 0) {
                $complianceStatus = 'rejected';
            } elseif ($missingCount > 0) {
                $complianceStatus = 'pending';
            } elseif ($totalCount > 0 && $approvedCount === $totalCount) {
                $complianceStatus = 'complete';
            } else {
                $complianceStatus = $verificationStatus;
            }
        } else {
            $complianceStatus = $verificationStatus;
        }
    }

    $passportVisa = [
        'document_submission_status' => $documentSubmissionStatus,
        'verification_status' => $verificationStatus,
        'compliance_status' => $complianceStatus
    ];

    // Schedule & Rates summary
    $stmt = $conn->prepare(
        "SELECT b.booking_status, b.payment_status
         FROM bookings b
         INNER JOIN guests g ON g.id = b.guest_id
         WHERE g.customer_id = ?
         ORDER BY b.updated_at DESC, b.id DESC
         LIMIT 1"
    );
    if (!$stmt) {
        throw new RuntimeException('Unable to prepare Schedule/Rates summary query.');
    }
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $bookingRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $scheduleInquiryStatus = 'pending';
    $rateQuotationStatus = 'pending';
    if ($bookingRow) {
        $scheduleInquiryStatus = normalizeStatus((string) ($bookingRow['booking_status'] ?? 'pending')) === 'complete'
            ? 'complete'
            : (normalizeStatus((string) ($bookingRow['booking_status'] ?? 'pending')) === 'rejected' ? 'rejected' : 'pending');
        $rateQuotationStatus = normalizeStatus((string) ($bookingRow['payment_status'] ?? 'pending')) === 'complete'
            ? 'complete'
            : (normalizeStatus((string) ($bookingRow['payment_status'] ?? 'pending')) === 'rejected' ? 'rejected' : 'pending');
    }

    $scheduleRates = [
        'schedule_inquiry_status' => $scheduleInquiryStatus,
        'rate_quotation_status' => $rateQuotationStatus
    ];

    // Facilities & Reservation summary
    $stmt = $conn->prepare(
        "SELECT fr.status AS reservation_status
         FROM facility_reservations fr
         WHERE fr.customer_id = ?
         ORDER BY fr.updated_at DESC, fr.id DESC
         LIMIT 1"
    );
    if (!$stmt) {
        throw new RuntimeException('Unable to prepare facility reservation query.');
    }
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $facilityRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare(
        "SELECT p.status AS payment_status
         FROM payments p
         WHERE p.customer_id = ?
         ORDER BY p.updated_at DESC, p.id DESC
         LIMIT 1"
    );
    if (!$stmt) {
        throw new RuntimeException('Unable to prepare payments query.');
    }
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $paymentRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $reservationStatus = $facilityRow ? (string) ($facilityRow['reservation_status'] ?? 'pending') : 'pending';
    $paymentStatus = $paymentRow
        ? (string) ($paymentRow['payment_status'] ?? 'pending')
        : (string) ($customer['payment_status'] ?? 'pending');
    $paymentSource = $paymentRow ? 'payments' : 'customers';

    $facilitiesReservation = [
        'reservation_status' => normalizeStatus($reservationStatus) === 'complete'
            ? 'complete'
            : (normalizeStatus($reservationStatus) === 'rejected' ? 'rejected' : 'pending'),
        'payment_status' => normalizeStatus($paymentStatus) === 'complete'
            ? 'complete'
            : (normalizeStatus($paymentStatus) === 'rejected' ? 'rejected' : 'pending')
    ];

    closeDBConnection($conn);

    respond(200, [
        'success' => true,
        'data' => [
            'customer' => [
                'id' => (int) $customer['id'],
                'name' => (string) ($customer['full_name'] ?? ''),
                'full_name' => (string) ($customer['full_name'] ?? ''),
                'email' => (string) ($customer['email'] ?? ''),
                'phone' => (string) ($customer['phone'] ?? ''),
                'destination' => (string) ($customer['destination'] ?? ''),
                'payment_status' => (string) ($customer['payment_status'] ?? 'unpaid'),
                'progress' => (int) ($customer['progress'] ?? 0),
                'tier' => (string) ($customer['tier'] ?? 'new'),
                'admission_status' => (string) ($customer['admission_status'] ?? 'pending'),
                'refund_flag' => (int) ($customer['refund_flag'] ?? 0),
                'overall_status' => (string) ($customer['status'] ?? 'pending'),
                'status' => (string) ($customer['status'] ?? 'pending')
            ],
            'crm' => [
                'tier' => (string) ($crm['customer_tier_level'] ?? 'new'),
                'customer_tier_level' => (string) ($crm['customer_tier_level'] ?? 'new'),
                'engagement_status' => (string) ($crm['engagement_status'] ?? 'pending'),
                'total_transactions' => (int) ($crm['total_transactions'] ?? 0)
            ],
            'passport' => [
                'document_status' => (string) ($passportVisa['document_submission_status'] ?? 'pending'),
                'document_submission_status' => (string) ($passportVisa['document_submission_status'] ?? 'pending'),
                'verification_status' => (string) ($passportVisa['verification_status'] ?? 'pending'),
                'compliance_status' => (string) ($passportVisa['compliance_status'] ?? 'pending')
            ],
            'schedule' => [
                'inquiry_status' => (string) ($scheduleRates['schedule_inquiry_status'] ?? 'pending'),
                'schedule_inquiry_status' => (string) ($scheduleRates['schedule_inquiry_status'] ?? 'pending'),
                'quotation_status' => (string) ($scheduleRates['rate_quotation_status'] ?? 'pending'),
                'rate_quotation_status' => (string) ($scheduleRates['rate_quotation_status'] ?? 'pending')
            ],
            'facilities' => [
                'reservation_status' => (string) ($facilitiesReservation['reservation_status'] ?? 'pending'),
                'payment_status' => (string) ($facilitiesReservation['payment_status'] ?? 'pending'),
                'payment_source' => $paymentSource
            ],
            // Backward-compatible keys for existing consumers.
            'passport_visa' => $passportVisa,
            'schedule_rates' => $scheduleRates,
            'facilities_reservation' => $facilitiesReservation
        ]
    ]);
} catch (Throwable $exception) {
    respond(500, [
        'success' => false,
        'message' => $exception->getMessage()
    ]);
}
