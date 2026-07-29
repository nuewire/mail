<?php

declare(strict_types=1);

namespace Nuewire\Mail\Tests;

use PHPUnit\Framework\Attributes\Test;

final class PlatformNavigationRegistrationTest extends TestCase
{
    #[Test]
    public function it_registers_email_under_settings_configuration(): void
    {
        $abstract = 'Nuewire\\Platform\\Navigation\\NavigationRegistry';
        $this->app->singleton($abstract, static fn (): FakeMailNavigationRegistry => new FakeMailNavigationRegistry());

        /** @var FakeMailNavigationRegistry $registry */
        $registry = $this->app->make($abstract);

        self::assertArrayHasKey('mail.settings', $registry->pages);
        self::assertSame('settings', $registry->pages['mail.settings']['area']);
        self::assertSame('configuration', $registry->pages['mail.settings']['group']);
        self::assertSame('email', $registry->pages['mail.settings']['slug']);
        self::assertSame('nuewire-mail', $registry->pages['mail.settings']['component']);
    }
}

final class FakeMailNavigationRegistry
{
    /** @var array<string, array<string, mixed>> */
    public array $pages = [];

    /** @param array<string, mixed> $area */
    public function registerArea(string $id, array $area = []): self
    {
        return $this;
    }

    /** @param array<string, mixed> $page */
    public function register(string $id, array $page): self
    {
        $this->pages[$id] = $page;

        return $this;
    }
}
