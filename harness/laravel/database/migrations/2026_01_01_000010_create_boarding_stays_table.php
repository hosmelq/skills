<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('boarding_stays', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('clinic_id')->index();
            $table->foreignId('pet_id')->index();
            $table->foreignId('room_id')->index();

            $table->timestamp('ends_at')->nullable();
            $table->timestamp('starts_at');

            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement(<<<'SQL'
                ALTER TABLE boarding_stays
                ADD CONSTRAINT boarding_stays_active_range_exclusion
                EXCLUDE USING gist (
                    room_id WITH =,
                    tsrange(starts_at, ends_at, '[)') WITH &&
                )
                WHERE (deleted_at IS NULL)
            SQL);
    }
};
