# Store Tests: Failures Related Availability

Pest POST store: Selected member, cabinet or service becomes unavailable after request validation; each concrete field retained.

The action is mocked to throw. These assertions verify controller translation, not the underlying business guard.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\WorkOrders\CreateWorkOrder;
use App\Exceptions\WorkOrders\CabinetIsUnavailable;
use App\Exceptions\WorkOrders\MemberIsUnavailable;
use App\Exceptions\WorkOrders\ServicePlanIsUnavailable;
use App\Models\Team;

describe('store', function (): void {
    it('maps an unavailable relation rejection to validation: member_id', function (): void {
        $team = Team::factory()->createOne();

        signIn(team: $team);

        mock(CreateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(MemberIsUnavailable::becauseItIsUnavailable());

        $response = post(route('teams.work-orders.store', $team));

        $response->assertRedirectBackWithErrors([
            'member_id' => 'The selected value is no longer available.',
        ]);
    });

    it('maps an unavailable relation rejection to validation: cabinet_id', function (): void {
        $team = Team::factory()->createOne();

        signIn(team: $team);

        mock(CreateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(CabinetIsUnavailable::becauseItIsUnavailable());

        $response = post(route('teams.work-orders.store', $team));

        $response->assertRedirectBackWithErrors([
            'cabinet_id' => 'The selected value is no longer available.',
        ]);
    });

    it('maps an unavailable relation rejection to validation: service_plan_id', function (): void {
        $team = Team::factory()->createOne();

        signIn(team: $team);

        mock(CreateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(ServicePlanIsUnavailable::becauseItIsUnavailable());

        $response = post(route('teams.work-orders.store', $team));

        $response->assertRedirectBackWithErrors([
            'service_plan_id' => 'The selected value is no longer available.',
        ]);
    });
});
```
