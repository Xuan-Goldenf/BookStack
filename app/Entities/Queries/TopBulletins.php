<?php

namespace BookStack\Entities\Queries;

use BookStack\Actions\Bulletin;
use BookStack\Auth\Role;
use BookStack\Auth\User;
use Illuminate\Database\Query\JoinClause;

class TopBulletins extends EntityQuery
{
    public function run(int $count, int $skip = 0)
    {
        $user = user();
        if (is_null($user) || $user->isDefault()) {
            return collect();
        }

        $query = $this->permissionService()
            ->restrictEntityRelationQuery(Bulletin::query(), 'bulletins', 'bulletinable_id', 'bulletinable_type')
            ->select('bulletins.*')
            ->leftJoin('views', function (JoinClause $join) {
                $join->on('bulletins.bulletinable_id', '=', 'views.viewable_id');
                $join->on('bulletins.bulletinable_type', '=', 'views.viewable_type');
                // $join->where('views.user_id', '=', user()->id);
            })
            ->orderBy('views.views', 'desc')
            ->whereIn('bulletins.role_id', $this->getCurrentUserRoleIds())->distinct();

        return $query->with('bulletinable')
            ->skip($skip)
            ->take($count)
            ->get()
            ->pluck('bulletinable')
            ->filter();
    }

    /**
     * Get the current user.
     */
    protected function currentUser(): User
    {
        return user();
    }

    /**
     * Get the roles for the current logged-in user.
     *
     * @return int[]
     */
    protected function getCurrentUserRoleIds(): array
    {
        if (auth()->guest()) {
            return [Role::getSystemRole('public')->id];
        }

        return $this->currentUser()->roles->pluck('id')->values()->all();
    }
}
