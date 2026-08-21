<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Meeting Alert Recipient
    |--------------------------------------------------------------------------
    |
    | Email address that receives meeting-related alerts (created/updated,
    | missed, and upcoming reminders). Leave unset to disable these alerts.
    |
    */

    'recipient_email' => env('ALERTS_RECIPIENT_EMAIL'),

];
