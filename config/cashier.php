<?php

return [
    'webhook' => [
        'secret' => env('STRIPE_WEBHOOK_SECRET', null),
    ],
];
