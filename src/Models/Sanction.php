<?php

declare(strict_types=1);

namespace Kyrch\Prohibition\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Config;

/**
 * @property-read int $id
 * @property-read string $name
 */
class Sanction extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(Config::string('prohibition.table_names.sanction'));
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    public static function findOrCreate(string $name): static
    {
        return static::query()->firstOrCreate([
            'name' => $name,
        ]);
    }

    /**
     * @return BelongsToMany<Prohibition, $this>
     */
    public function prohibitions(): BelongsToMany
    {
        /** @var class-string<Prohibition> $prohibition */
        $prohibition = Config::string('prohibition.models.prohibition');

        return $this->belongsToMany(
            $prohibition,
            Config::string('prohibition.table_names.sanction_prohibition'),
        );
    }

    /**
     * @return MorphToMany<User, $this>
     */
    public function users(): MorphToMany
    {
        /** @var class-string<User> $user */
        $user = Config::string('prohibition.models.user');

        return $this->morphedByMany(
            $user,
            'model',
            Config::string('prohibition.table_names.model_sanctions'),
        );
    }
}
