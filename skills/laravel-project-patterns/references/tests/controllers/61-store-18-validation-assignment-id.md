# Store Tests: Validation Assignment Id

POST store: Complete related-ID/label dataset plus foreign-tenant and soft-deleted relation rejection.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Member;
use App\Models\ServicePlan;
use App\Support\Sqid;
use Illuminate\Support\Str;

describe('store', function (): void {
    it('validates fields', function (array $data, array $expected): void {
        $member = Member::factory()->createOne();

        login(team: $member->team);

        $response = post(route('teams.members.cabinets.store', [
            'team' => $member->team,
            'member' => $member,
        ]), $data);

        $response->assertRedirectBackWithErrors($expected);
    })->with([
        'exists' => [
            'data' => fn (): array => [
                'service_plan_id' => resolve(Sqid::class)->encode(PHP_INT_MAX),
            ],
            'expected' => [
                'service_plan_id' => 'The selected service plan id is invalid.',
            ],
        ],
        'max:255 (string)' => [
            'data' => [
                'label' => Str::repeat('a', 256),
            ],
            'expected' => [
                'label' => 'The label field must not be greater than 255 characters.',
            ],
        ],
        'required' => [
            'data' => [],
            'expected' => [
                'service_plan_id' => 'The service plan id field is required.',
            ],
        ],
    ]);

    it('rejects a newly assigned relation from another tenant', function (): void {
        $member = Member::factory()->createOne();
        $servicePlan = ServicePlan::factory()->createOne();

        login(team: $member->team);

        $response = post(route('teams.members.cabinets.store', [
            'team' => $member->team,
            'member' => $member,
        ]), [
            'service_plan_id' => $servicePlan->sqid,
        ]);

        $response->assertRedirectBackWithErrors([
            'service_plan_id' => 'The selected service plan id is invalid.',
        ]);
    });

    it('rejects a newly assigned soft deleted relation', function (): void {
        $member = Member::factory()->createOne();
        $servicePlan = ServicePlan::factory()->trashed()->recycle($member->team)->createOne();

        login(team: $member->team);

        $response = post(route('teams.members.cabinets.store', [
            'team' => $member->team,
            'member' => $member,
        ]), [
            'service_plan_id' => $servicePlan->sqid,
        ]);

        $response->assertRedirectBackWithErrors([
            'service_plan_id' => 'The selected service plan id is invalid.',
        ]);
    });
});
```
