# Migrations: Constrain Coordinate Pairs

Allow both coordinates to be null or require both together, within their respective inclusive ranges. Default selection uses a separate partial unique index per member; it excludes deleted rows and non-default addresses.

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
        Schema::create('member_addresses', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('member_id')->index();
            $table->foreignId('team_id')->index();

            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('company')->nullable();
            $table->string('country_code', 2);
            $table->string('first_name')->nullable();
            $table->boolean('is_default')->default(false);
            $table->string('label')->nullable();
            $table->string('last_name')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone_number')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('province_code', 3)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->uniqueIndex(
                'member_id',
                'member_addresses_active_default_unique',
            )->where('deleted_at IS NULL AND is_default = true');
        });

        DB::statement(<<<'SQL'
            ALTER TABLE member_addresses
            ADD CONSTRAINT member_addresses_latitude_range_check
            CHECK (latitude IS NULL OR latitude BETWEEN -90 AND 90)
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE member_addresses
            ADD CONSTRAINT member_addresses_longitude_range_check
            CHECK (longitude IS NULL OR longitude BETWEEN -180 AND 180)
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE member_addresses
            ADD CONSTRAINT member_addresses_coordinate_pair_check
            CHECK (
                (latitude IS NULL AND longitude IS NULL)
                OR (latitude IS NOT NULL AND longitude IS NOT NULL)
            )
        SQL);
    }
};
```
