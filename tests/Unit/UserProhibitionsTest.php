<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Kyrch\Prohibition\Events\ModelProhibitionTriggered;
use Kyrch\Prohibition\Exceptions\ProhibitionDoesNotExist;
use Kyrch\Prohibition\Models\Prohibition;
use Kyrch\Prohibition\Models\Sanction;

beforeEach(function (): void {
    Prohibition::query()->create(['name' => 'prohibition']);
    Sanction::query()->create(['name' => 'sanction']);
});

test('applies prohibition to user', function (): void {
    $this->testUser->prohibit('prohibition', Date::now()->addDays(fake()->numberBetween(1, 10)));

    expect($this->testUser->prohibitions->isNotEmpty())->toBeTrue();
});

test('user is prohibited from', function (): void {
    $this->testUser->prohibit('prohibition', Date::now()->addDays(fake()->numberBetween(1, 10)));

    expect($this->testUser->isProhibitedFrom('prohibition'))->toBeTrue();
});

test('user is not prohibited from', function (): void {
    expect($this->testUser->isProhibitedFrom('prohibition'))->toBeFalse();
});

test('prohibition expired', function (): void {
    $this->testUser->prohibit('prohibition', Date::now()->subDays(fake()->numberBetween(1, 10)));

    expect($this->testUser->isProhibitedFrom('prohibition'))->toBeFalse();
});

test('user is directly prohibited from', function (): void {
    $this->testUser->prohibit('prohibition', Date::now()->addDays(fake()->numberBetween(1, 10)));

    expect($this->testUser->isDirectlyProhibitedFrom('prohibition'))->toBeTrue();
});

test('user is not directly prohibited when sanctioned', function (): void {
    $this->testUser->applySanction('sanction', Date::now()->addDays(fake()->numberBetween(1, 10)));

    expect($this->testUser->isDirectlyProhibitedFrom('prohibition'))->toBeFalse();
});

test('expires_at null means permanent prohibition', function (): void {
    $this->testUser->prohibit('prohibition', null);

    expect($this->testUser->isProhibitedFrom('prohibition'))->toBeTrue();
});

test('expired scope', function (): void {
    $this->testUser->prohibit('prohibition', Date::now()->addDays(fake()->numberBetween(1, 10)));

    expect(Prohibition::query()->expired()->get()->isEmpty())->toBeTrue();
});

test('notExpired scope', function (): void {
    $this->testUser->prohibit('prohibition', Date::now()->addDays(fake()->numberBetween(1, 10)));

    expect(Prohibition::query()->notExpired()->get()->isEmpty())->toBeFalse();
});

test('notExpired scope when expires_at is null', function (): void {
    $this->testUser->prohibit('prohibition', null);

    expect(Prohibition::query()->notExpired()->get()->isEmpty())->toBeFalse();
});

test('dispatches model prohibition triggered', function (): void {
    $this->testUser->prohibit('prohibition');

    Event::assertDispatched(ModelProhibitionTriggered::class);
});

test('throws prohibition does not exist', function (): void {
    $this->testUser->prohibit('non-existing-prohibition');
})->throws(ProhibitionDoesNotExist::class);
