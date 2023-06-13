<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail  = $_ENV['email.from'] ?? 'commuse@example.com';
    public string $fromName   = $_ENV['email.from_name'] ?? 'commuse';
    public string $recipients = '';

    /**
     * The "user agent"
     */
    public string $userAgent = $_ENV['email.user_agent'] ?? 'commuse';

    /**
     * The mail sending protocol: mail, sendmail, smtp
     */
    public string $protocol = $_ENV['email.protocol'] ?? 'mail';

    /**
     * The server path to Sendmail.
     */
    public string $mailPath = $_ENV['email.sendmail_path'] ?? '/usr/sbin/sendmail';

    /**
     * SMTP Server Address
     */
    public string $SMTPHost = $_ENV['email.smpt_host'] ?? '';

    /**
     * SMTP Username
     */
    public string $SMTPUser = $_ENV['email.smtp_username'] ?? '';

    /**
     * SMTP Password
     */
    public string $SMTPPass = $_ENV['email.smtp_password'] ?? '';

    /**
     * SMTP Port
     */
    public int $SMTPPort = $_ENV['email.smtp_port'] ?? 25;

    /**
     * SMTP Timeout (in seconds)
     */
    public int $SMTPTimeout = $_ENV['email.smtp_timeout'] ?? 5;

    /**
     * Enable persistent SMTP connections
     */
    public bool $SMTPKeepAlive = $_ENV['email.smtp_keepalive'] ?? false;

    /**
     * SMTP Encryption. Either tls or ssl
     */
    public string $SMTPCrypto = $_ENV['email.smtp_encryption'] ?? 'tls';

    /**
     * Enable word-wrap
     */
    public bool $wordWrap = $_ENV['email.email_wordwrap'] ?? true;

    /**
     * Character count to wrap at
     */
    public int $wrapChars = $_ENV['email.email_wordwrpa_count'] ?? 76;

    /**
     * Type of mail, either 'text' or 'html'
     */
    public string $mailType = $_ENV['email.type'] ?? 'text';

    /**
     * Character set (utf-8, iso-8859-1, etc.)
     */
    public string $charset = $_ENV['email.charset'] ?? 'UTF-8';

    /**
     * Whether to validate the email address
     */
    public bool $validate = $_ENV['email.validate'] ?? false;

    /**
     * Email Priority. 1 = highest. 5 = lowest. 3 = normal
     */
    public int $priority = $_ENV['email.priority'] ?? 3;

    /**
     * Newline character. (Use “\r\n” to comply with RFC 822)
     */
    public string $CRLF = $_ENV['email.newline_character'] ?? "\r\n";

    /**
     * Newline character. (Use “\r\n” to comply with RFC 822)
     */
    public string $newline = $_ENV['email.newline_character'] ?? "\r\n";

    /**
     * Enable BCC Batch Mode.
     */
    public bool $BCCBatchMode = $_ENV['email.bcc_batch_mode'] ?? false;

    /**
     * Number of emails in each BCC batch
     */
    public int $BCCBatchSize = $_ENV['email.bcc_batch_size'] ?? 200;

    /**
     * Enable notify message from server
     */
    public bool $DSN = $_ENV['email.dsn'] ?? false;
}
