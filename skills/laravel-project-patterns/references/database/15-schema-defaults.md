# Migrations: Define Typed Defaults

Keep database defaults aligned with the model. Enum values, false, nullable fields and required fields are distinct contracts. These indexed IDs do not declare foreign key constraints.

```php
<?php

declare(strict_types=1);

use App\Enums\AssignmentMode;
use App\Enums\CodeAlphabet;
use App\Enums\CurrencyCode;
use App\Enums\UnitSystem;
use App\Enums\WeightUnit;
use Illuminate\Database\Migrations\Migration;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('owner_id')->index();

            $table->string('assignment_mode')->default(AssignmentMode::RequiresApproval);
            $table->boolean('cabinets_enabled')->default(false);
            $table->string('code_format_alphabet_type')->default(CodeAlphabet::Alphanumeric);
            $table->integer('code_format_length')->default(6);
            $table->string('code_format_prefix')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone_number')->nullable();
            $table->string('country_code', 2);
            $table->string('currency_code', 3)->default(CurrencyCode::USD);
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('timezone');
            $table->string('unit_system')->default(UnitSystem::Imperial);
            $table->string('weight_unit')->default(WeightUnit::Pounds);
            $table->boolean('work_orders_enabled')->default(false);

            $table->timestamps();
        });
    }
};
```
