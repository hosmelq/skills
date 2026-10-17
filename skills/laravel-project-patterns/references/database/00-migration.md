# Database: Create Table Migration

Create a migration for a new application table with PostgreSQL Enhanced's schema builder: indexed foreign keys, columns with lengths, precisions and defaults, timestamps and soft deletes when records are soft-deleted; also PostgreSQL extension migrations. Constraints and their tests are separate references.

- Define only `up()`.
- Declare columns in groups separated by blank lines: `id`, foreign keys, other columns, timestamps with soft deletes, then indexes and constraints. Sort each group alphabetically.
- Foreign keys are `foreignId()->index()` without database constraints, adding `nullable()` for optional parents. Tenant-owned tables, children included, store an indexed tenant key even when a parent already leads to the tenant.
- Size columns to their data: ISO country codes `string(…, 2)`, currency codes `string(…, 3)`, money `decimal(8, 2)`, weights and dimensions `decimal(8, 4)`, coordinates `decimal(10, 7)`.
- Give settings and flags the model defaults in `$attributes` a matching `default()`, passing enum cases directly.
- Use `caseInsensitiveText()` for text that must match regardless of case.

```php
<?php

declare(strict_types=1);

use App\Enums\CurrencyCode;
use Illuminate\Database\Migrations\Migration;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('clinics', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('owner_id')->index();

            $table->boolean('accepts_emergencies')->default(false);
            $table->decimal('consultation_fee', 8, 2);
            $table->string('country_code', 2);
            $table->string('currency_code', 3)->default(CurrencyCode::USD);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->string('name');
            $table->json('opening_hours')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('slug')->unique();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedSmallInteger('visit_duration_minutes')->default(30);

            $table->timestamps();
        });
    }
};
```

PostgreSQL extensions get their own migration, dated before every table migration.

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
