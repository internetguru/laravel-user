<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Policies\AccountPolicy;
use Illuminate\Support\Facades\Gate;
use InternetGuru\LaravelUser\Enums\Role;
use Tests\TestCase;

class RoleListControllerTest extends TestCase
{
    public function test_guests_are_sent_to_the_login_page()
    {
        $this->get(route('role-list'))->assertRedirect(route('login'));
    }

    public function test_the_page_lists_every_role_with_the_abilities_it_gains()
    {
        $user = User::factory()->withRole(Role::CUSTOMER)->create();

        $response = $this->actingAs($user)->get(route('role-list'));

        $response->assertStatus(200);
        $response->assertViewIs('ig-common::layouts.base');
        $response->assertViewHas('view', 'role-list');
        $response->assertViewHas('props.rolePolicy', function (array $rolePolicy) {
            return array_keys($rolePolicy) === [
                Role::CUSTOMER->value,
                Role::OPERATOR->value,
                Role::AUDITOR->value,
                Role::MANAGER->value,
            ];
        });
        $response->assertSee(Role::MANAGER->translation());
        $response->assertSee(__('ig-user::role-list.UserPolicy@viewAny'));
    }

    public function test_an_application_can_close_the_page_by_narrowing_the_ability()
    {
        Gate::policy(User::class, AccountPolicy::class);

        $customer = User::factory()->withRole(Role::CUSTOMER)->create();
        $manager = User::factory()->withRole(Role::MANAGER)->create();

        $this->actingAs($customer)->get(route('role-list'))->assertForbidden();
        $this->actingAs($manager)->get(route('role-list'))->assertStatus(200);
    }
}
