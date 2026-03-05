<?php


class PassportVisaModel
{
    public function getApplicantsPage(
        int $page = 1,
        int $perPage = 10,
        string $filter = 'all',
        string $search = ''
    ): array {
        $conn = getDBConnection();

        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];
        $types = '';

        // FILTERING
        if ($filter === 'approved') {
            $where[] = "pa.application_status = 'approved'";
        }

        if ($filter === 'completed') {
            $where[] = "pa.application_status = 'visa issued'";
        }

        if ($search !== '') {
            $where[] = "(c.full_name LIKE ? OR pa.country LIKE ?)";
            $searchParam = '%' . $search . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= 'ss';
        }

        $whereSql = '';
        if (!empty($where)) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        $sql = "
            SELECT 
                ae.booking_id,
                c.id AS customer_id,
                c.full_name,
                c.email,
                c.phone,
                pa.passport_number,
                pa.country,
                pa.documents_status,
                pa.application_status,
                pa.submission_date
            FROM account_executive ae
            JOIN bookings b ON ae.booking_id = b.id
            JOIN customers c ON b.customer_id = c.id
            LEFT JOIN passport_applications pa 
                ON pa.booking_id = ae.booking_id AND pa.id = (
                    SELECT MAX(id) FROM passport_applications 
                    WHERE booking_id = ae.booking_id
                )
            $whereSql
            ORDER BY c.full_name ASC
            LIMIT ? OFFSET ?
        ";

        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new RuntimeException("Query preparation failed: " . $conn->error);
        }

        $types .= 'ii';
        $params[] = $perPage;
        $params[] = $offset;

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];

        while ($row = $result->fetch_assoc()) {
            $items[] = [
                'booking_id' => $row['booking_id'],
                'customer_id' => $row['customer_id'],
                'full_name' => $row['full_name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'passport_number' => $row['passport_number'] ?? 'N/A',
                'country' => $row['country'] ?? 'N/A',
                'documents_status' => $row['documents_status'] ?? 'not started',
                'application_status' => $row['application_status'] ?? 'not started',
                'submission_date' => $row['submission_date']
            ];
        }

        $stmt->close();

        // GET TOTAL COUNT
        $countSql = "
            SELECT COUNT(*) AS total
            FROM account_executive ae
            JOIN bookings b ON ae.booking_id = b.id
            JOIN customers c ON b.customer_id = c.id
            LEFT JOIN passport_applications pa 
                ON pa.booking_id = ae.booking_id
            $whereSql
        ";

        $countStmt = $conn->prepare($countSql);

        if (!empty($params)) {
            $countParams = $params;
            array_pop($countParams);
            array_pop($countParams);

            if (!empty($countParams)) {
                $countTypes = substr($types, 0, -2);
                $countStmt->bind_param($countTypes, ...$countParams);
            }
        }

        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalItems = $countResult->fetch_assoc()['total'];

        $countStmt->close();
        closeDBConnection($conn);
        return [
            'items' => $items,
            'allFiltered' => $items, // keep for controller compatibility
            'page' => $page,
            'perPage' => $perPage,
            'totalItems' => $totalItems,
            'totalPages' => ceil($totalItems / $perPage),
            'offset' => $offset
        ];
    }

    public function updateApplicantByBookingId(int $bookingId, array $data): bool
    {
        $conn = getDBConnection();

        // Extract and prepare values
        $passportNumber = $data['passport_number'] ?? '';
        $country = $data['country'] ?? '';
        $documentsStatus = $data['documents_status'] ?? 'not started';
        $applicationStatus = $data['application_status'] ?? 'not started';
        $submissionDate = $data['submission_date'] ?? '';
        $remarks = $data['remarks'] ?? '';

        // First, check if a passport_application record exists for this booking_id
        $checkSql = "SELECT MAX(id) as id FROM passport_applications WHERE booking_id = ? LIMIT 1";
        $checkStmt = $conn->prepare($checkSql);
        if (!$checkStmt) {
            closeDBConnection($conn);
            return false;
        }

        $checkStmt->bind_param("i", $bookingId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $existingRecord = $checkResult->fetch_assoc();
        $checkStmt->close();

        if ($existingRecord && $existingRecord['id']) {
            // Record exists - UPDATE the most recent one
            $recordId = $existingRecord['id'];
            $sql = "
                UPDATE passport_applications
                SET passport_number = ?,
                    country = ?,
                    documents_status = ?,
                    application_status = ?,
                    submission_date = ?,
                    remarks = ?
                WHERE id = ?
            ";
        } else {
            // Record doesn't exist - INSERT it
            $sql = "
                INSERT INTO passport_applications 
                    (booking_id, passport_number, country, documents_status, application_status, submission_date, remarks)
                VALUES 
                    (?, ?, ?, ?, ?, ?, ?)
            ";
        }

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            closeDBConnection($conn);
            return false;
        }

        if ($existingRecord && $existingRecord['id']) {
            // UPDATE binding - use the record ID
                $stmt->bind_param(
                    "ssssssi",
                $passportNumber,
                $country,
                $documentsStatus,
                $applicationStatus,
                $submissionDate,
                $remarks,
                $recordId
            );
        } else {
            // INSERT binding
            $stmt->bind_param(
                "issssss",
                $bookingId,
                $passportNumber,
                $country,
                $documentsStatus,
                $applicationStatus,
                $submissionDate,
                $remarks
            );
        }

        $success = $stmt->execute();
        $stmt->close();
        closeDBConnection($conn);

        return $success;
    }

    public function buildStats(): array
    {
        $conn = getDBConnection();

        $sql = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN pa.application_status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN pa.application_status IN ('processing','under review') THEN 1 ELSE 0 END) as review,
                SUM(CASE WHEN pa.submission_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as new_count
            FROM account_executive ae
            JOIN bookings b ON ae.booking_id = b.id
            LEFT JOIN passport_applications pa 
                ON pa.booking_id = ae.booking_id AND pa.id = (
                    SELECT MAX(id) FROM passport_applications 
                    WHERE booking_id = ae.booking_id
                )
        ";

        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

        closeDBConnection($conn);

        return [
            'total' => (int)$row['total'],
            'approved' => (int)$row['approved'],
            'review' => (int)$row['review'],
            'new' => (int)$row['new_count']
        ];
    }

    public function getApplicantById(int $bookingId): ?array
    {
        $conn = getDBConnection();

        $sql = "
            SELECT 
                ae.booking_id,
                c.id AS customer_id,
                c.full_name,
                c.email,
                c.phone,
                pa.id AS passport_application_id,
                pa.passport_number,
                pa.country,
                pa.documents_status,
                pa.application_status,
                pa.submission_date,
                pa.remarks,

                MAX(CASE 
                    WHEN pd.document_type = 'Passport Image' 
                    THEN pd.file_path 
                END) AS passport_image,

                MAX(CASE 
                    WHEN pd.document_type = '1x1 Photo' 
                    THEN pd.file_path 
                END) AS one_by_one_image

            FROM account_executive ae
            JOIN bookings b ON ae.booking_id = b.id
            JOIN customers c ON b.customer_id = c.id
            LEFT JOIN passport_applications pa 
                ON pa.booking_id = ae.booking_id AND pa.id = (
                    SELECT MAX(id) FROM passport_applications 
                    WHERE booking_id = ae.booking_id
                )
            LEFT JOIN passport_documents pd
                ON pd.passport_application_id = pa.id
            WHERE ae.booking_id = ?
            GROUP BY pa.id
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $stmt->close();
        closeDBConnection($conn);

        if (!$row) {
            return null;
        }

        return [
            'booking_id' => $row['booking_id'],
            'customer_id' => $row['customer_id'],
            'full_name' => $row['full_name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'passport_number' => $row['passport_number'] ?? 'N/A',
            'country' => $row['country'] ?? 'N/A',
            'documents_status' => $row['documents_status'] ?? 'not started',
            'application_status' => $row['application_status'] ?? 'not started',
            'submission_date' => $row['submission_date'],
            'remarks' => $row['remarks'] ?? '',
            'passport_image' => $row['passport_image'] ?? null,
            'one_by_one_image' => $row['one_by_one_image'] ?? null
        ];
    }
}