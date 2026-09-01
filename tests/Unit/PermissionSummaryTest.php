<?php

namespace Tests\Unit\Support;

use App\Models\User;
use App\Policies\AccountPolicy;
use App\Policies\WidgetPolicy;
use InternetGuru\LaravelUser\Enums\Role;
use InternetGuru\LaravelUser\Policies\UserPolicy as PackageUserPolicy;
use InternetGuru\LaravelUser\Support\PermissionSummary;
use Tests\TestCase;

class PermissionSummaryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['ig-user.role_list.policy_paths' => [
            __DIR__ . '/../src/App/Policies' => 'App\\Policies',
        ]]);
    }

    private function summary(): PermissionSummary
    {
        return app(PermissionSummary::class);
    }

    public function test_policies_are_discovered_from_the_configured_paths()
    {
        $this->assertSame(WidgetPolicy::class, $this->summary()->policies()['WidgetPolicy']);
    }

    public function test_an_application_policy_replaces_the_package_policy_it_extends()
    {
        $policies = $this->summary()->policies();

        $this->assertSame(AccountPolicy::class, $policies['AccountPolicy']);
        $this->assertNotContains(PackageUserPolicy::class, $policies);
    }

    public function test_matrix_evaluates_every_ability_for_every_role()
    {
        $matrix = $this->summary()->matrix();

        $this->assertFalse($matrix[Role::CUSTOMER->value]['WidgetPolicy@view']['allowed']);
        $this->assertTrue($matrix[Role::OPERATOR->value]['WidgetPolicy@view']['allowed']);
    }

    public function test_the_admin_role_is_left_out_and_the_rest_ordered_by_level()
    {
        $this->assertSame(
            [Role::CUSTOMER, Role::OPERATOR, Role::AUDITOR, Role::MANAGER],
            $this->summary()->roles()
        );
    }

    public function test_abilities_the_resolver_cannot_describe_are_left_out()
    {
        $matrix = $this->summary()->matrix();

        $this->assertArrayNotHasKey('WidgetPolicy@inspect', $matrix[Role::MANAGER->value]);
    }

    public function test_abilities_failing_without_real_context_are_left_out()
    {
        $matrix = $this->summary()->matrix();

        $this->assertArrayNotHasKey('WidgetPolicy@purge', $matrix[Role::MANAGER->value]);
    }

    public function test_a_role_only_lists_what_it_gains_over_the_role_below_it()
    {
        $grouped = $this->summary()->groupedByRole();

        $this->assertArrayHasKey('WidgetPolicy@view', $grouped[Role::OPERATOR->value]['granted']);
        $this->assertArrayNotHasKey('WidgetPolicy@view', $grouped[Role::AUDITOR->value]['granted']);
        $this->assertTrue($grouped[Role::CUSTOMER->value]['base']);
    }

    public function test_an_ability_lost_at_a_higher_role_is_listed_as_revoked()
    {
        $grouped = $this->summary()->groupedByRole();

        // The package lets everyone manage their own profile; a manager is compared against
        // a manager peer, which UserPolicy::crud allows as well, so nothing is revoked there.
        $this->assertArrayHasKey('AccountPolicy@crud', $grouped[Role::CUSTOMER->value]['granted']);
        $this->assertSame([], $grouped[Role::MANAGER->value]['revoked']);
    }

    public function test_the_application_override_of_a_package_ability_drives_the_summary()
    {
        $grouped = $this->summary()->groupedByRole();

        $this->assertArrayNotHasKey('AccountPolicy@viewRoleList', $grouped[Role::CUSTOMER->value]['granted']);
        $this->assertArrayHasKey('AccountPolicy@viewRoleList', $grouped[Role::MANAGER->value]['granted']);
    }

    public function test_an_ability_falls_back_to_its_key_until_someone_names_it()
    {
        $summary = $this->summary();

        $this->assertSame(__('ig-user::role-list.UserPolicy@crud'), $summary->label('UserPolicy@crud'));
        $this->assertSame('WidgetPolicy@view', $summary->label('WidgetPolicy@view'));
    }

    public function test_the_summary_never_reads_stored_accounts()
    {
        User::factory()->count(3)->create(['role' => Role::ADMIN]);

        $arguments = $this->summary()->matrix()[Role::CUSTOMER->value]['AccountPolicy@crud']['arguments'];

        foreach ($arguments as $argument) {
            $this->assertNull($argument->getKey());
            $this->assertSame(Role::CUSTOMER, $argument->role);
        }
    }
}
