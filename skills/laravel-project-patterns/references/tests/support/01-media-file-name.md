# Support Tests: Media File Name

Adding a PNG through the configured file namer produces a UUID filename stem and preserves its extension. Use a migrated media-enabled support model and fake the configured disk (`public` here).

```php
<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\Models\ExampleRecord;

it('uses a uuid file name when media is added', function (): void {
    Storage::fake('public');

    $model = ExampleRecord::query()->create();

    $media = $model->addMedia(UploadedFile::fake()->image('photo.png'))
        ->toMediaCollection();

    $fileName = pathinfo($media->file_name, PATHINFO_FILENAME);
    $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);

    expect($fileName)->toBeUuid()
        ->and($extension)->toBe('png');
});
```
