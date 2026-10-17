# Database: Pivot Table

Create the pivot table for a many-to-many membership with its own `id`, timestamps and a unique pair.

- Pivots have no soft deletes.

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('clinic_user', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('clinic_id')->index();
            $table->foreignId('user_id')->index();

            $table->timestamps();

            $table->unique(['clinic_id', 'user_id']);
        });
    }
};
```
