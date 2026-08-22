<?php

return [
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
    ],

    'africastalking' => [
        'username' => env('AFRICASTALKING_USERNAME'),
        'api_key' => env('AFRICASTALKING_API_KEY'),
        'from' => env('AFRICASTALKING_FROM', 'MKULIMA'),
    ],

    'mpesa' => [
        'consumer_key' => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        'passkey' => env('MPESA_PASSKEY'),
        'shortcode' => env('MPESA_SHORTCODE'),
        'env' => env('MPESA_ENV', 'sandbox'),
        'callback_secret' => env('MPESA_CALLBACK_SECRET'),
    ],

    'openweather' => [
        'api_key' => env('OPENWEATHER_API_KEY'),
    ],

    'ratin' => [
        'base_url' => env('RATIN_BASE_URL', 'https://ratin.net/ratinapp/api'),
        'usd_to_tzs_rate' => env('RATIN_USD_TO_TZS_RATE', 2600),
    ],

    'weather' => [
        'use_open_meteo' => env('WEATHER_USE_OPEN_METEO', true),
    ],

    /*
     |------------------------------------------------------------------
     | SMS / OTP delivery
     |------------------------------------------------------------------
     | 'provider' selects an implementation of App\Contracts\SmsProvider.
     | Swapping Africa's Talking for Twilio (or a future aggregator) is a
     | one-line env change; nothing in the authentication or OTP code knows
     | which gateway is behind it.
     |
     | 'log' writes messages to the application log instead of sending them,
     | which is what local development and the test suite use.
     */
    'sms' => [
        'provider' => env('SMS_PROVIDER', env('SMS_GATEWAY', 'africastalking')),
        'gateway' => env('SMS_GATEWAY', 'africastalking'),
        'sender_id' => env('SMS_SENDER_ID', 'MKULIMA'),
        'webhook_secret' => env('SMS_WEBHOOK_SECRET'),
    ],

    /*
     |------------------------------------------------------------------
     | Short links (/c/{slug})
     |------------------------------------------------------------------
     | Hosts a short link may redirect straight to. Subdomains of a listed
     | host are included. Anything else shows a "you are leaving" page first,
     | so the platform's domain cannot be borrowed for a phishing redirect.
     */
    'short_links' => [
        'allowed_hosts' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('SHORT_LINK_ALLOWED_HOSTS', 'mkulimaforum.app,mkulimaforum.com,wa.me,youtube.com,youtu.be,facebook.com,instagram.com,x.com,twitter.com,linkedin.com,tiktok.com'))
        ))),
    ],

    'ivr' => [
        'webhook_secret' => env('IVR_WEBHOOK_SECRET'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    'google_drive' => [
        'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),
        'credentials_path' => env('GOOGLE_DRIVE_CREDENTIALS_PATH', 'storage/app/private/google-drive-service-account.json'),
    ],

    'social' => [
        'google_client_ids' => array_values(array_filter(array_map('trim', explode(',', (string) env('GOOGLE_AUTH_CLIENT_IDS', ''))))),
        'apple_client_ids' => array_values(array_filter(array_map('trim', explode(',', (string) env('APPLE_AUTH_CLIENT_IDS', ''))))),
        'android_package' => env('ANDROID_APP_PACKAGE', 'app.mkulimaforum.mobile'),
    ],
];
