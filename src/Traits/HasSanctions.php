<?php

declare(strict_types=1);

namespace Kyrch\Prohibition\Traits;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Kyrch\Prohibition\Events\ModelSanctionTriggered;
use Kyrch\Prohibition\Exceptions\SanctionDoesNotExist;
use Kyrch\Prohibition\Models\Sanction;
use TypeError;

/**
 * @property Collection<int, Sanction> $sanctions
 */
trait HasSanctions
{
    use HasProhibitions;

    public function sanctions(): MorphToMany
    {
        return $this->morphToMany(
            config('prohibition.models.sanction'),
            'model',
            config('prohibition.table_names.model_sanctions'),
            'model_id',
        )
            ->using(config('prohibition.models.model_sanction'))
            ->withPivot(['expires_at', 'moderator_type', 'moderator_id', 'reason'])
            ->withTimestamps();
    }

    public function applySanction(
        Sanction|string $sanction,
        ?DateTimeInterface $expiresAt = null,
        ?string $reason = null,
        ?Model $moderator = null,
    ): void {
        if (is_string($sanctionName = $sanction)) {
            $sanction = config('prohibition.models.sanction')::query()->firstWhere('name', $sanctionName);

            if ($sanction === null) {
                throw SanctionDoesNotExist::name($sanctionName);
            }
        }

        $this->sanctions()->attach($sanction, [
            'moderator_type' => $moderator instanceof Model ? Relation::getMorphAlias($moderator::class) : null,
            'moderator_id' => $moderator instanceof Model ? $moderator->getKey() : null,
            'expires_at' => $expiresAt,
            'reason' => $reason,
        ]);

        if (config('prohibition.events_enabled')) {
            event(new ModelSanctionTriggered($this->getModel(), $sanction, $expiresAt, $reason, $moderator));
        }
    }

    /**
     * @param  string|array|Collection|null  $sanctions
     */
    public function hasSanctionNotExpired($sanctions): bool
    {
        if ($sanctions === null) {
            return false;
        }

        $this->loadMissing('sanctions');

        if (is_string($sanctions)) {
            return $this->checkExpiration($this->sanctions->firstWhere('name', $sanctions));
        }

        if (is_array($sanctions)) {
            return array_any($sanctions, fn ($sanction) => $this->hasSanctionNotExpired($sanction));
        }

        if ($sanctions instanceof Collection) {
            return $sanctions->intersect($this->sanctions)->filter(fn (Sanction $sanction) => $this->checkExpiration($sanction))->isNotEmpty();
        }

        throw new TypeError("Unexpected type for $sanctions parameter: ".gettype($sanctions));
    }
}
