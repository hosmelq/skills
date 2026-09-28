# Migrations: Constrain a Selected State

The initial selection is unique per team only while active and not deleted. Name uniqueness excludes deleted rows but still includes deactivated rows. The CHECK permits an initial selection only in the allowed base status.

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('work_order_statuses', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('team_id')->index();

            $table->string('base_status');
            $table->string('color')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_initial')->default(false);
            $table->boolean('is_member_visible')->default(false);
            $table->caseInsensitiveText('name');
            $table->unsignedInteger('sort_order');

            $table->timestamps();
            $table->softDeletes();

            $table->uniqueIndex(
                'team_id',
                'work_order_statuses_active_initial_unique',
            )->where('is_initial = true AND deactivated_at IS NULL AND deleted_at IS NULL');
            $table->uniqueIndex(
                ['team_id', 'name'],
                'work_order_statuses_active_name_unique',
            )->where(fn (Builder $query): Builder => $query->whereNull('deleted_at'));
        });

        DB::statement(<<<'SQL'
            ALTER TABLE work_order_statuses
            ADD CONSTRAINT work_order_statuses_initial_base_status_check
            CHECK (base_status = 'open' OR is_initial = false)
        SQL);
    }
};
```
