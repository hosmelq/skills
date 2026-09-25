# Support Tests: Media Path

The configured path generator puts the media UUID first, or after a configured prefix. These assertions check the relative-path prefix. Use a migrated media-enabled support model and fake the configured disk (`public` here).

```php
<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Support\Models\ExampleRecord;

it('uses the media uuid as the base path', function (): void {
    Storage::fake('public');
    config(['media-library.prefix' => '']);

    $model = ExampleRecord::query()->create();

    $media = $model->addMedia(UploadedFile::fake()->image('image.jpg'))
        ->toMediaCollection();

    $path = $media->getPathRelativeToRoot();

    expect(Str::startsWith($path, $media->uuid.'/'))->toBeTrue();
});

it('includes the configured prefix before the media uuid path', function (): void {
    Storage::fake('public');
    config(['media-library.prefix' => 'uploads']);

    $model = ExampleRecord::query()->create();

    $media = $model->addMedia(UploadedFile::fake()->image('image.jpg'))
        ->toMediaCollection();

    $path = $media->getPathRelativeToRoot();

    expect(Str::startsWith($path, 'uploads/'.$media->uuid.'/'))->toBeTrue();
});
```
