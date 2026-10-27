# Media: File Names and Paths

Store media library files under their media UUID, optionally behind a configured prefix, with UUID file names, so storage paths never expose model ids or user file names.

- Point `file_namer` and `path_generator` in `config/media-library.php` at these classes; the prefix comes from its `prefix` option.
- Test with `Storage::fake()` and a test model that has media in the integration suite.

```php
<?php

declare(strict_types=1);

namespace App\Support\Media;

use Override;
use Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer;

final class FileNamer extends DefaultFileNamer
{
    #[Override]
    public function originalFileName(string $fileName): string
    {
        return (string) str()->uuid();
    }
}
```

```php
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
```

```php
<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\Models\TestModel;

it('uses a uuid file name when media is added', function (): void {
    Storage::fake('public');

    $model = TestModel::query()->create();
    $media = $model->addMedia(UploadedFile::fake()->image('photo.png'))->toMediaCollection();
    $fileName = pathinfo($media->file_name, PATHINFO_FILENAME);
    $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);

    expect($fileName)->toBeUuid()->and($extension)->toBe('png');
});
```

```php
<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\Models\TestModel;

it('uses the media uuid as the base path', function (): void {
    Storage::fake('public');

    $model = TestModel::query()->create();
    $media = $model->addMedia(UploadedFile::fake()->image('image.jpg'))->toMediaCollection();

    expect($media->getPathRelativeToRoot())->toStartWith($media->uuid.'/');
});

it('includes the configured prefix before the media uuid path', function (): void {
    Storage::fake('public');

    config(['media-library.prefix' => 'uploads']);

    $model = TestModel::query()->create();
    $media = $model->addMedia(UploadedFile::fake()->image('image.jpg'))->toMediaCollection();

    expect($media->getPathRelativeToRoot())->toStartWith('uploads/'.$media->uuid.'/');
});
```
