<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('test_models', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('parent_id')->nullable()->index();

            $table->timestamp('deactivated_at')->nullable();
            $table->softDeletes();
        });
    }
};
