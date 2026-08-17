<?php

namespace Tests\Feature;

use App\Models\User;
use InternetGuru\LaravelUser\Enums\Role;
use Tests\TestCase;

class SystemNoticeTest extends TestCase
{
    /**
     * Give the user a linked identity so the notice falls through to the install hint.
     */
    private function operator(): User
    {
        $user = User::factory()->withRole(Role::OPERATOR)->create();
        $user->socialites()->create([
            'provider' => (User::providers())::cases()[0],
            'provider_id' => 'provider-id',
            'name' => $user->name,
            'email' => $user->email,
        ]);

        return $user;
    }

    public function test_install_hint_links_part_of_the_message_to_the_add_to_homescreen_guide()
    {
        $this->actingAs($this->operator());

        $view = $this->blade('<x-ig-user::system-notice />');

        $view->assertSee('data-testid="use-app"', false);
        $view->assertSee('data-add-to-homescreen', false);
        $view->assertSee(__('ig-user::layouts.use-app'), false);
    }

    public function test_install_hint_is_linked_in_every_locale()
    {
        foreach (['cs', 'en'] as $locale) {
            $message = __('ig-user::layouts.use-app', locale: $locale);

            $this->assertStringContainsString('data-add-to-homescreen', $message, "Missing trigger in [$locale].");
            $this->assertMatchesRegularExpression('/\w<\/a>/u', $message, "Link wraps no text in [$locale].");
        }
    }

    public function test_install_hint_degrades_to_plain_text_without_the_guide_script()
    {
        $this->actingAs($this->operator());

        $view = $this->blade('<x-ig-user::system-notice />');

        // Falls back when the application does not bundle resources/js/add-to-homescreen.js
        $view->assertSee('window.AddToHomeScreenInstance', false);
        $view->assertSee('.use-app [data-add-to-homescreen]', false);
    }
}
