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
        Schema::create('visits', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('attending_veterinarian_id')->index();
            $table->foreignId('clinic_id')->index();
            $table->foreignId('pet_id')->index();
            $table->foreignId('referring_veterinarian_id')->nullable()->index();
            $table->foreignId('room_id')->index();

            $table->text('notes')->nullable();
            $table->timestamp('scheduled_at');

            $table->timestamps();
        });
    }
};
