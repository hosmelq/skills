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
        Schema::create('users', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('current_clinic_id')->nullable()->index();

            $table->caseInsensitiveText('apple_email')->nullable()->unique();
            $table->string('apple_id')->nullable()->unique();
            $table->caseInsensitiveText('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('first_name')->nullable();
            $table->caseInsensitiveText('google_email')->nullable()->unique();
            $table->string('google_id')->nullable()->unique();
            $table->string('last_name')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();

            $table->timestamps();
        });
    }
};
