<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InternetGuru\LaravelUser\Enums\Provider;
use InternetGuru\LaravelUser\Enums\Role;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_displays_users()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $users = User::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('ig-common::layouts.base');
        $response->assertViewHas('view', 'users.index');
        $response->assertViewHas('props.users', function ($viewUsers) use ($users, $admin) {
            return $viewUsers->contains($admin) && $viewUsers->intersect($users)->count() === 3;
        });
    }

    public function test_show_displays_user()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('users.show', $user));

        $response->assertStatus(200);
        $response->assertViewIs('ig-common::layouts.base');
        $response->assertViewHas('view', 'users.show');
        $response->assertViewHas('props.user', $user);
    }

    public function test_show_displays_no_identities_message_when_user_has_no_socialites()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('users.show', $user));

        $response->assertStatus(200);
        $response->assertSee(__('ig-user::user.no-identities'));
    }

    public function test_update_name_successfully()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($admin)->post(route('users.update', $user), [
            'name' => 'New Name',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('ig-user::user.update.name'));
        $this->assertEquals('New Name', $user->fresh()->name);
    }

    public function test_update_email_successfully()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create(['email' => 'old@gmail.com']);

        $response = $this->actingAs($admin)->post(route('users.update', $user), [
            'email' => 'new@gmail.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('ig-user::user.update.email'));
        $this->assertEquals('new@gmail.com', $user->fresh()->email);
    }

    public function test_update_phone_successfully()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create(['phone' => null]);

        $response = $this->actingAs($admin)->post(route('users.update', $user), [
            'phone' => '+420123456789',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('ig-user::user.update.phone'));
        $this->assertEquals('+420123456789', $user->fresh()->phone);
    }

    public function test_update_phone_to_null()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create(['phone' => '+420123456789']);

        $response = $this->actingAs($admin)->post(route('users.update', $user), [
            'phone' => '',
        ]);

        $response->assertRedirect();
        $this->assertNull($user->fresh()->phone);
    }

    public function test_update_role_successfully()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create(['role' => Role::CUSTOMER]);

        $response = $this->actingAs($admin)->post(route('users.update', $user), [
            'role' => Role::OPERATOR->value,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('ig-user::user.update.role'));
        $this->assertEquals(Role::OPERATOR, $user->fresh()->role);
    }

    public function test_update_name_validation_fails()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->from('/users/' . $user->id)->post(route('users.update', $user), [
            'name' => '',
        ]);

        $response->assertRedirect('/users/' . $user->id);
        $response->assertSessionHasErrors('name');
    }

    public function test_update_email_validation_fails()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->from('/users/' . $user->id)->post(route('users.update', $user), [
            'email' => 'invalid-email',
        ]);

        $response->assertRedirect('/users/' . $user->id);
        $response->assertSessionHasErrors('email');
    }

    public function test_update_email_to_existing_email_fails()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create(['email' => 'unique@gmail.com']);
        User::factory()->create(['email' => 'existing@gmail.com']);

        $response = $this->actingAs($admin)->from('/users/' . $user->id)->post(route('users.update', $user), [
            'email' => 'existing@gmail.com',
        ]);

        $response->assertRedirect('/users/' . $user->id);
        $response->assertSessionHasErrors('email');
    }

    public function test_update_role_validation_fails()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->from('/users/' . $user->id)->post(route('users.update', $user), [
            'role' => 'invalid-role',
        ]);

        $response->assertRedirect('/users/' . $user->id);
        $response->assertSessionHasErrors('role');
    }

    public function test_update_throws_bad_request_for_unexpected_request()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('users.update', $user), [
            'unexpected' => 'data',
        ]);

        $response->assertStatus(400);
    }

    public function test_unauthorized_user_cannot_update_another_user()
    {
        $operator = User::factory()->create(['role' => Role::OPERATOR]);
        $user = User::factory()->create();

        $response = $this->actingAs($operator)->post(route('users.update', $user), [
            'name' => 'New Name',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_update_own_profile()
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->post(route('users.update', $user), [
            'name' => 'New Name',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('ig-user::user.update.name'));
        $this->assertEquals('New Name', $user->fresh()->name);
    }

    public function test_basic_user_cannot_update_others_profile()
    {
        $user = User::factory()->withRole(Role::CUSTOMER)->create();
        $otherUser = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->post(route('users.update', $otherUser), [
            'name' => 'New Name',
        ]);

        $response->assertStatus(403);
    }

    public function test_manager_can_update_roles_within_limits()
    {
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $operator = User::factory()->create(['role' => Role::OPERATOR]);

        $response = $this->actingAs($manager)->post(route('users.update', $operator), [
            'role' => Role::CUSTOMER->value,
        ]);

        $response->assertRedirect();
        $this->assertEquals(Role::CUSTOMER, $operator->fresh()->role);

        $response = $this->actingAs($manager)->post(route('users.update', $operator), [
            'role' => Role::ADMIN->value,
        ]);

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_user_routes()
    {
        $user = User::factory()->create();

        $this->get(route('users.index'))->assertRedirect('/login');
        $this->get(route('users.show', $user))->assertRedirect('/login');
        $this->post(route('users.update', $user), ['name' => 'New Name'])->assertRedirect('/login');
        $this->post(route('users.merge', $user), ['merge_user_id' => 1])->assertRedirect('/login');
        $this->post(route('users.unmerge', $user), ['merge_user_id' => 1])->assertRedirect('/login');
    }

    public function test_merge_endpoints_are_disabled_by_default()
    {
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $user = User::factory()->create(['role' => Role::CUSTOMER]);
        $other = User::factory()->create(['role' => Role::CUSTOMER]);

        $this->actingAs($manager)->post(route('users.merge', $user), [
            'merge_user_id' => $other->id,
        ])->assertStatus(403);

        $this->actingAs($manager)->post(route('users.unmerge', $user), [
            'merge_user_id' => $other->id,
        ])->assertStatus(403);

        $this->assertDatabaseCount('user_merges', 0);
    }

    public function test_manager_can_merge_and_unmerge_accounts()
    {
        config(['ig-user.merge' => true]);

        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $user = User::factory()->create(['role' => Role::CUSTOMER]);
        $other = User::factory()->create(['role' => Role::CUSTOMER]);

        $response = $this->actingAs($manager)->post(route('users.merge', $user), [
            'merge_user_id' => $other->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('ig-user::user.merge.added', ['name' => $other->name]));
        $this->assertTrue($user->fresh()->isMergedWith($other));
        $this->assertDatabaseHas('user_merges', [
            'user_id' => min($user->id, $other->id),
            'merged_user_id' => max($user->id, $other->id),
        ]);

        $response = $this->actingAs($manager)->post(route('users.unmerge', $user), [
            'merge_user_id' => $other->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('ig-user::user.merge.removed', ['name' => $other->name]));
        $this->assertFalse($user->fresh()->isMergedWith($other));
        $this->assertDatabaseCount('user_merges', 0);
    }

    public function test_merge_writes_history_for_both_accounts()
    {
        config(['ig-user.merge' => true]);

        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $user = User::factory()->create(['role' => Role::CUSTOMER]);
        $other = User::factory()->create(['role' => Role::CUSTOMER]);

        $this->actingAs($manager)->post(route('users.merge', $user), [
            'merge_user_id' => $other->id,
        ]);

        foreach ([$user, $other] as $account) {
            $history = $account->fresh()->associationHistories()->where('column_name', 'merged')->get();

            $this->assertCount(1, $history);
            $this->assertSame('', $history->first()->column_prev_value);
            $this->assertSame($manager->id, $history->first()->author_id);
        }
    }

    public function test_merge_is_idempotent_and_does_not_error()
    {
        config(['ig-user.merge' => true]);

        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $user = User::factory()->create(['role' => Role::CUSTOMER]);
        $other = User::factory()->create(['role' => Role::CUSTOMER]);

        $payload = ['merge_user_id' => $other->id];

        $this->actingAs($manager)->post(route('users.merge', $user), $payload);
        $response = $this->actingAs($manager)->post(route('users.merge', $user), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('ig-user::user.merge.already'));
        $this->assertDatabaseCount('user_merges', 1);
    }

    public function test_unmerging_unrelated_accounts_does_not_error()
    {
        config(['ig-user.merge' => true]);

        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $user = User::factory()->create(['role' => Role::CUSTOMER]);
        $other = User::factory()->create(['role' => Role::CUSTOMER]);

        $response = $this->actingAs($manager)->post(route('users.unmerge', $user), [
            'merge_user_id' => $other->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('ig-user::user.merge.not-merged'));
    }

    public function test_operator_cannot_merge_accounts()
    {
        config(['ig-user.merge' => true]);

        $operator = User::factory()->create(['role' => Role::OPERATOR]);
        $user = User::factory()->create(['role' => Role::CUSTOMER]);
        $other = User::factory()->create(['role' => Role::CUSTOMER]);

        $this->actingAs($operator)->post(route('users.merge', $user), [
            'merge_user_id' => $other->id,
        ])->assertStatus(403);

        $this->assertDatabaseCount('user_merges', 0);
    }

    public function test_manager_cannot_merge_an_admin_account()
    {
        config(['ig-user.merge' => true]);

        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $admin = User::factory()->create(['role' => Role::ADMIN]);

        // Neither by naming the admin as the route subject nor as the merge target
        $this->actingAs($manager)->post(route('users.merge', $manager), [
            'merge_user_id' => $admin->id,
        ])->assertStatus(403);

        $this->actingAs($manager)->post(route('users.merge', $admin), [
            'merge_user_id' => $manager->id,
        ])->assertStatus(403);

        $this->assertDatabaseCount('user_merges', 0);
    }

    public function test_merge_validates_the_target_account()
    {
        config(['ig-user.merge' => true]);

        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $user = User::factory()->create(['role' => Role::CUSTOMER]);

        $this->actingAs($manager)
            ->post(route('users.merge', $user), [])
            ->assertSessionHasErrors('merge_user_id');

        $this->actingAs($manager)
            ->post(route('users.merge', $user), ['merge_user_id' => 999999])
            ->assertSessionHasErrors('merge_user_id');

        // Merging an account with itself is meaningless
        $this->actingAs($manager)
            ->post(route('users.merge', $user), ['merge_user_id' => $user->id])
            ->assertStatus(400);

        $this->assertDatabaseCount('user_merges', 0);
    }

    public function test_merging_does_not_change_roles()
    {
        config(['ig-user.merge' => true]);

        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $customer = User::factory()->create(['role' => Role::CUSTOMER]);
        $operator = User::factory()->create(['role' => Role::OPERATOR]);

        $this->actingAs($manager)->post(route('users.merge', $customer), [
            'merge_user_id' => $operator->id,
        ]);

        $this->assertEquals(Role::CUSTOMER, $customer->fresh()->role);
        $this->assertEquals(Role::OPERATOR, $operator->fresh()->role);
    }

    public function test_show_lists_merged_accounts_for_a_manager()
    {
        config(['ig-user.merge' => true]);

        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $user = User::factory()->create(['role' => Role::CUSTOMER]);
        $other = User::factory()->create(['role' => Role::CUSTOMER, 'name' => 'Merged Person']);

        $user->mergeWith($other);

        $response = $this->actingAs($manager)->get(route('users.show', $user));

        $response->assertStatus(200);
        $response->assertSee(__('ig-user::user.merges'));
        $response->assertSee('Merged Person');
        $response->assertSee($other->email);
    }

    public function test_show_renders_the_seznam_brand_mark_instead_of_a_font_awesome_fallback()
    {
        $user = User::factory()->create();
        $user->socialites()->create([
            'provider' => Provider::SEZNAM,
            'provider_id' => 'seznam-1',
            'name' => 'Seznam User',
            'email' => 'user@seznam.cz',
        ]);

        $response = $this->actingAs($user)->get(route('users.show', $user));

        $response->assertStatus(200);
        $response->assertSee('<svg', false);
        $response->assertSee('fill="#C00"', false);
        $response->assertDontSee(config('services.seznam.icon'), false);
    }

    public function test_show_falls_back_to_the_configured_icon_for_providers_without_a_brand_mark()
    {
        $user = User::factory()->create();
        $user->socialites()->create([
            'provider' => Provider::GOOGLE,
            'provider_id' => 'google-1',
            'name' => 'Google User',
            'email' => 'user@gmail.com',
        ]);

        $response = $this->actingAs($user)->get(route('users.show', $user));

        $response->assertStatus(200);
        $response->assertSee(config('services.google.icon'), false);
    }

    public function test_show_describes_the_benefit_of_adding_an_identity()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('users.show', $user));

        $response->assertStatus(200);
        $response->assertSee(__('ig-user::user.authentication-desc'));
    }

    public function test_show_lists_a_short_candidate_list_without_a_search_box()
    {
        config(['ig-user.merge' => true]);

        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $user = User::factory()->create(['role' => Role::CUSTOMER]);
        $candidate = User::factory()->create(['role' => Role::CUSTOMER, 'name' => 'Merge Candidate']);

        $response = $this->actingAs($manager)->get(route('users.show', $user));

        $response->assertStatus(200);
        $response->assertSee('x-data="mergeSearch(', false);
        $response->assertSee($candidate->name);
        $response->assertSee($candidate->email);
        $response->assertSee(__('ig-user::user.merges-add'));

        // nothing to search through, so no search box and no hint to start typing
        $response->assertDontSee(__('ig-user::user.merges-search'));
        $response->assertDontSee(__('ig-user::user.merges-hint'));
    }

    public function test_show_renders_a_searchable_merge_candidate_picker()
    {
        config(['ig-user.merge' => true]);

        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $user = User::factory()->create(['role' => Role::CUSTOMER]);
        $candidates = User::factory()->count(User::MERGE_CANDIDATES_SHOWN + 1)->create(['role' => Role::CUSTOMER]);

        $response = $this->actingAs($manager)->get(route('users.show', $user));

        $response->assertStatus(200);
        $response->assertSee(__('ig-user::user.merges-search'));
        $response->assertSee(__('ig-user::user.merges-hint'));
        $response->assertSee(__('ig-user::user.merges-none'));
        $response->assertSee(__('ig-user::user.merges-more'));
        $response->assertSee('x-data="mergeSearch(', false);
        $response->assertSee('x-for="(candidate, index) in visible"', false);
        $response->assertSee($candidates->first()->email);
    }

    public function test_show_embeds_the_candidates_while_the_installation_is_small()
    {
        config(['ig-user.merge' => true, 'ig-user.merge_inline_limit' => 10]);

        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $user = User::factory()->create(['role' => Role::CUSTOMER]);
        $candidate = User::factory()->create(['role' => Role::CUSTOMER, 'name' => 'Merge Candidate']);

        $response = $this->actingAs($manager)->get(route('users.show', $user));

        $response->assertStatus(200);
        $response->assertSee($candidate->email);
        // the URL travels JSON encoded inside x-data, so match the path instead of the raw route
        $response->assertDontSee('merge-candidates', false);
    }

    public function test_show_switches_to_server_side_search_above_the_inline_limit()
    {
        config(['ig-user.merge' => true, 'ig-user.merge_inline_limit' => 2]);

        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $user = User::factory()->create(['role' => Role::CUSTOMER]);
        User::factory()->count(5)->create(['role' => Role::CUSTOMER]);

        $response = $this->actingAs($manager)->get(route('users.show', $user));

        $response->assertStatus(200);
        $response->assertSee('merge-candidates', false);
        $response->assertSee('mergeSearch([],', false);
    }

    public function test_merge_candidates_endpoint_searches_by_name_and_email()
    {
        config(['ig-user.merge' => true]);

        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $user = User::factory()->create(['role' => Role::CUSTOMER]);
        $match = User::factory()->create(['role' => Role::CUSTOMER, 'name' => 'Jana Dvorakova', 'email' => 'jana@example.com']);
        $other = User::factory()->create(['role' => Role::CUSTOMER, 'name' => 'Petr Svoboda', 'email' => 'petr@example.com']);

        $response = $this->actingAs($manager)->getJson(route('users.merge-candidates', $user) . '?q=jana');

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $match->id, 'name' => $match->name, 'email' => $match->email]);
        $response->assertJsonMissing(['id' => $other->id]);
    }

    public function test_merge_candidates_endpoint_returns_one_row_over_what_the_picker_shows()
    {
        config(['ig-user.merge' => true]);

        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $user = User::factory()->create(['role' => Role::CUSTOMER]);
        User::factory()->count(User::MERGE_CANDIDATES_SHOWN + 5)->create(['role' => Role::CUSTOMER]);

        $response = $this->actingAs($manager)->getJson(route('users.merge-candidates', $user));

        $response->assertStatus(200);

        // one row over the limit is what tells the picker to ask for a narrower search
        $this->assertCount(User::MERGE_CANDIDATES_SHOWN + 1, $response->json());
    }

    public function test_merge_candidates_endpoint_excludes_the_existing_group()
    {
        config(['ig-user.merge' => true]);

        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $user = User::factory()->create(['role' => Role::CUSTOMER]);
        $member = User::factory()->create(['role' => Role::CUSTOMER]);
        $user->mergeWith($member);

        $response = $this->actingAs($manager)->getJson(route('users.merge-candidates', $user->fresh()));

        $response->assertStatus(200);
        $response->assertJsonMissing(['id' => $member->id]);
        $response->assertJsonMissing(['id' => $user->id]);
    }

    public function test_merge_candidates_endpoint_is_denied_without_the_merge_ability()
    {
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $user = User::factory()->create(['role' => Role::CUSTOMER]);

        // merging is disabled by default, which denies the gate for everyone
        $this->actingAs($manager)->getJson(route('users.merge-candidates', $user))->assertForbidden();

        config(['ig-user.merge' => true]);

        $operator = User::factory()->create(['role' => Role::OPERATOR]);
        $this->actingAs($operator)->getJson(route('users.merge-candidates', $user))->assertForbidden();

        $this->post(route('users.merge-candidates', $user))->assertStatus(405);
    }

    public function test_show_hides_the_merge_section_while_merging_is_disabled()
    {
        $manager = User::factory()->create(['role' => Role::MANAGER]);
        $user = User::factory()->create(['role' => Role::CUSTOMER]);

        $response = $this->actingAs($manager)->get(route('users.show', $user));

        $response->assertStatus(200);
        $response->assertDontSee(__('ig-user::user.merges'));
    }

    public function test_show_hides_the_merge_section_from_non_managers()
    {
        $operator = User::factory()->create(['role' => Role::OPERATOR]);

        $response = $this->actingAs($operator)->get(route('users.show', $operator));

        $response->assertStatus(200);
        $response->assertDontSee(__('ig-user::user.merges'));
    }
}
