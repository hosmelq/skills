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
