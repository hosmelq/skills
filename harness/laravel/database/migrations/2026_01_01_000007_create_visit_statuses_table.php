<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('visit_statuses', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('clinic_id')->index();

            $table->timestamp('deactivated_at')->nullable();
            $table->boolean('is_initial')->default(false);
            $table->caseInsensitiveText('name');
            $table->unsignedInteger('sort_order');
            $table->string('stage');

            $table->timestamps();
            $table->softDeletes();

            $table->uniqueIndex('clinic_id', 'visit_statuses_active_initial_unique')
                ->where('is_initial = true AND deactivated_at IS NULL AND deleted_at IS NULL');
            $table->uniqueIndex(['clinic_id', 'name'], 'visit_statuses_active_name_unique')
                ->where(fn (Builder $query): Builder => $query->whereNull('deleted_at'));
        });
    }
};
