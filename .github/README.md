# Simple as fuck / Laravel performance log

## Configuration

```php
    'performance' => [
        'log_channel' => null,
        'database' => [
            'slow_query_threshold' => 50,
            'slow_transaction_threshold' => 300,
        ],
        'http' => [
            'slow_request_threshold' => 1000,
        ],
    ],
```
