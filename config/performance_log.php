<?php

return [
    // configure name of log chanel defined in config/logging.php used for logging
    // with null value default log chanel will be used
    'log_channel' => null,
    // thresholds definitions, if anything run longer than threshold service will log warning
    // all thresholds are float value in milliseconds
    // null threshold will turn off any measurring
    // threshold zero value with 'app.debug' true value will log running time for anything as debug
    'database' => [
        # define threshold for single database query
        'slow_query_threshold' => 50,

        # define threshold for one database transaction
        'slow_transaction_threshold' => 300,
    ],
    'migration' => [
        # define threshold for single database query executed in migrations
        'slow_query_threshold' => 150,
    ],
    'http' => [
        # define threshold for whole http request
        'slow_request_threshold' => 1000,
    ],
];
