# Factories: Configure Expiration and Usage

Keep the unused default explicit. Expired and used states change their own timestamps independently; retain the declared `self` return type.

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OneTimePassword;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OneTimePassword>
 */
class OneTimePasswordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => fake()->safeEmail(),
            'code' => fake()->numerify('######'),
            'expires_at' => now()->addMinutes(10),
            'used_at' => null,
        ];
    }

    public function expired(): self
    {
        return $this->state(['expires_at' => now()->subMinute()]);
    }

    public function used(): self
    {
        return $this->state(['used_at' => now()]);
    }
}
```
