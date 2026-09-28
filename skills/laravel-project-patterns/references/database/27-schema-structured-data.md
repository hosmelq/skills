# Migrations: Store Nullable Structured Data

Use a nullable JSON column when structured data may be absent. The focused schema keeps its owning team, name and lifecycle columns; JSON storage is separate from the model's cast and validation.

For facility coordinates, apply [coordinate pairs](23-schema-coordinate-pairs.md) using the `facilities_` constraint prefix.

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('team_id')->index();

            $table->string('name');
            $table->json('opening_hours')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }
};
```
