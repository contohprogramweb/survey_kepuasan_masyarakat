<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail  = 'noreply@example.com';
    public string $fromName   = 'Survey Management System';
    public string $recipients = '';

    /**
     * The "user agent"
     */
    public string $userAgent = 'CodeIgniter';

    /**
     * The mail sending protocol: mail, sendmail, smtp
     */
    public string $protocol = 'smtp';

    /**
     * The server path to Sendmail.
     */
    public string $mailPath = '/usr/sbin/sendmail';

    /**
     * SMTP Server Hostname
     */
    public string $SMTPHost = 'smtp.gmail.com';

    /**
     * SMTP Username
     */
    public string $SMTPUser = 'your-email@gmail.com';

    /**
     * SMTP Password
     */
    public string $SMTPPass = 'your-app-password';

    /**
     * SMTP Port (usually 25, 465 for SSL, 587 for TLS)
     */
    public int $SMTPPort = 587;

    /**
     * SMTP Timeout (in seconds)
     */
    public int $SMTPTimeout = 5;

    /**
     * Enable SMTP Encryption
     * Options: 'tls' or 'ssl'
     */
    public string $SMTPCrypto = 'tls';

    /**
     * Enable word-wrap
     */
    public bool $wordWrap = true;

    /**
     * Character count per line to wrap before
     */
    public int $wrapChars = 76;

    /**
     * Mail Type for HTML/Text emails
     */
    public string $mailType = 'html';

    /**
     * Character set (utf-8, iso-8859-1, etc.)
     */
    public string $charset = 'UTF-8';

    /**
     * Whether to validate the email address
     */
    public bool $validate = true;

    /**
     * Email Priority. 1 = highest. 5 = lowest. 3 = normal
     */
    public int $priority = 3;

    /**
     * Newline character. (Use "\r\n" to comply with RFC 822)
     */
    public string $CRLF = "\r\n";

    /**
     * Newline character. (Windows only! Use "\r\n" to comply with RFC 822)
     */
    public string $newline = "\r\n";

    /**
     * BCC Batch Mode. Enables sending BCC emails in batches
     */
    public bool $BCCBatchMode = false;

    /**
     * BCC Batch Max Number of recipients
     */
    public int $BCCBatchSize = 200;

    /**
     * SMTP debug mode
     */
    public bool $SMTPDebug = false;
}
