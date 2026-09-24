# Store Tests: Api Json Resource

Pest POST store: JSON store: unauthenticated, unverified, invalid public tenant ID, domain error and created resource contract.

The invalid identifier case sends a real numeric ID to a public-ID route. Returned-model fixtures do not prove persistence or ownership.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\postJson;

use App\Actions\Cabinets\RequestEnrollment;
use App\Exceptions\CannotRequestEnrollment;
use App\Models\Enrollment;
use App\Models\Team;
use App\Models\User;

describe('store', function (): void {
    it('requires authentication', function (): void {
        $team = Team::factory()->createOne();

        $response = postJson(route('api.teams.enrollments.store', $team->public_id));

        $response->assertUnauthorized();
    });

    it('prevents storing for an unverified user', function (): void {
        $team = Team::factory()->createOne();
        $user = User::factory()->unverified()->createOne();

        signIn($user);

        $response = postJson(route('api.teams.enrollments.store', $team->public_id));

        $response->assertForbidden();
    });

    it('returns not found when the tenant public identifier is invalid', function (): void {
        $team = Team::factory()->createOne();

        signIn();

        $response = postJson(route('api.teams.enrollments.store', $team->id));

        $response->assertNotFound();
    });

    it('maps a disabled self-service rejection to validation', function (): void {
        $team = Team::factory()->createOne();
        $user = User::factory()->createOne();

        mock(RequestEnrollment::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Team $teamArgument, User $userArgument): bool => (
                $teamArgument->is($team)
                && $userArgument->is($user)
            ))
            ->andThrow(CannotRequestEnrollment::becauseCabinetsAreDisabled());

        signIn($user);

        $response = postJson(route('api.teams.enrollments.store', $team->public_id));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'cabinet_enrollment' => 'Cabinet self-service is not enabled for this team.',
            ]);
    });

    it('stores the record', function (): void {
        $team = Team::factory()->createOne();
        $user = User::factory()->createOne();
        $enrollment = Enrollment::factory()->createOne();

        mock(RequestEnrollment::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Team $teamArgument, User $userArgument): bool => (
                $teamArgument->is($team)
                && $userArgument->is($user)
            ))
            ->andReturn($enrollment);

        signIn($user);

        $response = postJson(route('api.teams.enrollments.store', $team->public_id));

        $response->assertCreated()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonPath('data.id', $enrollment->public_id)
            ->assertJsonPath('data.type', 'enrollments')
            ->assertJsonPath('data.attributes.status', 'pending');
    });
});
```
