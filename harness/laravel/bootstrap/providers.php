<?php

use App\Providers\AppServiceProvider;
use App\Providers\SqidsServiceProvider;
use App\Providers\TypeScriptTransformerServiceProvider;

return [
    AppServiceProvider::class,
    SqidsServiceProvider::class,
    TypeScriptTransformerServiceProvider::class,
];
