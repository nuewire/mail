<?php

declare(strict_types=1);

namespace Nuewire\Mail\Tests;

use Livewire\Livewire;

final class ComponentRegistrationTest extends TestCase
{
    public function test_the_settings_component_is_registered(): void
    {
        Livewire::test('nuewire::mail')
            ->assertSee('Pengaturan Email');
    }
}
