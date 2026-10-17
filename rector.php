<?php

declare(strict_types=1);

use HosmelQ\PhpCodingStandard\CodingStandard;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withSets([CodingStandard::RECTOR_LARAVEL_APP, CodingStandard::RECTOR_PEST]);
