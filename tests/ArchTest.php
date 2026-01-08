<?php

declare(strict_types=1);

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Facade;

arch()
    ->expect('src')
    ->toUseStrictTypes()
    ->not->toUse(['die', 'dd', 'dump', 'var_dump']);

arch()
    ->expect('src\Console\Commands')
    ->toExtend(Command::class);

arch()
    ->expect('src\Contracts')
    ->toBeInterfaces();

arch()
    ->expect('src\Facades')
    ->toBeClasses()
    ->toExtend(Facade::class);

arch()
    ->expect('src\Models')
    ->toBeClasses()
    ->toExtend(Model::class);

arch()
    ->expect('src\Pivots')
    ->toBeClasses()
    ->toExtend(Pivot::class);

arch()
    ->expect('src\Traits')
    ->toBeTraits();
