# Authentication Tests: Google Account Conflicts

POST JSON Google login: reject a registered email with no linked identity and an email linked to another provider subject. Both use verified client claims and return the same 422 error.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\postJson;

use App\Models\User;
use Mockery\MockInterface;

it('rejects a registered email without a linked identity', function (): void {
    User::factory()->createOne([
        'email' => 'casey@example.com',
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

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'id_token' => 'Cannot sign in with Google for this account. Use email to continue.',
        ]);
});

it('rejects an email linked to another identity', function (): void {
    User::factory()->createOne([
        'email' => 'casey@example.com',
        'google_id' => 'other-identity',
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

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'id_token' => 'Cannot sign in with Google for this account. Use email to continue.',
        ]);
});
```
