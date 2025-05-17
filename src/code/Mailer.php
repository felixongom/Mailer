<?php
namespace Mailer;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

class Mailer {
    private $mailer;

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

    private function validate() {
        if (empty($this->from)) {
            throw new Exception("Sender email 'from' is required.");
        }
        if (empty($this->to)) {
            throw new Exception("At least one recipient 'to' is required.");
        }
        if (empty($this->subject)) {
            throw new Exception("Email 'subject' is required.");
        }
        if (empty($this->text) && empty($this->html)) {
            throw new Exception("Either 'text' or 'html' content must be provided.");
        }
    }

    public function reset() {
        $this->from = null;
        $this->fromName = null;
        $this->to = [];
        $this->cc = [];
        $this->bcc = [];
        $this->replyTo = null;
        $this->replyToName = null;
        $this->subject = null;
        $this->text = null;
        $this->html = null;
        $this->attachments = [];
        $this->images = [];
    }

    public function send($mailOptions = []) {
        if (!empty($mailOptions)) {
            foreach ($mailOptions as $key => $value) {
                $this->$key = $value;
            }
        }

        try {
            $this->validate();

            $this->mailer->clearAllRecipients();
            $this->mailer->clearAttachments();

            $this->mailer->setFrom($this->from, $this->fromName ?? '');

            foreach ((array) $this->to as $to) {
                if (is_array($to)) {
                    $this->mailer->addAddress($to['email'], $to['name'] ?? '');
                } else {
                    $this->mailer->addAddress($to);
                }
            }

            foreach ((array) $this->cc as $cc) {
                if (is_array($cc)) {
                    $this->mailer->addCC($cc['email'], $cc['name'] ?? '');
                } else {
                    $this->mailer->addCC($cc);
                }
            }

            foreach ((array) $this->bcc as $bcc) {
                if (is_array($bcc)) {
                    $this->mailer->addBCC($bcc['email'], $bcc['name'] ?? '');
                } else {
                    $this->mailer->addBCC($bcc);
                }
            }

            if (!empty($this->replyTo)) {
                $this->mailer->addReplyTo($this->replyTo, $this->replyToName ?? '');
            }

            $this->mailer->Subject = $this->subject;
            $this->mailer->Body = $this->html ?? $this->text;
            $this->mailer->AltBody = $this->text ?? strip_tags($this->mailer->Body);
            $this->mailer->isHTML(isset($this->html));

            foreach ((array) $this->attachments as $filePath) {
                $this->mailer->addAttachment($filePath);
            }

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
            $this->reset();
            return true;

        } catch (Exception $e) {
            return 'Message could not be sent. Mailer Error: ' . $this->mailer->ErrorInfo;
        }
    }
}