<?php
namespace Mailer;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

class Mailer {
    private $mailer;
    private $config = [];

    public $from;
    public $fromName;
    public $to = [];
    public $cc = [];
    public $bcc = [];
    public $replyTo;
    public $replyToName;
    public $subject;
    public $text;
    public $html;
    public $attachments = [];
    public $images = [];

    public function __construct($options = []) {
        $this->mailer = new PHPMailer(true);
        $this->setup($options);
    }

    private function setup($options) {
        $service = strtolower($options['service'] ?? '');
        $auth = $options['auth'] ?? [];

        $serviceMap = [
            'gmail' => [
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'secure' => PHPMailer::ENCRYPTION_STARTTLS,
            ],
            'outlook' => [
                'host' => 'smtp.office365.com',
                'port' => 587,
                'secure' => PHPMailer::ENCRYPTION_STARTTLS,
            ],
            'yahoo' => [
                'host' => 'smtp.mail.yahoo.com',
                'port' => 465,
                'secure' => PHPMailer::ENCRYPTION_SMTPS,
            ]
        ];

        if (!isset($serviceMap[$service])) {
            throw new Exception("Unsupported mail service: $service");
        }

        $config = $serviceMap[$service];
        $this->mailer->isSMTP();
        $this->mailer->Host = $config['host'];
        $this->mailer->Port = $config['port'];
        $this->mailer->SMTPSecure = $config['secure'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $auth['user'] ?? '';
        $this->mailer->Password = $auth['pass'] ?? '';
    }

    public function send($mailOptions = []) {
        // If $mailOptions is given, override properties
        if (!empty($mailOptions)) {
            foreach ($mailOptions as $key => $value) {
                $this->$key = $value;
            }
        }

        try {
            $this->mailer->setFrom($this->from, $this->fromName ?? '');

            // To
            $toList = is_array($this->to) ? $this->to : [$this->to];
            foreach ($toList as $to) {
                if (is_array($to)) {
                    $this->mailer->addAddress($to['email'], $to['name'] ?? '');
                } else {
                    $this->mailer->addAddress($to);
                }
            }

            // CC
            foreach ((array) $this->cc as $cc) {
                if (is_array($cc)) {
                    $this->mailer->addCC($cc['email'], $cc['name'] ?? '');
                } else {
                    $this->mailer->addCC($cc);
                }
            }

            // BCC
            foreach ((array) $this->bcc as $bcc) {
                if (is_array($bcc)) {
                    $this->mailer->addBCC($bcc['email'], $bcc['name'] ?? '');
                } else {
                    $this->mailer->addBCC($bcc);
                }
            }

            // Reply-To
            if (!empty($this->replyTo)) {
                $this->mailer->addReplyTo($this->replyTo, $this->replyToName ?? '');
            }

            $this->mailer->Subject = $this->subject ?? '';
            $this->mailer->Body = $this->html ?? $this->text ?? '';
            $this->mailer->AltBody = $this->text ?? strip_tags($this->mailer->Body);
            $this->mailer->isHTML(isset($this->html));

            // Attachments
            foreach ((array) $this->attachments as $filePath) {
                $this->mailer->addAttachment($filePath);
            }

            // Inline images
            foreach ((array) $this->images as $image) {
                $this->mailer->addEmbeddedImage(
                    $image['path'],
                    $image['cid'],
                    $image['name'] ?? '',
                    'base64',
                    $image['type'] ?? 'image/png'
                );
            }

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            return 'Message could not be sent. Mailer Error: ' . $this->mailer->ErrorInfo;
        }
    }
}
