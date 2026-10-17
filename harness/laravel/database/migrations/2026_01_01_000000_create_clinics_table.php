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
