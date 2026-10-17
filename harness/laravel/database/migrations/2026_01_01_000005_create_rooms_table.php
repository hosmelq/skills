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
        Schema::create('rooms', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('clinic_id')->index();

            $table->unsignedSmallInteger('capacity');
            $table->string('code');
            $table->timestamp('deactivated_at')->nullable();
            $table->string('name');
            $table->string('normalized_code');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }
};
