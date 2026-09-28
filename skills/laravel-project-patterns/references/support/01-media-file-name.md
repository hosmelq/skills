# Support: Media File Names

Override only the original basename with a UUID string; Media Library handles the extension. Conversion and responsive-image naming remain inherited. Register this class as `media-library.file_namer`.

```php
<?php

declare(strict_types=1);

namespace App\Support\Media;

use Illuminate\Support\Str;
use Override;
use Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer;

class FileNamer extends DefaultFileNamer
{
    #[Override]
    public function originalFileName(string $fileName): string
    {
        return (string) Str::uuid();
    }
}
```
