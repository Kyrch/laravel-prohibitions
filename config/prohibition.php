<?php

declare(strict_types=1);

use Illuminate\Foundation\Auth\User;
use Kyrch\Prohibition\Models\Prohibition;
use Kyrch\Prohibition\Models\Sanction;
use Kyrch\Prohibition\Pivots\ModelProhibition;
use Kyrch\Prohibition\Pivots\ModelSanction;

return [
    /*
    |--------------------------------------------------------------------------
    | Events Enabled
    |--------------------------------------------------------------------------
    |
    | Enables or disables all package-related events.
    |
    | When set to true, the package will dispatch events during actions such as:
    | - ModelProhibitionTriggered
    | - ModelSanctionTriggered
    |
    | Disable this if you want full manual control or to avoid side effects
    | in specific environments (e.g. testing).
    |
    */
    'events_enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Model Classes
    |--------------------------------------------------------------------------
    |
    | These values define the Eloquent model classes used internally
    | by the package.
    |
    | All models must extend its respective model.
    | You may override any of these to use your own custom implementations.
    |
    */
    'models' => [
        'user' => User::class,
        'prohibition' => Prohibition::class,
        'sanction' => Sanction::class,
        'model_prohibition' => ModelProhibition::class,
        'model_sanction' => ModelSanction::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Defines the database table names used by the package.
    |
    | You may change these if your project uses a different naming
    | convention or if table names conflict with existing tables.
    |
    */
    'table_names' => [
        'prohibition' => 'prohibitions',
        'sanction' => 'sanctions',
        'sanction_prohibition' => 'sanction_prohibition',
        'model_sanctions' => 'model_sanctions',
        'model_prohibitions' => 'model_prohibitions',
    ],
];
