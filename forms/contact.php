<?php
/*
 * forms/contact.php
 * Simple secure contact form processor
 */

// ================= CONFIG =================
$to_email       = "mahodizahin@gmail.com";          // ← Your email here
$from_name      = "Your Website";                   // Sender name in email
$subject_prefix = "New message from website: ";     // Email subject

// ================= HONEYPOT FIELD NAME =================
// Must match the hidden field you'll add to HTML form
$honeypot_field = "website";   // ← common name bots fill

// ================= START PROCESSING =================
$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ================= HONEYPOT CHECK (anti-spam) =================
    if (!empty($_POST[$honeypot_field])) {
        // If filled → probably a bot → pretend success but do nothing
        $success = true;
        goto output;
    }

    // ================= GET & SANITIZE INPUTS =================
    $name    = trim($_POST["name"]    ?? "");
    $email   = trim($_POST["email"]   ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $message = trim($_POST["message"] ?? "");

    // ================= VALIDATION =================
    if (empty($name)) {
        $errors[] = "Name is required.";
    } elseif (strlen($name) < 2 || strlen($name) > 100) {
        $errors[] = "Name must be between 2 and 100 characters.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($subject)) {
        $errors[] = "Subject is required.";
    }

    if (empty($message)) {
        $errors[] = "Message is required.";
    } elseif (strlen($message) < 10) {
        $errors[] = "Message is too short.";
    }

    // ================= IF NO ERRORS → SEND EMAIL =================
    if (empty($errors)) {
        $full_message = "Name:    $name\n";
        $full_message .= "Email:   $email\n";
        $full_message .= "Subject: $subject\n\n";
        $full_message .= "Message:\n$message\n";

        $headers = "From: $from_name <$email>\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        $mail_sent = mail($to_email, $subject_prefix . $subject, $full_message, $headers);

        if ($mail_sent) {
            $success = true;
        } else {
            $errors[] = "Sorry, failed to send message. Please try again later.";
        }
    }
}

// ================= OUTPUT FOR AJAX / YOUR FORM =================
output:

header('Content-Type: application/json');

if ($success) {
    echo json_encode([
        'success' => true,
        'message' => 'Your message has been sent. Thank you!'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'errors'  => $errors
    ]);
}

exit;