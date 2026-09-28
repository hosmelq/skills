# Configuration: Storage URLs and Record Ordering

The first block contains selected entries for `config/filesystems.php`: trim trailing slashes before appending `/storage`. The second block is `config/eloquent-sortable.php`; keep `sort_order`, creation sorting and timestamp behavior aligned with the model.

```php
<?php

declare(strict_types=1);

return [
    'disks' => [
        'public' => [
            'url' => mb_rtrim((string) env('APP_URL', 'http://localhost'), '/').'/storage',
        ],
    ],
];
```

```php
<?php

declare(strict_types=1);

return [
    'ignore_timestamps' => false,
    'order_column_name' => 'sort_order',
    'sort_when_creating' => true,
];
```
