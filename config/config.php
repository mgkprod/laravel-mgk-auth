<?php

/*
 * You can place your custom package configuration in here.
 */

return [
    /*
    * Auth endpoint used to authenticate User and get his access token, and so on.
    */
    'host' => env('MGKAUTH_HOST', 'https://auth.mgk.dev'),

    /*
     * Does we need to establish a secured connection to Auth endpoint?
     */
    'verify_ssl' => env('MGKAUTH_VERIFY_SSL', true),

    /*
    * Credentials used to authenticate to Auth for this very application.
    */
    'credentials' => [
        'client_id' => env('MGKAUTH_CLIENT_ID'),
        'client_secret' => env('MGKAUTH_CLIENT_SECRET'),
    ],
];
