<?php

/*
|--------------------------------------------------------------------------
| Betachem E-Commerce
| Email System
|--------------------------------------------------------------------------
*/

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;


/*
|--------------------------------------------------------------------------
| Load PHPMailer
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../libraries/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../libraries/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../libraries/PHPMailer/src/SMTP.php';


/*
|--------------------------------------------------------------------------
| Load Email Configuration
|--------------------------------------------------------------------------
*/

$emailConfig = require __DIR__ . '/../config/email.php';


/*
|--------------------------------------------------------------------------
| Send Betachem Email
|--------------------------------------------------------------------------
*/

function sendBetachemEmail(
    string $recipientEmail,
    string $recipientName,
    string $subject,
    string $htmlBody,
    string $plainTextBody = ''
): bool {

    global $emailConfig;


    /*
    |--------------------------------------------------------------------------
    | Basic Validation
    |--------------------------------------------------------------------------
    */

    if (
        empty($recipientEmail) ||
        !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)
    ) {
        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Create PHPMailer
    |--------------------------------------------------------------------------
    */

    $mail = new PHPMailer(true);


    try {

        /*
        |--------------------------------------------------------------------------
        | SMTP
        |--------------------------------------------------------------------------
        */

        $mail->isSMTP();

        $mail->Host = $emailConfig['host'];

        $mail->SMTPAuth = true;

        $mail->Username = $emailConfig['username'];

        $mail->Password = $emailConfig['password'];

        $mail->Port = (int) $emailConfig['port'];


        /*
        |--------------------------------------------------------------------------
        | Encryption
        |--------------------------------------------------------------------------
        */

        if ($emailConfig['encryption'] === 'ssl') {

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

        } else {

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        }


        /*
        |--------------------------------------------------------------------------
        | Character Set
        |--------------------------------------------------------------------------
        */

        $mail->CharSet = 'UTF-8';


        /*
        |--------------------------------------------------------------------------
        | Sender
        |--------------------------------------------------------------------------
        */

        $mail->setFrom(
            $emailConfig['from_email'],
            $emailConfig['from_name']
        );


        /*
        |--------------------------------------------------------------------------
        | Reply-To
        |--------------------------------------------------------------------------
        */

        if (!empty($emailConfig['reply_to_email'])) {

            $mail->addReplyTo(
                $emailConfig['reply_to_email'],
                $emailConfig['reply_to_name'] ?? $emailConfig['from_name']
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Recipient
        |--------------------------------------------------------------------------
        */

        $mail->addAddress(
            $recipientEmail,
            $recipientName
        );


        /*
        |--------------------------------------------------------------------------
        | Email Content
        |--------------------------------------------------------------------------
        */

        $mail->isHTML(true);

        $mail->Subject = $subject;

        $mail->Body = $htmlBody;

        $mail->AltBody = !empty($plainTextBody)
            ? $plainTextBody
            : strip_tags($htmlBody);


        /*
        |--------------------------------------------------------------------------
        | Send
        |--------------------------------------------------------------------------
        */

        return $mail->send();

    } catch (Exception $e) {

        /*
        |--------------------------------------------------------------------------
        | Log Email Error
        |--------------------------------------------------------------------------
        */

        error_log(
            'Betachem email error: ' . $mail->ErrorInfo
        );

        return false;
    }
}