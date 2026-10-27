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
