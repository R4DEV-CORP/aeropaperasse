<?php

namespace Tests\Feature\Livewire\Notifications;

use Livewire\Livewire;
use Tests\TestCase;

class ToastStackTest extends TestCase
{
    public function test_mount_consumes_a_single_flash_payload(): void
    {
        session()->flash('toast', [
            'message' => 'Demande créée',
            'variant' => 'success',
            'title' => 'Succès',
        ]);

        Livewire::test('notifications.toast-stack')
            ->assertCount('toasts', 1)
            ->assertSet('toasts.0.message', 'Demande créée')
            ->assertSet('toasts.0.variant', 'success')
            ->assertSet('toasts.0.title', 'Succès');

        $this->assertNull(session('toast'));
    }

    public function test_mount_consumes_a_list_of_flash_payloads(): void
    {
        session()->flash('toast', [
            ['message' => 'Premier', 'variant' => 'success'],
            ['message' => 'Second', 'variant' => 'warning'],
        ]);

        Livewire::test('notifications.toast-stack')
            ->assertCount('toasts', 2)
            ->assertSet('toasts.0.message', 'Premier')
            ->assertSet('toasts.0.variant', 'success')
            ->assertSet('toasts.1.message', 'Second')
            ->assertSet('toasts.1.variant', 'warning');
    }

    public function test_on_toast_event_appends_a_toast(): void
    {
        Livewire::test('notifications.toast-stack')
            ->assertCount('toasts', 0)
            ->call('onToast', 'Hello', 'info', null, 5000)
            ->assertCount('toasts', 1)
            ->assertSet('toasts.0.message', 'Hello')
            ->assertSet('toasts.0.variant', 'info')
            ->assertSet('toasts.0.ttl', 5000);
    }

    public function test_unknown_variant_falls_back_to_info(): void
    {
        Livewire::test('notifications.toast-stack')
            ->call('onToast', 'Boom', 'pink', null, 6000)
            ->assertSet('toasts.0.variant', 'info');
    }

    public function test_dismiss_removes_only_the_targeted_toast(): void
    {
        $component = Livewire::test('notifications.toast-stack')
            ->call('onToast', 'A', 'info', null, 6000)
            ->call('onToast', 'B', 'success', null, 6000)
            ->call('onToast', 'C', 'danger', null, 6000)
            ->assertCount('toasts', 3);

        $targetId = $component->get('toasts')[1]['id'];

        $component
            ->call('dismiss', $targetId)
            ->assertCount('toasts', 2)
            ->assertSet('toasts.0.message', 'A')
            ->assertSet('toasts.1.message', 'C');
    }

    public function test_render_displays_message_and_title(): void
    {
        session()->flash('toast', [
            'message' => 'Action effectuée',
            'variant' => 'success',
            'title' => 'Bravo',
        ]);

        Livewire::test('notifications.toast-stack')
            ->assertSee('Action effectuée')
            ->assertSee('Bravo');
    }

    public function test_ttl_is_clamped_to_minimum(): void
    {
        Livewire::test('notifications.toast-stack')
            ->call('onToast', 'Quick', 'info', null, 100)
            ->assertSet('toasts.0.ttl', 1000);
    }
}
