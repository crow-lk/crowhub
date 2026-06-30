<?php

return [
    'name' => env('COMPANY_NAME', 'Crow.lk'),
    'tagline' => env('COMPANY_TAGLINE', ''),
    'address' => env('COMPANY_ADDRESS', ''),
    'phone' => env('COMPANY_PHONE', ''),
    'email' => env('COMPANY_EMAIL', ''),
    'website' => env('COMPANY_WEBSITE', 'crow.lk'),
    'tax_id' => env('COMPANY_TAX_ID', ''),
    'bank' => [
        'account_number' => env('COMPANY_BANK_ACCOUNT_NUMBER', '007010350044'),
        'account_name' => env('COMPANY_BANK_ACCOUNT_NAME', 'Crow.lk Pvt Ltd'),
        'bank' => env('COMPANY_BANK_NAME', 'HNB'),
        'branch' => env('COMPANY_BANK_BRANCH', 'Pettah'),
        'swift_code' => env('COMPANY_BANK_SWIFT_CODE', 'HBLILKLX'),
    ],
];
