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
        Schema::create('client_addresses', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('clinic_id')->index();
            $table->foreignId('client_id')->index();

            $table->string('address1');
            $table->string('address2')->nullable();
            $table->string('city');
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
        });
    }
};
