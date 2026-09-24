# Listener Tests: Media Dimensions

Integration tests for a model media-attachment event: unsupported GIF leaves dimension properties null; supported JPEG exposes its width and height.

Use a migrated support model implementing the inspected media-library contract, its registered listener and the suite’s configured media disk. These assertions inspect the returned media object; they do not independently verify a refreshed database row.

```php
<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Tests\Support\Models\ExampleRecord;

it('leaves unsupported media without dimensions', function (): void {
    $model = ExampleRecord::query()->create();
    $media = $model->addMedia(UploadedFile::fake()->image('image.gif'))
        ->toMediaCollection();

    expect($media)
        ->getCustomProperty('height')->toBeNull()
        ->getCustomProperty('width')->toBeNull();
});

it('sets dimensions for supported media', function (): void {
    $model = ExampleRecord::query()->create();
    $media = $model->addMedia(UploadedFile::fake()->image('image.jpg', 200, 200))
        ->toMediaCollection();

    expect($media)
        ->getCustomProperty('height')->toBe(200)
        ->getCustomProperty('width')->toBe(200);
});
```
