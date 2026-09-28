# Migrations: Scope Uniqueness to Live Rows

Limit uniqueness to rows where `deleted_at` is null. Keep nullable case-insensitive values distinct from required normalized codes and required parent pairs. Deactivation does not participate in these predicates.

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('team_id')->index();

            $table->caseInsensitiveText('email')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->text('note')->nullable();
            $table->string('phone_number')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->uniqueIndex(
                ['team_id', 'email'],
                'members_active_email_unique',
            )->where(fn (Builder $query): Builder => $query->whereNull('deleted_at'));
            $table->uniqueIndex(
                ['team_id', 'phone_number'],
                'members_active_phone_number_unique',
            )->where(fn (Builder $query): Builder => $query->whereNull('deleted_at'));
        });
    }
};
```

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('cabinets', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('member_id')->index();
            $table->foreignId('service_plan_id')->index();
            $table->foreignId('team_id')->index();

            $table->string('code');
            $table->timestamp('deactivated_at')->nullable();
            $table->string('label')->nullable();
            $table->string('normalized_code');

            $table->timestamps();
            $table->softDeletes();

            $table->uniqueIndex(
                ['member_id', 'service_plan_id'],
                'cabinets_active_member_service_plan_unique',
            )->where(fn (Builder $query): Builder => $query->whereNull('deleted_at'));
            $table->uniqueIndex(
                ['team_id', 'normalized_code'],
                'cabinets_active_normalized_code_unique',
            )->where(fn (Builder $query): Builder => $query->whereNull('deleted_at'));
        });
    }
};
```
