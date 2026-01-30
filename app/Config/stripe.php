<?php

declare(strict_types=1);

return [
    'secret_key' => \App\Support\Env::string('STRIPE_SECRET', ''),
    'publishable_key' => \App\Support\Env::string('STRIPE_KEY', ''),
    'webhook_secret' => \App\Support\Env::string('STRIPE_WEBHOOK_SECRET', ''),
];
