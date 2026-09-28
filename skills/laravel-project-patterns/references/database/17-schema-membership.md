# Migrations: Constrain Membership Pairs

Use a composite unique constraint for the pair itself. The pivot uses the generated constraint name; the review table names it explicitly and keeps reviewer/time nullable. Neither example adds a soft-delete predicate.

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('team_user', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('team_id')->index();
            $table->foreignId('user_id')->index();

            $table->timestamps();

            $table->unique(['team_id', 'user_id']);
        });
    }
};
```

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('member_id')->index();
            $table->foreignId('team_id')->index();
            $table->foreignId('requested_by_user_id')->index();
            $table->foreignId('reviewed_by_user_id')->nullable()->index();

            $table->timestamp('requested_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->string('status');

            $table->timestamps();

            $table->unique(
                ['member_id', 'team_id'],
                'enrollments_member_team_unique',
            );
        });
    }
};
```
