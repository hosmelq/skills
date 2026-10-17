<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('pets', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('client_id')->index();
            $table->foreignId('clinic_id')->index();

            $table->unsignedSmallInteger('body_condition_score')->nullable();
            $table->timestamp('deceased_at')->nullable();
            $table->text('deceased_reason')->nullable();
            $table->caseInsensitiveText('name');
            $table->timestamp('registered_at')->nullable();
            $table->decimal('weight', 8, 4)->nullable();
            $table->string('weight_unit')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE pets
            ADD CONSTRAINT pets_body_condition_score_range_check
            CHECK (body_condition_score IS NULL OR body_condition_score BETWEEN 1 AND 9)
            SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE pets
            ADD CONSTRAINT pets_deceased_reason_check
            CHECK (deceased_reason IS NULL OR deceased_at IS NOT NULL)
            SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE pets
            ADD CONSTRAINT pets_weight_pair_check
            CHECK (
                (weight IS NULL AND weight_unit IS NULL)
                OR (weight IS NOT NULL AND weight > 0 AND weight_unit IS NOT NULL)
            )
            SQL);
    }
};
