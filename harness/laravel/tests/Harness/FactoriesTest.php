<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Factories\Factory;

dataset('factory states', function (): iterable {
    foreach (glob(__DIR__.'/../../database/factories/*Factory.php') as $file) {
        $class = 'Database\\Factories\\'.basename($file, '.php');
        $reflection = new ReflectionClass($class);

        yield str(basename($file))->before('Factory').' definition' => [$class, null];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class === $class && $method->getNumberOfRequiredParameters() === 0
                && ! in_array($method->name, ['configure', 'definition'], true)) {
                yield str(basename($file))->before('Factory').' '.$method->name => [$class, $method->name];
            }
        }
    }
});

it('creates valid records from every factory state', function (string $class, null|string $state): void {
    /** @var Factory $factory */
    $factory = $class::new();

    $model = ($state === null ? $factory : $factory->{$state}())->createOne();

    expect($model->exists)->toBeTrue();
})->with('factory states');

it('seeds the local database', function (): void {
    $this->seed();

    expect(App\Models\Clinic::query()->count())->toBe(2);
});
