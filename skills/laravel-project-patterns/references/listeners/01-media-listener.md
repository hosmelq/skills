# Listeners: Media Listener

Store the dimensions of uploaded images as media custom properties when the media library adds a file, skipping other mime types and reporting failures without breaking the upload.

- Catch `Throwable` and `report()` it: a listener on an upload must never fail the request.
- Test with fake uploads on a test model in the integration suite.

```php
<?php

declare(strict_types=1);

namespace App\Listeners;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;
use Throwable;

final class SetMediaDimensions
{
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        try {
            if (! in_array(
                str($event->media->mime_type)->lower()->toString(),
                ['image/jpeg', 'image/jpg', 'image/png'],
                true,
            )) {
                return;
            }

            $manager = new ImageManager(new Driver());
            $image = $manager->decodeStream($event->media->stream());

            $event->media->setCustomProperty('height', $image->height());
            $event->media->setCustomProperty('width', $image->width());

            $event->media->save();
        } catch (Throwable $e) {
            report($e);
        }
    }
}
```

```php
<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Tests\Support\Models\TestModel;

it('only runs for supported mime types', function (): void {
    $model = TestModel::query()->create();
    $media = $model->addMedia(UploadedFile::fake()->image('image.gif'))->toMediaCollection();

    expect($media)->getCustomProperty('height')->toBeNull()->getCustomProperty('width')->toBeNull();
});

it('saves the height and width of the image', function (): void {
    $model = TestModel::query()->create();
    $media = $model->addMedia(UploadedFile::fake()->image('image.jpg', 200, 200))
        ->toMediaCollection();

    expect($media)->getCustomProperty('height')->toBe(200)->getCustomProperty('width')->toBe(200);
});
```
