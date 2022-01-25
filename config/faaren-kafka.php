<?php

return [
    'broker' => [
        'url' => env('FAA_KAFKA_BROKER_URL', null),
        /**
         * Auth Credentials for Kafka, can be ignored if config is used
         */
        'auth' => [
            'user' => env('FAA_KAFKA_BROKER_AUTH_USER'),
            'password' => env('FAA_KAFKA_BROKER_AUTH_PASSWORD')
        ],
        /**
         * Additional Config for Kafka
         */
        'config' => [
            'bootstrap.servers' => env('FAA_KAFKA_BROKER_URL', null),
            'security.protocol' => 'SASL_SSL',
            'sasl.mechanism' => 'PLAIN',
            'sasl.username' => env('FAA_KAFKA_BROKER_AUTH_USER'),
            'sasl.password' => env('FAA_KAFKA_BROKER_AUTH_PASSWORD')
        ]
    ],
    /**
     * Specific Settings for Kafka Registry
     */
    'registry' => [
        'url' => env('FAA_KAFKA_REGISTRY_URL', null),
        'auth' => [
            'user' => env('FAA_KAFKA_REGISTRY_AUTH_USER', null),
            'password' => env('FAA_KAFKA_REGISTRY_AUTH_PASSWORD', null)
        ]
    ],
    /**
     * Specific Settings for Kafka Consumer
     */
    'consumer' => [
        'group-id' => env('FAA_KAFKA_CONSUMER_GROUP_ID', 'consumer')
    ]
];