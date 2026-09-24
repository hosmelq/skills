# Authentication Tests: Google Token Validation

Pest POST JSON Google login: required identity-token dataset and unverifiable token from a mocked Google client. Preserve client initialization, verification argument and 422 field error.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\postJson;

use Mockery\MockInterface;

it('validates fields', function (array $data, array $expected): void {
    $response = postJson(route('api.auth.google.login'), $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors($expected);
})->with([
    'required' => [
        'data' => [],
        'expected' => [
            'id_token' => 'The id token field is required.',
        ],
    ],
]);

it('rejects an unverifiable identity token', function (): void {
    $this->mock(Google_Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('setClientId')->once();
        $mock->shouldReceive('verifyIdToken')
            ->once()
            ->with('id-token')
            ->andReturnFalse();
    });

    $response = postJson(route('api.auth.google.login'), [
        'id_token' => 'id-token',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'id_token' => 'Unable to verify the supplied Google credentials.',
        ]);
});
```
