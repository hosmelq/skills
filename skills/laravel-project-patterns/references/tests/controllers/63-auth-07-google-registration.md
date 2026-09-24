# Authentication Tests: New Google Identity

POST JSON Google login: create a verified account from provider email, subject and profile claims. Assert token response, account fields, linked identity and issued token count.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\postJson;

use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Mockery\MockInterface;

it('creates and authenticates a new account', function (): void {
    $this->mock(Google_Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('setClientId')->once();
        $mock->shouldReceive('verifyIdToken')
            ->once()
            ->with('id-token')
            ->andReturn([
                'email' => 'casey@example.com',
                'family_name' => 'Reed',
                'given_name' => 'Casey',
                'sub' => 'identity-123',
            ]);
    });

    $response = postJson(route('api.auth.google.login'), [
        'id_token' => 'id-token',
    ]);

    $response->assertOk()
        ->assertJson(function (AssertableJson $json): void {
            $json->whereType('access_token', 'string')
                ->where('user.email', 'casey@example.com');
        });

    $user = User::query()->where('email', 'casey@example.com')->sole();

    expect($user)
        ->email->toBe('casey@example.com')
        ->email_verified_at->not->toBeNull()
        ->first_name->toBe('Casey')
        ->google_email->toBe('casey@example.com')
        ->google_id->toBe('identity-123')
        ->last_name->toBe('Reed')
        ->tokens->toHaveCount(1);
});
```
