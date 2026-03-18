<?php

declare(strict_types=1);

namespace Kyrch\Prohibition\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Config;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read Collection<int, Sanction> $sanctions
 */
class Prohibition extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(Config::string('prohibition.table_names.prohibition'));
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
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    #[Scope]
    protected function expired(Builder $query): Builder
    {
        $pivotTable = Config::string('prohibition.table_names.model_prohibitions');

        return $query
            ->join($pivotTable, $this->getQualifiedKeyName(), '=', $this->sanctions()->getQualifiedParentKeyName())
            ->where("$pivotTable.expires_at", '<', now());
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    #[Scope]
    protected function notExpired(Builder $query): Builder
    {
        $pivotTable = Config::string('prohibition.table_names.model_prohibitions');

        return $query->where("$pivotTable.expires_at", '>', now())
            ->join($pivotTable, $this->getQualifiedKeyName(), '=', $this->sanctions()->getQualifiedParentKeyName())
            ->orWhereNull("$pivotTable.expires_at");
    }

    /**
     * @return BelongsToMany<Sanction, $this>
     */
    public function sanctions(): BelongsToMany
    {
        /** @var class-string<Sanction> $sanction */
        $sanction = Config::string('prohibition.models.sanction');

        return $this->belongsToMany(
            $sanction,
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
            Config::string('prohibition.table_names.model_prohibitions'),
        );
    }
}
