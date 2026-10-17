<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithCachedConfig;
use Illuminate\Foundation\Testing\WithCachedRoutes;
use Illuminate\Support\Facades\Http;
use Tests\Support\Models\TestModel;

abstract class TestCase extends BaseTestCase
{
    use LazilyRefreshDatabase {
        migrateFreshUsing as baseMigrateFreshUsing;
    }
    use WithCachedConfig;
    use WithCachedRoutes;

    protected function migrateFreshUsing(): array
    {
        return array_merge($this->baseMigrateFreshUsing(), [
            '--path' => [
                'database/migrations',
                'tests/migrations',
            ],
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        Relation::enforceMorphMap([
            'test_model' => TestModel::class,
        ]);
    }
}
