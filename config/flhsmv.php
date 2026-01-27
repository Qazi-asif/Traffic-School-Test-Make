<?php

return [
    /*
    |--------------------------------------------------------------------------
    | FLHSMV SOAP Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Florida DHSMV Driver School Web Service integration
    |
    */

    'username' => env('FLHSMV_USERNAME', ''),
    'password' => env('FLHSMV_PASSWORD', ''),

    'wsdl_url' => env('FLHSMV_WSDL_URL', 'https://services.flhsmv.gov/DriverSchoolWebService/wsPrimerComponentService.svc?wsdl'),

    'service_url' => env('FLHSMV_SERVICE_URL', 'https://services.flhsmv.gov/DriverSchoolWebService/wsPrimerComponentService.svc'),

    'test_wsdl_url' => env('FLHSMV_TEST_WSDL_URL', ''),
    'test_service_url' => env('FLHSMV_TEST_SERVICE_URL', ''),

    'environment' => env('FLHSMV_ENVIRONMENT', 'production'), // 'test' or 'production'

    'default_school_id' => env('FLHSMV_DEFAULT_SCHOOL_ID', ''),
    'default_instructor_id' => env('FLHSMV_DEFAULT_INSTRUCTOR_ID', ''),

    'retry_attempts' => env('FLHSMV_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('FLHSMV_RETRY_DELAY', 300), // seconds

    'timeout' => env('FLHSMV_TIMEOUT', 30),

    'error_codes' => [
        'AF000' => 'Could not insert address',
        'CC000' => 'School is out of certificates',
        'CC001' => 'Could not update school certificate count',
        'CF000' => 'Unique student identifier validation failed',
        'CF010' => 'No valid unique applicant identifier submitted',
        'CF020' => 'Submitted SSN is not four numeric digits',
        'CF030' => 'Driver License and state of record required',
        'CF031' => 'Invalid state of record code',
        'CF032' => 'Not in Florida DL format',
        'CF033' => 'Invalid DL number',
        'CF034' => 'Multiple records for DL',
        'CF035' => 'Error updating driver data',
        'CF040' => 'Alien registration number must be numeric',
        'CF050' => 'Non-alien registration number must be numeric',
        'CL000' => 'County name required',
        'CO000' => 'County name invalid',
        'DB000' => 'Generic student insert error',
        'DV030' => 'Student first name not sent',
        'DV040' => 'Student last name missing',
        'DV050' => 'Student sex required',
        'DV060' => 'Court case number required',
        'DV070' => 'Driver license number required',
        'DV080' => 'Citation date required',
        'DV090' => 'Citation county required',
        'DV100' => 'Citation number required',
        'DV110' => 'Reason attending required',
        'DV120' => 'Invalid address state code',
        'DV130' => 'Valid numeric ZIP code required',
        'DV140' => 'Valid numeric phone required',
        'SI000' => 'School instructor required',
        'SI001' => 'School instructor validation failed',
        'ST000' => 'Student first name missing',
        'ST001' => 'Student last name missing',
        'ST002' => 'Student sex field missing',
        'ST003' => 'Reason attending required',
        'ST004' => 'Student date of birth missing',
        'ST005' => 'Reason attending validation failed',
        'VC000' => 'Could not verify class',
        'VC001' => 'Invalid reason code',
        'VC003' => 'Invalid completion date',
        'VI000' => 'Could not verify instructor',
        'VS000' => 'School validation failed',
        'VS010' => 'Invalid School Type',
        'VL000' => 'Login failed',
    ],
];
