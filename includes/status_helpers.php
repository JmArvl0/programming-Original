<?php
/**
 * Application status helpers for Passport & Visa module
 * Provides mappings to labels, bootstrap badge classes, progress percent,
 * and normalization utilities.
 */

if (!function_exists('application_statuses')) {
    function application_statuses(): array
    {
        return [
            0 => 'Not Started',
            1 => 'Submitted',
            2 => 'Under Review',
            3 => 'Approved',
            4 => 'Rejected',
            5 => 'Visa Issued'
        ];
    }
}

if (!function_exists('application_status_code')) {
    function application_status_code($value): int
    {
        $value = is_numeric($value) ? (int) $value : strtolower(trim((string) $value));
        $map = array_change_key_case(array_flip(array_map('strtolower', application_statuses())), CASE_LOWER);
        if (is_int($value) && array_key_exists($value, application_statuses())) {
            return $value;
        }
        if (is_string($value) && isset($map[strtolower($value)])) {
            return (int) $map[strtolower($value)];
        }
        // fallback: Not Started
        return 0;
    }
}

if (!function_exists('application_status_label')) {
    function application_status_label($value): string
    {
        $code = application_status_code($value);
        $labels = application_statuses();
        return $labels[$code] ?? 'Not Started';
    }
}

if (!function_exists('application_status_badge_class')) {
    function application_status_badge_class($value): string
    {
        $code = application_status_code($value);
        switch ($code) {
            case 0: // Not Started
                return 'bg-secondary';
            case 1: // Submitted
                return 'bg-primary';
            case 2: // Under Review
                return 'bg-warning text-dark';
            case 3: // Approved
                return 'bg-success';
            case 4: // Rejected
                return 'bg-danger';
            case 5: // Visa Issued
                return 'bg-success';
            default:
                return 'bg-secondary';
        }
    }
}

if (!function_exists('application_status_progress')) {
    /**
     * Returns an integer percent representing progress for the status.
     */
    function application_status_progress($value): int
    {
        $code = application_status_code($value);
        switch ($code) {
            case 0: return 0;    // Not Started
            case 1: return 25;   // Submitted
            case 2: return 60;   // Under Review
            case 3: return 85;   // Approved
            case 5: return 100;  // Visa Issued
            case 4: return 0;    // Rejected (stops progression)
            default: return 0;
        }
    }
}

?>

<?php
// Document status helpers (separate from overall application status)
if (!function_exists('document_statuses')) {
    function document_statuses(): array
    {
        return [
            0 => 'Not Started',
            1 => 'Missing',
            2 => 'Incomplete',
            3 => 'Submitted',
            4 => 'Processing',
            5 => 'Verified',
            6 => 'Completed'
        ];
    }
}

if (!function_exists('document_status_code')) {
    function document_status_code($value): int
    {
        $value = is_numeric($value) ? (int) $value : strtolower(trim((string) $value));
        $map = array_change_key_case(array_flip(array_map('strtolower', document_statuses())), CASE_LOWER);
        if (is_int($value) && array_key_exists($value, document_statuses())) {
            return $value;
        }
        if (is_string($value) && isset($map[strtolower($value)])) {
            return (int) $map[strtolower($value)];
        }
        // fallback: Not Started
        return 0;
    }
}

if (!function_exists('document_status_label')) {
    function document_status_label($value): string
    {
        $code = document_status_code($value);
        $labels = document_statuses();
        return $labels[$code] ?? 'Not Started';
    }
}

if (!function_exists('document_status_badge_class')) {
    function document_status_badge_class($value): string
    {
        $code = document_status_code($value);
        switch ($code) {
            case 0: // Not Started
                return 'bg-secondary';
            case 1: // Missing
                return 'bg-danger';
            case 2: // Incomplete
                return 'bg-warning text-dark';
            case 3: // Submitted
                return 'bg-primary';
            case 4: // Processing
                return 'bg-warning text-dark';
            case 5: // Verified
                return 'bg-success';
            case 6: // Completed
                return 'bg-success';
            default:
                return 'bg-secondary';
        }
    }
}

?>
