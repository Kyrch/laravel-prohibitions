<?php

declare(strict_types=1);

namespace Kyrch\Prohibition\Traits;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Config;
use Kyrch\Prohibition\Events\ModelProhibitionTriggered;
use Kyrch\Prohibition\Exceptions\ProhibitionDoesNotExist;
use Kyrch\Prohibition\Models\Prohibition;
use Kyrch\Prohibition\Models\Sanction;

/**
 * @property Collection<int, Prohibition> $prohibitions
 */
trait HasProhibitions
{
    public function prohibitions(): MorphToMany
    {
        return $this->morphToMany(
            Config::string('prohibition.models.prohibition'),
            'model',
            Config::string('prohibition.table_names.model_prohibitions'),
            'model_id',
        )
            ->using(Config::string('prohibition.models.model_prohibition'))
            ->withPivot(['expires_at', 'moderator_type', 'moderator_id', 'reason'])
            ->withTimestamps();
    }

    public function prohibit(
        Prohibition|string $prohibition,
        ?DateTimeInterface $expiresAt = null,
        ?string $reason = null,
        ?Model $moderator = null,
    ): void {
        if (is_string($prohibitionName = $prohibition)) {
            $prohibition = Config::string('prohibition.models.prohibition')::query()->firstWhere('name', $prohibitionName);

            if ($prohibition === null) {
                throw ProhibitionDoesNotExist::name($prohibitionName);
            }
        }

        $this->prohibitions()->attach($prohibition, [
            'moderator_type' => $moderator instanceof Model ? Relation::getMorphAlias($moderator::class) : null,
            'moderator_id' => $moderator instanceof Model ? $moderator->getKey() : null,
            'expires_at' => $expiresAt,
            'reason' => $reason,
        ]);

        if (Config::boolean('prohibition.events_enabled')) {
            event(new ModelProhibitionTriggered($this->getModel(), $prohibition, $expiresAt, $reason, $moderator));
        }
    }

    public function isProhibitedFrom(string $ability): bool
    {
        return $this->isDirectlyProhibitedFrom($ability) || $this->isProhibitedViaSanction($ability);
    }

    public function isDirectlyProhibitedFrom(string $ability): bool
    {
        $prohibition = $this->loadMissing('prohibitions')->prohibitions
            ->firstWhere('name', $ability);

        return $prohibition
            ? $this->checkExpiration($prohibition)
            : false;
    }

    public function isProhibitedViaSanction(string $ability): bool
    {
        return $this->hasSanctionNotExpired(
            $this->loadMissing('sanctions')->sanctions->filter(fn (Sanction $sanction) => $sanction->prohibitions->firstWhere('name', $ability))
        );
    }

    protected function checkExpiration(Prohibition|Sanction|null $prohibition): bool
    {
        if ($prohibition === null) {
            return false;
        }

        return $prohibition->pivot->expires_at === null || $prohibition->pivot->expires_at->isFuture();
    }
}
