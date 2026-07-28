<?php

declare(strict_types=1);

namespace Nuewire\Mail\Tests;

use PHPUnit\Framework\Attributes\Test;

final class PlatformNavigationRegistrationTest extends TestCase
{
    #[Test]
    public function it_registers_navigation_when_the_platform_registry_is_resolved_later(): void
    {
        $abstract = 'Nuewire\\Platform\\Navigation\\NavigationRegistry';

        $this->app->singleton($abstract, static fn (): FakeMailNavigationRegistry => new FakeMailNavigationRegistry());

        /** @var FakeMailNavigationRegistry $registry */
        $registry = $this->app->make($abstract);

        self::assertArrayHasKey('mail', $registry->pages);
        self::assertSame('nuewire::mail', $registry->pages['mail']['component']);
    }
}

final class FakeMailNavigationRegistry
{
    /** @var array<string, array<string, mixed>> */
    public array $pages = [];

    /** @param array<string, mixed> $page */
    public function register(string $id, array $page): self
    {
        $this->pages[$id] = $page;

        return $this;
    }
}
