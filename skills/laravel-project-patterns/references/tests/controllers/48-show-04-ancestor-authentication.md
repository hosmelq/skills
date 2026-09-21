# Show Tests: Two-Parent Record Authentication

Browser GET show two-parent record authentication: a guest redirects to login and an unrelated authenticated tenant gets 403. Keep every valid route parameter for this route depth.

## Authentication And Access

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRate;

describe('show', function (): void {
    it('requires authentication', function (): void {
        $rate = PlanRate::factory()->createOne();

        $response = get(route('teams.service-plans.plan-rules.rates.show', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]));

        $response->assertRedirectToRoute('login');
    });

    it('prevents viewing from an unrelated tenant', function (): void {
        $rate = PlanRate::factory()->createOne();

        signIn();

        $response = get(route('teams.service-plans.plan-rules.rates.show', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]));

        $response->assertForbidden();
    });
});
```
