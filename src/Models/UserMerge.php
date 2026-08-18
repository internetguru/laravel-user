<?php

namespace InternetGuru\LaravelUser\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single undirected link between two user accounts belonging to the same person.
 *
 * Two invariants are maintained by InternetGuru\LaravelUser\Traits\MergedAccounts:
 * pairs are normalized so that user_id < merged_user_id, and every unordered pair
 * within a merged group is stored (a clique, not a spanning chain).
 */
class UserMerge extends Model
{
    protected $fillable = [
        'user_id',
        'merged_user_id',
    ];

    /**
     * Order a pair of user ids so the same link can never be stored in mirror form.
     *
     * @return array{0: int, 1: int}
     */
    public static function normalize(int $a, int $b): array
    {
        return $a < $b ? [$a, $b] : [$b, $a];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mergedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merged_user_id');
    }
}
