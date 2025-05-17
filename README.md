# Mailer
A PHP library that works similar to Nodemailer for node js, for sending emails.

### What is Mailer exactly.
It is what is described above, it uses [PHPMailer](https://packagist.org/packages/phpmailer/phpmailer) in the background. It can probably perform several tasks;

You're now fully equipped with a custom, flexible mail system using PHP and PHPMailer that supports:

✅ Gmail and other providers
✅ Plain text + HTML emails
✅ Reply-To, CC, BCC
✅ Multiple recipients
✅ File attachments
✅ Inline images (e.g., logos in HTML body)
✅ Sending multiple email.
✅ Images

## Usage
It has two ways of using it, passing parameters as an associative array or using the Object format to assign parameters

Example Usage (Everything Included)

### 🟡 Option 1: Object Style
```php
$mailer = new Mailer([
    'service' => 'gmail',
    'auth' => [
        'user' => 'your-email@gmail.com',
        'pass' => 'your-app-password'
    ]
]);
// 
// 
$result = $mailer->send([
    'from' => 'your-email@gmail.com',
    'fromName' => 'Your Name',
    'to' => [
        ['email' => 'person1@example.com', 'name' => 'Alice'],
        ['email' => 'person2@example.com', 'name' => 'Bob']
    ],
    'cc' => 'manager@example.com',
    'bcc' => [
        ['email' => 'audit@example.com'],
        'hidden@example.com'
    ],
    'replyTo' => 'support@example.com',
    'replyToName' => 'Support Team',
    'subject' => 'Email with All Features',
    'html' => '
        <h1>Hello!</h1>
        <p>This email has HTML, CC, BCC, attachments, and an inline image:</p>
        <img src="cid:logo_cid">
    ',
    'text' => 'This is the plain text version.',
    'attachments' => [
        __DIR__ . '/files/report.pdf'
    ],
    'images' => [
        [
            'path' => __DIR__ . '/images/logo.png',
            'cid' => 'logo_cid',
            'name' => 'logo.png',
            'type' => 'image/png'
        ]
    ]
]);

echo $result === true ? "Email sent successfully!" : $result;
// 
```

### 🟡 Option 2: Array Style

```php
$mail = new Mailer([
    'service' => 'gmail',
    'auth' => ['user' => 'you@gmail.com', 'pass' => 'your-app-password']
]);
$mail = new MailTransport([
    'service' => 'gmail',
    'auth' => ['user' => 'you@gmail.com', 'pass' => 'your-app-password']
]);

$mail->from = 'you@gmail.com';
$mail->fromName = 'Your Name';
$mail->to = [
    ['email' => 'john@example.com', 'name' => 'John'],
    'jane@example.com'
];

// 
$mail->cc = 'boss@example.com';
$mail->bcc = ['secret@example.com'];
$mail->replyTo = 'reply@example.com';
$mail->replyToName = 'Reply Desk';
$mail->subject = 'Sample Email';
$mail->text = 'Plain text content';
$mail->html = '<h1>Hello</h1><p>This is HTML with logo</p><img src="cid:logo">';
$mail->attachments = [__DIR__ . '/files/invoice.pdf'];
$mail->images = [[
    'path' => __DIR__ . '/images/logo.png',
    'cid' => 'logo',
    'name' => 'logo.png',
    'type' => 'image/png'
]];

$result = $mail->send()
echo $$result === true ? "Sent!" : "Failed!";
```

The send method retrns a boolean.