<?php

namespace InternetGuru\LaravelUser\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InternetGuru\LaravelUser\Models\UserMerge;

/**
 * Merged account groups: several user rows belonging to the same real person.
 *
 * Consumers scope owned rows to the whole group — `whereIn('user_id', $user->mergedIds())`
 * instead of `where('user_id', $user->id)`. Membership is symmetric: every member resolves
 * the same group.
 *
 * IMPORTANT: mergedIds() expresses row ownership only. Roles and abilities are never unioned
 * across a group — a merged group keeps each account's own role. Because owning a row usually
 * implies the right to edit it, merging accounts of different roles does let the
 * lower-privileged member reach the other's rows; that is why the `merge` ability requires
 * `crud` on both accounts.
 */
trait MergedAccounts
{
    /**
     * How many candidates the picker lists. A search matching more is answered with "keep
     * typing" instead, so one row over the limit is all a query ever needs to return.
     */
    public const MERGE_CANDIDATES_SHOWN = 10;

    /**
     * Per-instance memo. Never make this static or container-bound: under Octane that would
     * leak across requests.
     */
    protected ?array $mergedIdsCache = null;

    /**
     * Drop a deleted account's links, so the remaining members stay merged with each other.
     *
     * The migration declares onDelete('cascade') as well, but the clique is an application
     * invariant and must not depend on the driver actually enforcing foreign keys — SQLite,
     * for instance, does not by default.
     */
    public static function bootMergedAccounts(): void
    {
        static::deleted(function ($user) {
            $key = (int) $user->getKey();

            DB::table('user_merges')
                ->where('user_id', $key)
                ->orWhere('merged_user_id', $key)
                ->delete();
        });
    }

    /**
     * All user ids in this account's merged group, including its own.
     *
     * Resolved in a single query: the stored pairs form a clique, so an account's direct
     * links already are its whole group.
     *
     * @return array<int, int>
     */
    public function mergedIds(): array
    {
        if ($this->mergedIdsCache !== null) {
            return $this->mergedIdsCache;
        }

        $key = (int) $this->getKey();

        $ids = DB::table('user_merges')
            ->where('user_id', $key)
            ->orWhere('merged_user_id', $key)
            ->get(['user_id', 'merged_user_id'])
            ->flatMap(fn ($row) => [(int) $row->user_id, (int) $row->merged_user_id])
            ->push($key)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $this->mergedIdsCache = $ids;
    }

    public function forgetMergedIds(): void
    {
        $this->mergedIdsCache = null;
    }

    /**
     * Other accounts in this account's merged group, in one query.
     */
    public function mergedUsers(): Collection
    {
        $ids = array_diff($this->mergedIds(), [(int) $this->getKey()]);

        return User::whereIn('id', $ids)->orderBy('name')->get();
    }

    public function isMergedWith(User $other): bool
    {
        return in_array((int) $other->getKey(), $this->mergedIds(), true);
    }

    public function isMerged(): bool
    {
        return count($this->mergedIds()) > 1;
    }

    /**
     * The group as a comma separated id list, excluding this account.
     *
     * Backs the `merged` pseudo-column in the association history, the same way
     * getSocialiteAttribute() backs `socialite`. Ids rather than names, because names mutate.
     */
    public function getMergedAttribute(): string
    {
        return implode(',', array_diff($this->mergedIds(), [(int) $this->getKey()]));
    }

    /**
     * Merge the two accounts' groups into one.
     *
     * Idempotent. Rebuilds the full clique from the union of both groups, which also repairs
     * any pre-existing rows that only formed a chain.
     */
    public function mergeWith(User $other): void
    {
        if ((int) $other->getKey() === (int) $this->getKey()) {
            return;
        }

        DB::transaction(function () use ($other) {
            $members = $this->lockAndResolveGroups($other);

            $affected = $this->snapshotAffected($members);

            foreach ($this->pairs($members) as [$a, $b]) {
                UserMerge::firstOrCreate([
                    'user_id' => $a,
                    'merged_user_id' => $b,
                ]);
            }

            $this->recordMergeHistory($affected);

            $this->forgetMergedIds();
            $other->forgetMergedIds();
        });
    }

    /**
     * Remove an account from this account's merged group.
     *
     * Deletes every link between that account and the remaining members, so the group splits
     * exactly as the UI presents it.
     */
    public function unmergeFrom(User $other): void
    {
        if ((int) $other->getKey() === (int) $this->getKey()) {
            return;
        }

        DB::transaction(function () use ($other) {
            $members = $this->lockAndResolveGroups($other);

            if (! in_array((int) $other->getKey(), $members, true)) {
                return;
            }

            $affected = $this->snapshotAffected($members);

            $otherKey = (int) $other->getKey();
            $remaining = array_values(array_diff($members, [$otherKey]));

            foreach ($remaining as $memberKey) {
                [$a, $b] = UserMerge::normalize($otherKey, $memberKey);
                UserMerge::where('user_id', $a)->where('merged_user_id', $b)->delete();
            }

            $this->recordMergeHistory($affected);

            $this->forgetMergedIds();
            $other->forgetMergedIds();
        });
    }

