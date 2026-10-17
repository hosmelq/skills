# Models: Static Helpers and Predicates

Add model-owned domain logic that is not an attribute or relation: static normalizers for user-entered codes, static finders that look records up by a secret, and predicates about a record's state, including ones that combine several attributes.

- Keep a helper on the model when only that model's data defines the rule; otherwise it belongs in an action or support class.
- Store a normalized copy of a user-entered code in its own column and build it with the static normalizer.
- Finders exclude records the secret can never be used for again; conditions the caller reports to the user, such as expiry, stay with the caller.
- A model that is active only when neither deactivated nor soft deleted defines its own `isActive()`; composite predicates build on it.
- Test normalizers and predicates with datasets in the unit suite, relying on frozen time for date boundaries, and finders in the integration suite.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property-read null|CarbonImmutable $accepted_at
 * @property-read CarbonImmutable $expires_at
 * @property-read string $token
 */
final class Invitation extends Model
{
    public static function findByToken(string $token): null|self
    {
        return self::query()
            ->where('token', hash('sha256', $token))
            ->whereNull('accepted_at')
            ->first();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isBefore(now());
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $code
 * @property-read string $normalized_code
 */
final class Room extends Model
{
    public static function normalizeCode(string $code): string
    {
        return str($code)->upper()->replaceMatches('/[^A-Z0-9]/', '')->toString();
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VisitStage;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read null|CarbonImmutable $deactivated_at
 * @property-read VisitStage $stage
 */
final class VisitStatus extends Model
{
    use SoftDeletes;

    public function canBeInitial(): bool
    {
        return $this->isActive() && $this->stage === VisitStage::CheckedIn;
    }

    public function isActive(): bool
    {
        return $this->deactivated_at === null && ! $this->trashed();
    }
}
```

```php
<?php

declare(strict_types=1);

use App\Models\Invitation;
use Carbon\CarbonImmutable;

it(
    'determines whether an invitation is expired',
    function (CarbonImmutable $expiresAt, bool $expired): void {
        $invitation = new Invitation(['expires_at' => $expiresAt]);

        expect($invitation->isExpired())->toBe($expired);
    },
)->with([
    'expired' => [fn (): CarbonImmutable => now()->subSecond(), true],
    'expires now' => [fn (): CarbonImmutable => now(), false],
    'not expired' => [fn (): CarbonImmutable => now()->addSecond(), false],
]);
```

```php
<?php

declare(strict_types=1);

use App\Models\Room;

it('normalizes room codes', function (string $code): void {
    expect(Room::normalizeCode($code))->toBe('EX004992');
})->with([
    'dash separator' => 'EX-004992',
    'lowercase' => 'ex-004992',
    'mixed separators' => 'ex.004/992',
    'no separator' => 'EX004992',
    'space separator' => 'EX 004992',
]);
```

```php
<?php

declare(strict_types=1);

use App\Enums\VisitStage;
use App\Models\VisitStatus;

it('detects statuses that can be initial', function (): void {
    $visitStatus = new VisitStatus([
        'deactivated_at' => null,
        'stage' => VisitStage::CheckedIn,
    ]);

    expect($visitStatus->canBeInitial())->toBeTrue();
});

it('detects statuses that cannot be initial', function (array $attributes): void {
    $visitStatus = new VisitStatus($attributes);

    expect($visitStatus->canBeInitial())->toBeFalse();
})->with([
    'deactivated' => [[
        'deactivated_at' => '2026-03-30 15:33:00',
        'stage' => VisitStage::CheckedIn,
    ]],
    'other stage' => [[
        'deactivated_at' => null,
        'stage' => VisitStage::Completed,
    ]],
    'soft deleted' => [[
        'deactivated_at' => null,
        'deleted_at' => '2026-03-30 15:33:00',
        'stage' => VisitStage::CheckedIn,
    ]],
]);
```

```php
<?php

declare(strict_types=1);

use App\Models\Invitation;

it('finds unaccepted invitations by their plain token', function (): void {
    $invitation = Invitation::factory()->withToken('invitation-token')->createOne();

    expect($invitation->token)
        ->toBe(hash('sha256', 'invitation-token'))
        ->and(Invitation::findByToken('invitation-token')?->is($invitation))->toBeTrue();
});

it('does not find accepted invitations by token', function (): void {
    Invitation::factory()->accepted()->withToken('invitation-token')->createOne();

    expect(Invitation::findByToken('invitation-token'))->toBeNull();
});
```
