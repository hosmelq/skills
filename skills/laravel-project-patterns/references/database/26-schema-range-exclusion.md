# Migrations: Prevent Overlapping Ranges

Use `btree_gist` before this migration. Exclude overlapping half-open ranges within the same rule, considering only non-deleted rows. A null maximum creates an unbounded upper end; adjacent endpoints remain valid.

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('plan_rates', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('plan_rule_id')->index();
            $table->foreignId('team_id')->index();

            $table->decimal('maximum_weight', 8, 4)->nullable();
            $table->decimal('minimum_weight', 8, 4);
            $table->string('name');
            $table->decimal('rate', 8, 2);

            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE plan_rates
            ADD CONSTRAINT plan_rates_active_range_exclusion
            EXCLUDE USING gist (
                plan_rule_id WITH =,
                numrange(minimum_weight, maximum_weight, '[)') WITH &&
            )
            WHERE (deleted_at IS NULL)
        SQL);
    }
};
```