    /**
     * Users this account may be merged with, as model-browser style options.
     *
     * Deliberately not built on User::summary(), which applies scopeFilterAutomatic and would
     * hide the automatic placeholder accounts that are the most common merge targets. The email
     * travels alongside the name, because duplicate accounts usually share a name and the
     * candidate picker both shows and searches it.
     *
     * The accounts already in the group stay in the list, flagged as `merged`, so adding one
     * greys its row out where it stands instead of pulling every row below it up.
     *
     * Always bounded: the picker embeds the result in the page while the installation is small
     * and switches to searching over `users.merge-candidates` once it is not, so neither path
     * may load the whole table.
     *
     * @param  string|null  $search  Whitespace separated terms, each matched against name and email
     * @return array<int, array{id: int, name: string, email: string, merged: bool}>
     */
    public static function mergeCandidateOptions(User $for, ?string $search = null, int $limit = self::MERGE_CANDIDATES_SHOWN + 1): array
    {
        // Lowercased because whereLikeUnaccented falls back to a plain LIKE for an accented
        // search value, and SQLite's LIKE only folds case for ASCII: without this, "NOVÁK"
        // would miss "Novák" while "novak" and "Novák" both find it.
        $terms = array_map(mb_strtolower(...), array_filter(preg_split('/\s+/', (string) $search)));
        $mergedIds = $for->mergedIds();

        return User::whereKeyNot($for->getKey())
            ->when(
                ! auth()?->user()?->isAdmin(),
                fn ($query) => $query->where('role', '!=', User::roles()::ADMIN->value)
            )
            ->tap(function ($query) use ($terms) {
                foreach ($terms as $term) {
                    // whereLikeUnaccented comes from laravel-model-browser and matches without
                    // case or diacritics on both SQLite and MySQL, so "novak" finds "Novák".
                    // Emails are ASCII, which lets it skip the SQLite unaccent() UDF.
                    $query->where(fn ($subQuery) => $subQuery
                        ->whereLikeUnaccented('name', $term)
                        ->orWhere(fn ($emailQuery) => $emailQuery->whereLikeUnaccented('email', $term, asciiFast: true))
                    );
                }
            })
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'email'])
            ->map(fn ($user) => [
                'id' => (int) $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'merged' => in_array((int) $user->id, $mergedIds, true),
            ])
            ->all();
    }

    /**
     * Lock both accounts' existing links and return the union of their groups.
     *
     * The lock stops two concurrent merges from leaving a partial clique behind.
     *
     * @return array<int, int>
     */
    private function lockAndResolveGroups(User $other): array
    {
        $keys = [(int) $this->getKey(), (int) $other->getKey()];

        DB::table('user_merges')
            ->whereIn('user_id', $keys)
            ->orWhereIn('merged_user_id', $keys)
            ->lockForUpdate()
            ->get();

        $this->forgetMergedIds();
        $other->forgetMergedIds();

        $members = array_unique(array_merge($this->mergedIds(), $other->mergedIds()));
        sort($members);

        return array_values($members);
    }

    /**
     * Load every affected account with its group state as it is before the write.
     *
     * @param  array<int, int>  $members
     * @return array<int, array{user: User, prev: string}>
     */
    private function snapshotAffected(array $members): array
    {
        return User::whereIn('id', $members)
            ->get()
            ->map(fn (User $user) => [
                'user' => $user,
                'prev' => $user->merged,
            ])
            ->all();
    }

    /**
     * Write one history entry per affected account, each with that account's own previous value.
     *
     * The pivot is invisible to AssociationHistory's dirty-column tracking, so entries are
     * written by hand — the same approach SocialiteAuth uses for its `socialite`
     * pseudo-column. Every affected account gets an entry, otherwise the other side's detail
     * page would show nothing and its history chain could not be reconstructed later.
     *
     * @param  array<int, array{user: User, prev: string}>  $affected
     */
    private function recordMergeHistory(array $affected): void
    {
        foreach ($affected as $entry) {
            $user = $entry['user'];
            $user->forgetMergedIds();

            if ($user->merged === $entry['prev']) {
                continue;
            }

            $user->associationHistories()->create([
                'column_name' => 'merged',
                'column_prev_value' => $entry['prev'],
                'author_id' => auth()->id(),
            ]);
        }
    }

    /**
     * Every unordered, normalized pair within a group.
     *
     * @param  array<int, int>  $members
     * @return array<int, array{0: int, 1: int}>
     */
    private function pairs(array $members): array
    {
        $pairs = [];
        $count = count($members);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $pairs[] = UserMerge::normalize($members[$i], $members[$j]);
            }
        }

        return $pairs;
    }
}
