# Migrations: Enable PostgreSQL Extensions

Run these migrations before tables that use case-insensitive text or GiST equality operators. They require `tpetry/laravel-postgresql-enhanced`; use the enhanced Schema facade and Blueprint together where package column or index methods are needed.

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::createExtensionIfNotExists('citext');
    }
};
```

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::createExtensionIfNotExists('btree_gist');
    }
};
```
