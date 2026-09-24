# Model Tests: Trait Composition

Unit tests for recursive model trait composition: deactivation, computed public identifiers, soft deletion and sorting. These checks do not prove trait behavior.

Use only the traits present in the inspected model, in the order shown; test their behavior separately.

```php
<?php

declare(strict_types=1);

use App\Models\Concerns\HasDeactivation;
use App\Models\Concerns\HasPublicId;
use App\Models\ItemGroup;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\SortableTrait;

it('uses the deactivation trait', function (): void {
    $traits = class_uses_recursive(ItemGroup::class);

    expect($traits)->toContain(HasDeactivation::class);
});

it('uses the public identifier trait', function (): void {
    $traits = class_uses_recursive(ItemGroup::class);

    expect($traits)->toContain(HasPublicId::class);
});

it('uses the soft deletion trait', function (): void {
    $traits = class_uses_recursive(ItemGroup::class);

    expect($traits)->toContain(SoftDeletes::class);
});

it('uses the ordering trait', function (): void {
    $traits = class_uses_recursive(ItemGroup::class);

    expect($traits)->toContain(SortableTrait::class);
});
```
