# Support: Media Paths

Use the media UUID as the base directory, optionally preceded by the configured prefix. Only an empty string omits the prefix; slashes are not normalized. Inherited methods append `/`, `/conversions/` or `/responsive-images/`. Register this class as `media-library.path_generator`.

```php
<?php

declare(strict_types=1);

namespace App\Support\Media;

use Illuminate\Support\Facades\Config;
use Override;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

class PathGenerator extends DefaultPathGenerator
{
    #[Override]
    protected function getBasePath(Media $media): string
    {
        $prefix = Config::string('media-library.prefix', '');

        if ($prefix !== '') {
            return $prefix.'/'.$media->uuid;
        }

        return $media->uuid;
    }
}
```
