# Destroy Tests: Nested Success Redirects And Default Record

Successful nested DELETE destroy calls with exact model-identity expectations and toasts. Covers nested collection redirects at two depths, parent-detail redirects at two depths and deleting a record explicitly marked default; no persistence or replacement-default assertion is implied.

Use deletes the record for each controller. These are alternative route contracts; do not duplicate one success case solely because its destination changes.

## Nested Collection And Default Record

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

use App\Actions\MemberAddresses\DeleteMemberAddress;
use App\Models\MemberAddress;

describe('destroy', function (): void {
    it('deletes the record', function (): void {
        $address = MemberAddress::factory()->createOne();

        signIn(team: $address->member->team);

        mock(DeleteMemberAddress::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (MemberAddress $addressArgument): bool => $addressArgument->is($address));

        $response = delete(route('teams.members.addresses.destroy', [
            'team' => $address->member->team,
            'member' => $address->member,
            'address' => $address,
        ]));

        $response->assertRedirectToRoute('teams.members.addresses.index', [
            'team' => $address->member->team,
            'member' => $address->member,
        ])
            ->assertToast('Address deleted');
    });

    it('deletes the default record', function (): void {
        $defaultAddress = MemberAddress::factory()->createOne(['is_default' => true]);

        signIn(team: $defaultAddress->member->team);

        mock(DeleteMemberAddress::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (MemberAddress $addressArgument): bool => $addressArgument->is($defaultAddress));

        $response = delete(route('teams.members.addresses.destroy', [
            'team' => $defaultAddress->member->team,
            'member' => $defaultAddress->member,
            'address' => $defaultAddress,
        ]));

        $response->assertRedirectToRoute('teams.members.addresses.index', [
            'team' => $defaultAddress->member->team,
            'member' => $defaultAddress->member,
        ])
            ->assertToast('Address deleted');
    });
});
```

## Deep Collection

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

use App\Actions\ServicePlans\DeletePlanRate;
use App\Models\PlanRate;

describe('destroy', function (): void {
    it('deletes the record', function (): void {
        $rate = PlanRate::factory()->createOne();

        signIn(team: $rate->planRule->servicePlan->team);

        mock(DeletePlanRate::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                PlanRate $rateArgument
            ): bool => $rateArgument->is($rate));

        $response = delete(route('teams.service-plans.plan-rules.rates.destroy', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]));

        $response->assertRedirectToRoute('teams.service-plans.plan-rules.rates.index', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
        ])
            ->assertToast('Rate deleted');
    });
});
```

## Parent Detail

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

use App\Actions\WorkOrderLines\DeleteWorkOrderLine;
use App\Models\WorkOrderLine;

describe('destroy', function (): void {
    it('deletes the record', function (): void {
        $line = WorkOrderLine::factory()->createOne();

        signIn(team: $line->workOrder->team);

        mock(DeleteWorkOrderLine::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (WorkOrderLine $itemArgument): bool => $itemArgument->is($line));

        $response = delete(route('teams.work-orders.lines.destroy', [
            'team' => $line->workOrder->team,
            'work_order' => $line->workOrder,
            'line' => $line,
        ]));

        $response->assertRedirectToRoute('teams.work-orders.show', [
            'team' => $line->workOrder->team,
            'work_order' => $line->workOrder,
        ])
            ->assertToast('Work order line deleted');
    });
});
```

## Nested Parent Detail

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

use App\Actions\ServicePlans\DeletePlanRule;
use App\Models\PlanRule;

describe('destroy', function (): void {
    it('deletes the record', function (): void {
        $planRule = PlanRule::factory()->createOne();

        signIn(team: $planRule->servicePlan->team);

        mock(DeletePlanRule::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (PlanRule $planRuleArgument): bool => $planRuleArgument->is($planRule));

        $response = delete(route('teams.service-plans.plan-rules.destroy', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertRedirectToRoute('teams.service-plans.show', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
        ])
            ->assertToast('Plan rule deleted');
    });
});
```
