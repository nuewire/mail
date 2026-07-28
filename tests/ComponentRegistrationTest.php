<?php

declare(strict_types=1);

namespace Btekno\Mail\Tests;

use Livewire\Livewire;

final class ComponentRegistrationTest extends TestCase
{
    public function test_the_settings_component_is_registered(): void
    {
        Livewire::test('btekno::mail')
            ->assertSee('Pengaturan Email');
    }
}
