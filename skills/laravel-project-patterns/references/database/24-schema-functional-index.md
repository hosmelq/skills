# Migrations: Index a Normalized Expression

Use an explicitly named functional unique index for a normalized string. Keep the expression parenthesized and the predicate explicit: null references and deleted rows do not participate. The example includes the columns required by this index.

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('team_id')->index();

            $table->string('reference')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->uniqueIndex(
                ['team_id', '(lower(reference))'],
                'work_orders_active_reference_unique',
            )->where('reference IS NOT NULL AND deleted_at IS NULL');
        });
    }
};
```
