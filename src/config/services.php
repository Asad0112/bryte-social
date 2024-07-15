<?php

    return [
        'facebook' => [
            'app_id' => getenv('FACEBOOK_APP_ID'),
            'app_secret' => getenv('FACEBOOK_APP_SECRET'),
            'redirect_url' => getenv('FACEBOOK_REDIRECT_URL'),
        ],
        'twitter' => [
            'consumer_key' => getenv('TWITTER_CONSUMER_KEY'),
            'consumer_secret' => getenv('TWITTER_CONSUMER_SECRET'),
            'redirect_url' => getenv('TWITTER_CALLBACK_URL'),
            'client_id' => getenv('TWITTER_CLIENT_ID'),
            'client_secret' => getenv('TWITTER_CLIENT_SECRET'),
            'api_base_url' => getenv('TWITTER_API_BASE_URL'),
            'scope' => getenv('TWITTER_SCOPE'),
        ]
    ];
