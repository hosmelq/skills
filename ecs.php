<?php

declare(strict_types=1);

use HosmelQ\PhpCodingStandard\CodingStandard;
use PhpCsFixer\Fixer\ClassNotation\FinalClassFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withRules([FinalClassFixer::class])
    ->withSets([CodingStandard::ECS_LARAVEL_APP]);
