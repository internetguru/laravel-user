<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InternetGuru\LaravelUser\Enums\Role;
use InternetGuru\LaravelUser\Models\UserMerge;
use Tests\TestCase;

class MergedAccountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_unmerged_account_group_is_only_itself()
    {
        $user = User::factory()->create();

        $this->assertSame([$user->id], $user->mergedIds());
        $this->assertFalse($user->isMerged());
        $this->assertSame('', $user->merged);
    }

    public function test_merge_is_symmetric_from_both_sides()
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        $first->mergeWith($second);

        $expected = collect([$first->id, $second->id])->sort()->values()->all();

        $this->assertSame($expected, $first->fresh()->mergedIds());
        $this->assertSame($expected, $second->fresh()->mergedIds());
        $this->assertTrue($first->fresh()->isMergedWith($second));
        $this->assertTrue($second->fresh()->isMergedWith($first));
    }

    public function test_merged_ids_are_integers_and_sorted()
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $third = User::factory()->create();

        $first->mergeWith($third);
        $first->fresh()->mergeWith($second);

        $ids = $first->fresh()->mergedIds();

        $this->assertSame(collect($ids)->sort()->values()->all(), $ids);
        foreach ($ids as $id) {
            $this->assertIsInt($id);
        }
    }

    public function test_merging_two_groups_stores_a_full_clique()
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $third = User::factory()->create();

        $first->mergeWith($second);
        // Merge the third one in via the second account, not the first
        $third->fresh()->mergeWith($second->fresh());

        $expected = collect([$first->id, $second->id, $third->id])->sort()->values()->all();

        $this->assertSame($expected, $first->fresh()->mergedIds());
        $this->assertSame($expected, $second->fresh()->mergedIds());
        $this->assertSame($expected, $third->fresh()->mergedIds());

        // A clique of three, not a two-edge chain
        $this->assertSame(3, UserMerge::count());
    }

    public function test_unmerge_leaves_the_rest_of_the_group_intact()
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $third = User::factory()->create();

        $first->mergeWith($second);
        $first->fresh()->mergeWith($third);

        $first->fresh()->unmergeFrom($third->fresh());

        $expected = collect([$first->id, $second->id])->sort()->values()->all();

        $this->assertSame($expected, $first->fresh()->mergedIds());
        $this->assertSame($expected, $second->fresh()->mergedIds());
        $this->assertSame([$third->id], $third->fresh()->mergedIds());
        $this->assertSame(1, UserMerge::count());
    }

    public function test_unmerge_a_pair_leaves_both_accounts_alone()
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        $first->mergeWith($second);
        $first->fresh()->unmergeFrom($second->fresh());

        $this->assertSame([$first->id], $first->fresh()->mergedIds());
        $this->assertSame([$second->id], $second->fresh()->mergedIds());
        $this->assertSame(0, UserMerge::count());
    }

    public function test_merge_is_idempotent()
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        $first->mergeWith($second);
        $first->fresh()->mergeWith($second->fresh());

        $this->assertSame(1, UserMerge::count());
    }

    public function test_self_merge_is_ignored()
    {
        $user = User::factory()->create();

        $user->mergeWith($user);

        $this->assertSame(0, UserMerge::count());
        $this->assertSame([$user->id], $user->fresh()->mergedIds());
    }

    public function test_pairs_are_normalized_regardless_of_merge_direction()
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        // Merge from the higher id towards the lower one
        $second->mergeWith($first);

        $this->assertSame(1, UserMerge::count());
        $this->assertDatabaseHas('user_merges', [
            'user_id' => min($first->id, $second->id),
            'merged_user_id' => max($first->id, $second->id),
        ]);
    }

    public function test_deleting_a_middle_member_leaves_the_others_merged()
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $third = User::factory()->create();

        $first->mergeWith($second);
        $first->fresh()->mergeWith($third);

        $second->delete();

        $expected = collect([$first->id, $third->id])->sort()->values()->all();

        $this->assertSame($expected, $first->fresh()->mergedIds());
        $this->assertSame($expected, $third->fresh()->mergedIds());
    }

    public function test_next_merge_completes_a_hand_inserted_chain_into_a_clique()
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $third = User::factory()->create();
        $fourth = User::factory()->create();

        // Chain rows only: first-second and second-third, no first-third link
        [$a, $b] = UserMerge::normalize($first->id, $second->id);
        UserMerge::create(['user_id' => $a, 'merged_user_id' => $b]);
        [$a, $b] = UserMerge::normalize($second->id, $third->id);
        UserMerge::create(['user_id' => $a, 'merged_user_id' => $b]);

        $second->fresh()->mergeWith($fourth);

        // second's group is {first, second, third} plus fourth, so a clique of four
        $this->assertSame(6, UserMerge::count());
        $this->assertTrue($first->fresh()->isMergedWith($third));
        $this->assertTrue($first->fresh()->isMergedWith($fourth));
    }

    public function test_merged_ids_are_memoized_and_can_be_forgotten()
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->assertSame([$first->id], $first->mergedIds());

        [$a, $b] = UserMerge::normalize($first->id, $second->id);
        UserMerge::create(['user_id' => $a, 'merged_user_id' => $b]);

        // Still the memoized value
        $this->assertSame([$first->id], $first->mergedIds());

        $first->forgetMergedIds();

        $expected = collect([$first->id, $second->id])->sort()->values()->all();
        $this->assertSame($expected, $first->mergedIds());
    }

    public function test_merge_refreshes_the_memo_on_both_instances()
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        // Prime both memos before mutating
        $first->mergedIds();
        $second->mergedIds();

        $first->mergeWith($second);

        $expected = collect([$first->id, $second->id])->sort()->values()->all();
        $this->assertSame($expected, $first->mergedIds());
        $this->assertSame($expected, $second->mergedIds());
    }

    public function test_merged_users_excludes_self()
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        $first->mergeWith($second);

        $mergedUsers = $first->fresh()->mergedUsers();

        $this->assertCount(1, $mergedUsers);
        $this->assertSame($second->id, $mergedUsers->first()->id);
    }

    public function test_merged_attribute_lists_the_other_members_only()
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        $first->mergeWith($second);

        $this->assertSame((string) $second->id, $first->fresh()->merged);
        $this->assertSame((string) $first->id, $second->fresh()->merged);
    }

    public function test_merge_writes_a_history_entry_for_every_affected_account()
    {
        $manager = User::factory()->withRole(Role::MANAGER)->create();
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->actingAs($manager);
        $first->mergeWith($second);

        foreach ([$first, $second] as $user) {
            $history = $user->fresh()->associationHistories()->where('column_name', 'merged')->get();

            $this->assertCount(1, $history);
            // Each account was alone before the merge
            $this->assertSame('', $history->first()->column_prev_value);
            $this->assertSame($manager->id, $history->first()->author_id);
        }
    }

    public function test_unmerge_records_the_previous_group_per_account()
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        $first->mergeWith($second);
        $first->fresh()->unmergeFrom($second->fresh());

        // Both entries land in the same second, so break the tie on the key, the same way
        // the association-history component does
        $history = $first->fresh()
            ->associationHistories()
            ->where('column_name', 'merged')
            ->orderByDesc('id')
            ->first();

        $this->assertSame((string) $second->id, $history->column_prev_value);
    }

    public function test_merging_does_not_change_either_role()
    {
        $spectator = User::factory()->withRole(Role::CUSTOMER)->create();
        $manager = User::factory()->withRole(Role::MANAGER)->create();

        $spectator->mergeWith($manager);

        $this->assertSame(Role::CUSTOMER, $spectator->fresh()->role);
        $this->assertSame(Role::MANAGER, $manager->fresh()->role);
    }

    public function test_merge_candidate_options_include_automatic_accounts_and_flag_members()
    {
        $admin = User::factory()->withRole(Role::ADMIN)->create();
        $this->actingAs($admin);

        $user = User::factory()->create();
        $automatic = User::factory()->automatic()->create();
        $member = User::factory()->create();

        $user->mergeWith($member);

        $options = collect(User::mergeCandidateOptions($user->fresh()));

        // Automatic placeholder accounts are the most common merge target
        $this->assertContains($automatic->id, $options->pluck('id')->all());
        $this->assertFalse($options->firstWhere('id', $automatic->id)['merged']);

        // Members stay listed, flagged, so adding one does not pull the rows below it up
        $this->assertTrue($options->firstWhere('id', $member->id)['merged']);
        $this->assertNotContains($user->id, $options->pluck('id')->all());
    }

    public function test_merge_candidate_options_carry_the_name_and_the_email()
    {
        $admin = User::factory()->withRole(Role::ADMIN)->create();
        $this->actingAs($admin);

        $user = User::factory()->create();
        $candidate = User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

        $options = collect(User::mergeCandidateOptions($user))->firstWhere('id', $candidate->id);

        $this->assertSame('Jane Doe', $options['name']);
        $this->assertSame('jane@example.com', $options['email']);
    }

    public function test_merge_candidate_options_are_bounded_and_searchable()
    {
        $admin = User::factory()->withRole(Role::ADMIN)->create();
        $this->actingAs($admin);

        $user = User::factory()->create();
        User::factory()->count(5)->create(['name' => 'Nobody']);
        $match = User::factory()->create(['name' => 'Jan Novák', 'email' => 'jan@example.com']);

        $this->assertCount(3, User::mergeCandidateOptions($user, limit: 3));

        $ids = collect(User::mergeCandidateOptions($user, 'nov jan'))->pluck('id')->all();

        $this->assertSame([$match->id], $ids);
    }

    public function test_merge_candidate_options_search_ignores_diacritics_and_case()
    {
        $admin = User::factory()->withRole(Role::ADMIN)->create();
        $this->actingAs($admin);

        $user = User::factory()->create();
        $match = User::factory()->create(['name' => 'Jan Novák', 'email' => 'jan@example.com']);
        User::factory()->create(['name' => 'Petr Svoboda', 'email' => 'petr@example.com']);

        foreach (['novak', 'NOVÁK', 'Novák'] as $search) {
            $ids = collect(User::mergeCandidateOptions($user, $search))->pluck('id')->all();

            $this->assertSame([$match->id], $ids, "search '{$search}' should find the accented name");
        }

        $ids = collect(User::mergeCandidateOptions($user, 'JAN@EXAMPLE.COM'))->pluck('id')->all();

        $this->assertSame([$match->id], $ids);
    }

    public function test_merge_candidate_options_hide_admins_from_non_admins()
    {
        $manager = User::factory()->withRole(Role::MANAGER)->create();
        $this->actingAs($manager);

        $admin = User::factory()->withRole(Role::ADMIN)->create();

        $ids = collect(User::mergeCandidateOptions($manager))->pluck('id')->all();

        $this->assertNotContains($admin->id, $ids);
    }
}
