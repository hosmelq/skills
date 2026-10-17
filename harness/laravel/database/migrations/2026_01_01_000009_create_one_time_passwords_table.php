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
        Schema::create('one_time_passwords', function (Blueprint $table): void {
            $table->id();

            $table->string('code');
            $table->string('email');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();

            $table->timestamps();
        });
    }
};
