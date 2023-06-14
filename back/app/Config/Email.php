<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail = '';
    public string $fromName = '';
    public string $recipients = '';

    /**
     * The "user agent"
     */
    public string $userAgent = 'commuse';

    /**
     * The mail sending protocol: mail, sendmail, smtp
     */
    public string $protocol = 'mail';

    /**
     * The server path to Sendmail.
     */
    public string $mailPath = '/usr/sbin/sendmail';

    /**
     * SMTP Server Address
     */
    public string $SMTPHost = '';

    /**
     * SMTP Username
     */
    public string $SMTPUser = '';

    /**
     * SMTP Password
     */
    public string $SMTPPass = '';

    /**
     * SMTP Port
     */
    public int $SMTPPort = 25;

    /**
     * SMTP Timeout (in seconds)
     */
    public int $SMTPTimeout = 5;

    /**
     * Enable persistent SMTP connections
     */
    public bool $SMTPKeepAlive = false;

    /**
     * SMTP Encryption. Either tls or ssl
     */
    public string $SMTPCrypto = 'tls';

    /**
     * Enable word-wrap
     */
    public bool $wordWrap = true;

    /**
     * Character count to wrap at
     */
    public int $wrapChars = 76;

    /**
     * Type of mail, either 'text' or 'html'
     */
    public string $mailType = 'text';

    /**
     * Character set (utf-8, iso-8859-1, etc.)
     */
    public string $charset = 'UTF-8';

    /**
     * Whether to validate the email address
     */
    public bool $validate = false;

    /**
     * Email Priority. 1 = highest. 5 = lowest. 3 = normal
     */
    public int $priority = 3;

    /**
     * Newline character. (Use “\r\n” to comply with RFC 822)
     */
    public string $CRLF = "\r\n";

    /**
     * Newline character. (Use “\r\n” to comply with RFC 822)
     */
    public string $newline = "\r\n";

    /**
     * Enable BCC Batch Mode.
     */
    public bool $BCCBatchMode = false;

    /**
     * Number of emails in each BCC batch
     */
    public int $BCCBatchSize = 200;

    /**
     * Enable notify message from server
     */
    public bool $DSN = false;

    /**
     * Email constructor.
     */
    public function __construct()
    {
          parent::__construct();

          $this->fromEmail = $_ENV['email.from'] ?? $this->fromEmail;
          $this->fromName = $_ENV['email.from_name'] ?? $this->fromName;
          $this->recipients = $_ENV['email.recipients'] ?? $this->recipients;
          $this->userAgent = $_ENV['email.user_agent'] ?? $this->userAgent;
          $this->protocol = $_ENV['email.protocol'] ?? $this->protocol;
          $this->mailPath = $_ENV['email.mail_path'] ?? $this->mailPath;
          $this->SMTPHost = $_ENV['email.smtp_host'] ?? $this->SMTPHost;
          $this->SMTPUser = $_ENV['email.smtp_username'] ?? $this->SMTPUser;
          $this->SMTPPass = $_ENV['email.smtp_password'] ?? $this->SMTPPass;
          $this->SMTPPort = $_ENV['email.smtp_port'] ?? $this->SMTPPort;
          $this->SMTPTimeout = $_ENV['email.smtp_timeout'] ?? $this->SMTPTimeout;
          $this->SMTPKeepAlive = $_ENV['email.smtp_keepalive'] ?? $this->SMTPKeepAlive;
          $this->SMTPCrypto = $_ENV['email.smtp_crypto'] ?? $this->SMTPCrypto;
          $this->wordWrap = $_ENV['email.word_wrap'] ?? $this->wordWrap;
          $this->wrapChars = $_ENV['email.wrap_chars'] ?? $this->wrapChars;
          $this->mailType = $_ENV['email.mail_type'] ?? $this->mailType;
          $this->charset = $_ENV['email.charset'] ?? $this->charset;
          $this->validate = $_ENV['email.validate'] ?? $this->validate;
          $this->priority = $_ENV['email.priority'] ?? $this->priority;
          $this->CRLF = $_ENV['email.CRLF'] ?? $this->CRLF;
          $this->newline = $_ENV['email.newline'] ?? $this->newline;
          $this->BCCBatchMode = $_ENV['email.BCCBatchMode'] ?? $this->BCCBatchMode;
          $this->BCCBatchSize = $_ENV['email.BCCBatchSize'] ?? $this->BCCBatchSize;
          $this->DSN = $_ENV['email.DSN'] ?? $this->DSN;
    }
}
