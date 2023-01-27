<?php

return [
    'required_choices' => 5,
    'gdpr_anonymise_after' => 365,
    'gdpr_contact' => env('GDPR_CONTACT'),
    'wlm_api_url' => env('OLD_API_URL'),
    'api_key' => env('PROJECTS_API_KEY', \Illuminate\Support\Str::random(64)),
];
