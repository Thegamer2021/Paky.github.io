<?php
declare(strict_types=1);

return [
  'contact' => [
    'to_email' => 'hello@pasqualemagro.dev',
    'public_email' => 'hello@pasqualemagro.dev',
    'from_email' => 'noreply@pasqualemagro.dev',
    'from_name' => 'Pasquale Magro Portfolio',
    'subject_prefix' => '[Pasquale Portfolio]',
  ],
  'mail' => [
    // For XAMPP/Windows, set SMTP host and port only if needed.
    // Example:
    // 'smtp_host' => 'smtp.your-provider.com',
    // 'smtp_port' => 587,
    'smtp_host' => '',
    'smtp_port' => 0,
  ],
];
