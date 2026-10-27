<?php

declare(strict_types=1);

namespace App\Support\Media;

use Override;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

final class PathGenerator extends DefaultPathGenerator
{
    #[Override]
    protected function getBasePath(Media $media): string
    {
        $prefix = config()->string('media-library.prefix', '');

        if ($prefix !== '') {
            return $prefix.'/'.$media->uuid;
        }

        return $media->uuid;
    }
}
