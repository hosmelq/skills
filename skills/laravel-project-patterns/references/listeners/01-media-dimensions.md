# Listeners: Media Dimensions

Handle the package’s media-added event synchronously through the project’s listener discovery or registration. Lowercase the MIME type and accept only JPEG/JPG/PNG; other values return before reading the stream and leave existing properties unchanged. Decode the stream with Intervention Image 4 and GD, set height then width, preserve other custom properties and save the media model.

Report processing failures through Sentry. The catch does not restore in-memory properties after a failed save, and an exception from the reporter itself can escape. The listener defines no queued retry behavior.

```php
<?php

declare(strict_types=1);

namespace App\Listeners;

use function Sentry\captureException;

use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;
use Throwable;

class SetMediaDimensions
{
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        try {
            if (! in_array(
                Str::lower($event->media->mime_type),
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
            captureException($e);
        }
    }
}
```

APIs: [Laravel listeners](https://laravel.com/docs/13.x/events), [Media Library events](https://spatie.be/docs/laravel-medialibrary/v11/advanced-usage/consuming-events), [Intervention stream decoding](https://image.intervention.io/v4/basics/instantiation). Use the separate [listener tests](../tests/listeners/01-media-dimensions.md) for media-attachment assertions.
