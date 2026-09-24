# Store Tests: Api Json Resource

POST store: JSON store: unauthenticated, unverified, invalid public tenant ID, domain error and created resource contract.

The invalid identifier case sends a real numeric ID to a public-ID route. Returned-model fixtures do not prove persistence or ownership.

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

        $response = postJson(route('api.teams.enrollments.store', $team->sqid));

        $response->assertUnauthorized();
    });

    it('prevents storing for an unverified user', function (): void {
        $team = Team::factory()->createOne();
        $user = User::factory()->unverified()->createOne();

        login($user);

        $response = postJson(route('api.teams.enrollments.store', $team->sqid));

        $response->assertForbidden();
    });

    it('returns not found when the tenant public identifier is invalid', function (): void {
        $team = Team::factory()->createOne();

        login();

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

        login($user);

        $response = postJson(route('api.teams.enrollments.store', $team->sqid));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'cabinet_enrollment' => 'Cabinet self-service is not enabled for this team.',
            ]);
    });

    it('returns the requested resource', function (): void {
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

        login($user);

        $response = postJson(route('api.teams.enrollments.store', $team->sqid));

        $response->assertCreated()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonPath('data.id', $enrollment->sqid)
            ->assertJsonPath('data.type', 'enrollments')
            ->assertJsonPath('data.attributes.status', 'pending');
    });
});
```
