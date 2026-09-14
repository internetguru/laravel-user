<?php

namespace Tests\Feature;

use App\Models\User;
use InternetGuru\LaravelUser\Enums\Role;
use Tests\TestCase;

class RoleLabelTest extends TestCase
{
    public function test_a_role_renders_as_a_label_carrying_its_own_icon()
    {
        $html = Role::ADMIN->toLabelHtml();

        $this->assertStringContainsString('class="ig-label ig-label-icon"', $html);
        $this->assertStringContainsString('--ig-label-dot: var(--bs-danger)', $html);
        $this->assertStringContainsString('<i class="fa-solid fa-fw fa-user-gear" aria-hidden="true"></i>', $html);
        $this->assertStringContainsString(Role::ADMIN->translation(), $html);
    }

    public function test_every_role_is_coloured_by_how_far_it_reaches()
    {
        $variants = [];

        foreach (Role::cases() as $role) {
            $this->assertContains($role->variant(), ['secondary', 'primary', 'info', 'warning', 'danger']);
            $variants[] = $role->variant();
        }

        $this->assertSame($variants, array_unique($variants), 'Roles of different reach share a colour.');
    }

    public function test_the_label_is_the_translation()
    {
        foreach (Role::cases() as $role) {
            $this->assertSame($role->translation(), $role->label());
        }
    }

    public function test_a_roles_enum_saying_nothing_about_its_look_is_left_as_plain_text()
    {
        $this->assertSame('operator', formatUserRoleLabel(new class
        {
            public function translation(): string
            {
                return 'operator';
            }
        }));
    }

    public function test_the_user_list_shows_the_role_as_a_label_and_exports_it_as_text()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertSee('ig-label ig-label-icon', false);
        $response->assertSee('data-raw="' . Role::ADMIN->translation() . '"', false);
    }
}
