# Database: Seeders

Seed a local development database with a known login and realistic tenant data through factories and the same actions the application uses, called from the default database seeder.

- `DatabaseSeeder` only calls other seeders; local development data lives in its own seeder.
- Inject the actions the application runs when it sets up a tenant into `run()` and call them for every seeded tenant.
- Create a fixed-email user that owns and belongs to the seeded tenants, and switch it into each tenant before assigning tenant roles.
- Build children with `for()` on the seeded tenant.

```php
<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
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

use App\Actions\EnsureClinicRoles;
use App\Actions\EnsureInitialVisitStatus;
use App\Enums\MembershipRole;
use App\Models\Client;
use App\Models\Clinic;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

final class LocalSeeder extends Seeder
{
    public function run(
        EnsureClinicRoles $ensureClinicRoles,
        EnsureInitialVisitStatus $ensureInitialVisitStatus,
    ): void {
        $user = User::factory()->createOne(['email' => 'test@example.com']);
        $clinics = Clinic::factory()
            ->count(2)
            ->for($user, 'owner')
            ->sequence(['name' => 'Local'], [])
            ->create();

        $user->clinics()->sync($clinics);

        $clinics->each(function (Clinic $clinic) use (
            $ensureClinicRoles,
            $ensureInitialVisitStatus,
            $user
        ): void {
            $ensureClinicRoles->handle($clinic);
            $ensureInitialVisitStatus->handle($clinic);

            $user->switchClinic($clinic);
            $user->assignRole(MembershipRole::Owner);

            Client::factory()->for($clinic)->withDefaultAddress()->createOne();
            Room::factory()->for($clinic)->count(4)->create();
        });
    }
}
```
