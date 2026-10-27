<?php

declare(strict_types=1);

return [

    'one_time_password' => [
        'greeting' => 'Here is your sign-in code',
        'ignore' => "If you didn't request this, you can ignore this email.",
        'line' => 'Use this code to finish signing in: :code',
        'subject' => 'Your sign-in code',
    ],

    'providers' => [
        'apple' => [
            'account_conflict' => "You can't sign in with Apple on this account. Use your email to continue.",
            'invalid_credentials' => 'Unable to verify the supplied Apple credentials.',
        ],
        'google' => [
            'account_conflict' => "You can't sign in with Google on this account. Use your email to continue.",
            'invalid_credentials' => 'Unable to verify the supplied Google credentials.',
        ],
    ],

];
