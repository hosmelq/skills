# Seeders: Compose Related Fixtures

Call the local seeder from `DatabaseSeeder`. Inject the action that ensures initial state, create the shared owner, sequence team attributes and sync membership. Preserve parent associations and the contiguous bounded/open-ended rate ranges. These seeders insert fixtures whenever invoked; the class name alone does not restrict the environment.

```php
<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(LocalSeeder::class);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\WorkOrderStatuses\EnsureInitialWorkOrderStatus;
use App\Enums\CountryCode;
use App\Models\Facility;
use App\Models\Member;
use App\Models\PlanRate;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class LocalSeeder extends Seeder
{
    public function run(EnsureInitialWorkOrderStatus $ensureInitialWorkOrderStatus): void
    {
        $user = User::factory()->createOne([
            'email' => 'test@example.com',
        ]);

        $teams = Team::factory()
            ->count(2)
            ->for($user, 'owner')
            ->sequence(['name' => 'Local'], [])
            ->create();

        $user->teams()->sync($teams);

        $teams->each(function (Team $team) use ($ensureInitialWorkOrderStatus): void {
            $ensureInitialWorkOrderStatus->handle($team);

            Member::factory()
                ->for($team)
                ->withDefaultAddress()
                ->createOne();

            Facility::factory()
                ->for($team)
                ->count(4)
                ->create();

            ServicePlan::factory()
                ->for($team)
                ->count(2)
                ->create()
                ->each(function (ServicePlan $servicePlan): void {
                    $firstPlanRule = PlanRule::factory()
                        ->for($servicePlan)
                        ->forCountry(CountryCode::Canada)
                        ->withoutRounding()
                        ->createOne([
                            'minimum_chargeable_weight' => 1,
                            'name' => 'First region',
                        ]);

                    $secondPlanRule = PlanRule::factory()
                        ->for($servicePlan)
                        ->forCountry(CountryCode::Japan)
                        ->roundUp()
                        ->createOne([
                            'minimum_chargeable_weight' => 11,
                            'name' => 'Second region',
                        ]);

                    foreach ([$firstPlanRule, $secondPlanRule] as $planRule) {
                        PlanRate::factory()
                            ->for($planRule, 'planRule')
                            ->forRange(0, 1)
                            ->createOne(['name' => '0 to 1']);

                        PlanRate::factory()
                            ->for($planRule, 'planRule')
                            ->forRange(1, 5)
                            ->createOne(['name' => '1 to 5']);

                        PlanRate::factory()
                            ->for($planRule, 'planRule')
                            ->forRange(5, null)
                            ->createOne(['name' => '5 and up']);
                    }
                });
        });
    }
}
```
