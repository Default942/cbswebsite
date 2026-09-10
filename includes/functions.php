<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

// ---- Pricing constants -----------------------------------------------
define('YARD_PRICE', 50);   // GHS per yard of cloth
define('DUES_PRICE', 65);   // GHS flat dues fee

// ---- Helpers -----------------------------------------------------------

/** Redirect to a page and stop execution. */
function redirect($path) {
    header("Location: {$path}");
    exit;
}

/** Simple flash-message helper (stored in session, shown once). */
function set_flash($type, $message) {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes() {
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/** Require the student to be logged in, otherwise bounce to login page. */
function require_login() {
    if (empty($_SESSION['student_id'])) {
        set_flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
}

/** Fetch the list of departments from the DB. */
function get_departments($pdo) {
    $stmt = $pdo->query("SELECT name, code FROM departments ORDER BY name ASC");
    return $stmt->fetchAll();
}

/**
 * Validate that a student ID begins with the initials of the given
 * department code (case-insensitive). Returns true/false.
 */
function student_id_matches_department($studentId, $departmentCode) {
    $studentId = strtoupper(trim($studentId));
    $departmentCode = strtoupper(trim($departmentCode));
    return strpos($studentId, $departmentCode) === 0;
}

/** Generate a 6-digit numeric OTP code. */
function generate_otp() {
    return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * "Send" an email. In this prototype we attempt PHP's mail() function;
 * if that isn't configured on the host, the message is logged to
 * /includes/email_log.txt instead so the flow can still be demoed.
 */
function send_email($to, $subject, $htmlBody) {
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Central University Business School <no-reply@cubusinessschool.edu.gh>\r\n";

    $sent = @mail($to, $subject, $htmlBody, $headers);

    if (!$sent) {
        $log = "----- " . date('Y-m-d H:i:s') . " -----\n";
        $log .= "TO: {$to}\nSUBJECT: {$subject}\n{$htmlBody}\n\n";
        file_put_contents(__DIR__ . '/email_log.txt', $log, FILE_APPEND);
    }

    return true;
}

/** Format a number as Ghana Cedis. */
function money($amount) {
    return 'GHS ' . number_format((float) $amount, 2);
}
