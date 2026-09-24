# Authentication Tests: Existing Google Identity

Pest POST JSON Google login: authenticate a linked identity and preserve the account email when the provider email changes. Keep subject matching, user ID/token assertions and exact database fields.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Mockery\MockInterface;

it('authenticates an existing identity', function (): void {
    $user = User::factory()->createOne([
        'email' => 'casey@example.com',
        'google_email' => 'casey@example.com',
        'google_id' => 'identity-123',
    ]);

    $this->mock(Google_Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('setClientId')->once();
        $mock->shouldReceive('verifyIdToken')
            ->once()
            ->with('id-token')
            ->andReturn([
                'email' => 'casey@example.com',
                'sub' => 'identity-123',
            ]);
    });

    $response = postJson(route('api.auth.google.login'), [
        'id_token' => 'id-token',
    ]);

    $response->assertOk()
        ->assertJson(function (AssertableJson $json) use ($user): void {
            $json->whereType('access_token', 'string')
                ->where('user.id', $user->sqid);
        });

    expect($user->tokens)->toHaveCount(1);
});

it('keeps the account email when the identity email changes', function (): void {
    $user = User::factory()->createOne([
        'email' => 'casey@example.com',
        'google_email' => 'casey@example.com',
        'google_id' => 'identity-123',
    ]);

    $this->mock(Google_Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('setClientId')->once();
        $mock->shouldReceive('verifyIdToken')
            ->once()
            ->with('id-token')
            ->andReturn([
                'sub' => 'identity-123',
                'email' => 'updated@example.com',
            ]);
    });

    $response = postJson(route('api.auth.google.login'), [
        'id_token' => 'id-token',
    ]);

    $response->assertOk();

    assertDatabaseHas(User::class, [
        'id' => $user->id,
        'email' => 'casey@example.com',
        'google_email' => 'updated@example.com',
        'google_id' => 'identity-123',
    ]);
});
```
