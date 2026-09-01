<?php

namespace InternetGuru\LaravelUser\Support;

use App\Models\User;
use BackedEnum;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Throwable;

/**
 * The permission model of the application, derived from the policies themselves.
 *
 * Nothing is listed by hand: the policies are discovered, every ability is invoked once per
 * role with sample arguments, and the results are reduced to what each role gains or loses
 * compared to the role below it. A policy change therefore shows up on the page without
 * anyone maintaining a second copy of the rules.
 *
 * @phpstan-type Permission array{role: BackedEnum, policy: string, ability: string, arguments: array<int, mixed>, allowed: bool}
 */
class PermissionSummary
{
    /**
     * Policy methods belonging to the authorization plumbing rather than to an ability.
     * The HandlesAuthorization helpers are public, so reflection alone cannot tell them apart.
     */
    protected const IGNORED_METHODS = [
        '__construct',
        'before',
        'after',
        'allow',
        'deny',
        'denyWithStatus',
        'denyAsNotFound',
    ];

    public function __construct(protected PolicyArgumentResolver $resolver) {}

    /**
     * Permissions each role gains (or loses) compared to the previous, less privileged role.
     *
     * @return array<string, array{role: BackedEnum, base: bool, granted: array<string, Permission>, revoked: array<string, Permission>}>
     */
    public function groupedByRole(): array
    {
        $matrix = $this->matrix();

        $grouped = [];
        $previousPermissions = [];

        foreach ($this->roles() as $index => $role) {
            $permissions = $matrix[$role->value] ?? [];

            $granted = [];
            $revoked = [];
            foreach ($permissions as $key => $permission) {
                $wasAllowed = $previousPermissions[$key]['allowed'] ?? false;
                if ($permission['allowed'] && ! $wasAllowed) {
                    $granted[$key] = $permission;
                } elseif (! $permission['allowed'] && $wasAllowed) {
                    $revoked[$key] = $permission;
                }
            }

            $grouped[$role->value] = [
                'role' => $role,
                'base' => $index === 0,
                'granted' => $granted,
                'revoked' => $revoked,
            ];

            $previousPermissions = $permissions;
        }

        return $grouped;
    }

    /**
     * Every ability evaluated for every role, keyed by role value and then by `Policy@ability`.
     *
     * @return array<string, array<string, Permission>>
     */
    public function matrix(): array
    {
        $policies = $this->policies();

        $matrix = [];
        foreach ($this->roles() as $role) {
            foreach ($policies as $policy => $className) {
                foreach ($this->abilities($className) as $method) {
                    $permission = $this->evaluate($className, $method, $role);
                    if ($permission === null) {
                        continue;
                    }

                    $matrix[$role->value]["$policy@{$method->getName()}"] = [
                        'role' => $role,
                        'policy' => $policy,
                        'ability' => $method->getName(),
                        ...$permission,
                    ];
                }
            }
        }

        return $matrix;
    }

    /**
     * Discovered policies, keyed by the short class name the translation keys use.
     *
     * @return array<string, class-string>
     */
    public function policies(): array
    {
        $classNames = [];

        foreach (config('ig-user.role_list.policy_paths', []) as $path => $namespace) {
            foreach (glob(rtrim($path, '/') . '/*.php') ?: [] as $file) {
                $classNames[] = rtrim($namespace, '\\') . '\\' . basename($file, '.php');
            }
        }

        // Registered policies cover the packages, whose directories the application does not list.
        $classNames = array_merge($classNames, array_values(Gate::policies()));

        $classNames = array_values(array_filter(array_unique($classNames), 'class_exists'));

        // Keep only the most derived class of each hierarchy: an application policy extending a
        // package one replaces it, so its overrides drive the summary instead of the parent's.
        $policies = [];
        foreach ($classNames as $className) {
            foreach ($classNames as $other) {
                if ($other !== $className && is_subclass_of($other, $className)) {
                    continue 2;
                }
            }

            $policies[class_basename($className)] = $className;
        }

        ksort($policies);

        return $policies;
    }

    /**
     * Roles the summary describes, from the least to the most privileged.
     *
     * @return list<BackedEnum>
     */
    public function roles(): array
    {
        $roles = array_values(User::publicRolesArray());
        usort($roles, fn (BackedEnum $a, BackedEnum $b): int => $a->level() <=> $b->level());

        return $roles;
    }

    /**
     * Human readable name of an ability.
     *
     * The application's own `role-list` lines win over the package ones, so an application can
     * name a package ability in the words of its own domain. An ability nobody has named yet
     * falls back to its key, which keeps a newly added policy visible instead of blank.
     */
    public function label(string $key): string
    {
        foreach (["role-list.$key", "ig-user::role-list.$key"] as $line) {
            if (Lang::has($line)) {
                return __($line);
            }
        }

        return $key;
    }

    /**
     * The sample arguments an ability was evaluated with, for the debug-only dump on the page.
     *
     * @param  array<int, mixed>  $arguments
     */
    public function describeArguments(array $arguments): string
    {
        return print_r(array_map(
            fn (mixed $argument): mixed => match (true) {
                $argument instanceof Model => class_basename($argument) . ' ' . json_encode($argument->attributesToArray()),
                $argument instanceof BackedEnum => class_basename($argument) . '::' . $argument->name,
                is_object($argument) => get_class($argument),
                default => $argument,
            },
            $arguments
        ), true);
    }

    /**
     * @return list<ReflectionMethod>
     */
    protected function abilities(string $className): array
    {
        return array_values(array_filter(
            (new ReflectionClass($className))->getMethods(ReflectionMethod::IS_PUBLIC),
            fn (ReflectionMethod $method): bool => ! $method->isStatic()
                && ! in_array($method->getName(), self::IGNORED_METHODS, true)
        ));
    }

    /**
     * Invoke a single ability for a single role.
     *
     * The policy is called directly rather than through the gate: the summary documents the
     * policies, and a global before callback would collapse every admin ability into one answer.
     *
     * Returns null for an ability this installation cannot describe — an argument the resolver
     * does not know, or a policy that needs more context than a sample object carries. Such an
     * ability is left out entirely instead of being reported as denied.
     *
     * @return array{arguments: array<int, mixed>, allowed: bool}|null
     */
    protected function evaluate(string $className, ReflectionMethod $method, BackedEnum $role): ?array
    {
        $arguments = [];
        foreach ($method->getParameters() as $parameter) {
            $argument = $this->resolver->resolve($parameter, $role);

            if ($argument === null && ! $this->acceptsNull($parameter)) {
                return null;
            }

            $arguments[] = $argument;
        }

        try {
            $result = $method->invokeArgs(app($className), $arguments);
        } catch (Throwable) {
            return null;
        }

        return [
            'arguments' => $arguments,
            'allowed' => $result instanceof Response ? $result->allowed() : (bool) $result,
        ];
    }

    protected function acceptsNull(ReflectionParameter $parameter): bool
    {
        return ! $parameter->hasType() || $parameter->getType()->allowsNull();
    }
}
