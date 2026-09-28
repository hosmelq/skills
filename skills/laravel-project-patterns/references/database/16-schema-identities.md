# Migrations: Store Case-Insensitive Identities

Use `citext` through `caseInsensitiveText()` for case-insensitive identities. Preserve nullable provider credentials, unique versus indexed email fields, and explicit code expiry and usage timestamps.

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('current_team_id')->nullable()->index();

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

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->caseInsensitiveText('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }
};
```

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('one_time_passwords', function (Blueprint $table): void {
            $table->id();

            $table->string('code')->unique();
            $table->caseInsensitiveText('email')->index();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();

            $table->timestamps();
        });
    }
};
```
