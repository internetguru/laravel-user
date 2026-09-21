<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use InternetGuru\LaravelUser\Enums\Role;
use Tests\TestCase;

class UserMenuTest extends TestCase
{
    public function test_the_first_line_names_the_user_and_the_role_as_a_label()
    {
        $html = $this->menuFor(User::factory()->create([
            'name' => 'Anna Kurtz',
            'role' => Role::MANAGER,
        ]));

        $this->assertStringContainsString('Anna Kurtz', $html);
        $this->assertStringContainsString('ig-label ig-label-slim ig-label-icon', $html);
        $this->assertStringContainsString('fa-user-tie', $html);
        $this->assertStringContainsString(Role::MANAGER->translation(), $html);
    }

    /**
     * Bootstrap closes a dropdown on any click inside it by default, which takes
     * the menu away the moment a name or a date in it is selected.
     */
    public function test_the_menu_stays_open_when_something_inside_it_is_clicked()
    {
        $html = $this->menuFor(User::factory()->create());

        $this->assertStringContainsString('data-bs-auto-close="outside"', $html);
    }

    protected function menuFor(User $user): string
    {
        $this->actingAs($user);

        return Blade::render('<x-ig-user::user-menu />');
    }
}
