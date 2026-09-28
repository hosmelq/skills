# Migrations: Constrain Related Values

Keep weight with its unit and all dimensions with their unit: each group is either entirely null or complete with positive measurements. Quantity must be positive. Monetary value may be zero, but it must accompany its currency.

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('work_order_lines', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('item_group_id')->nullable()->index();
            $table->foreignId('team_id')->index();
            $table->foreignId('work_order_id')->index();

            $table->string('currency_code', 3)->nullable();
            $table->text('description');
            $table->string('dimension_unit')->nullable();
            $table->decimal('height', 8, 4)->nullable();
            $table->decimal('length', 8, 4)->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_value', 8, 2)->nullable();
            $table->decimal('weight', 8, 4)->nullable();
            $table->string('weight_unit')->nullable();
            $table->decimal('width', 8, 4)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE work_order_lines
            ADD CONSTRAINT work_order_lines_quantity_check
            CHECK (quantity > 0)
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE work_order_lines
            ADD CONSTRAINT work_order_lines_weight_pair_check
            CHECK (
                (weight IS NULL AND weight_unit IS NULL)
                OR (weight IS NOT NULL AND weight > 0 AND weight_unit IS NOT NULL)
            )
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE work_order_lines
            ADD CONSTRAINT work_order_lines_dimensions_group_check
            CHECK (
                (length IS NULL AND width IS NULL AND height IS NULL AND dimension_unit IS NULL)
                OR (
                    length IS NOT NULL
                    AND length > 0
                    AND width IS NOT NULL
                    AND width > 0
                    AND height IS NOT NULL
                    AND height > 0
                    AND dimension_unit IS NOT NULL
                )
            )
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE work_order_lines
            ADD CONSTRAINT work_order_lines_value_currency_check
            CHECK (
                (unit_value IS NULL AND currency_code IS NULL)
                OR (unit_value IS NOT NULL AND unit_value >= 0 AND currency_code IS NOT NULL)
            )
        SQL);
    }
};
```
