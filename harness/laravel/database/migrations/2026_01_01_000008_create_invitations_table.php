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
        Schema::create('invitations', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('clinic_id')->index();
            $table->foreignId('invited_by_user_id')->index();

            $table->timestamp('accepted_at')->nullable();
            $table->caseInsensitiveText('email');
            $table->timestamp('expires_at');
            $table->string('role');
            $table->string('token')->unique();

            $table->timestamps();
        });
    }
};
