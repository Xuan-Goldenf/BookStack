<?php

namespace BookStack\Http\Controllers;

use BookStack\Auth\Role;
use BookStack\Auth\User;
use BookStack\Entities\Models\Entity;
use BookStack\Entities\Queries\TopBulletins;
use BookStack\Interfaces\Bulletinable;
use BookStack\Model;
use Illuminate\Http\Request;

class BulletinController extends Controller
{
    /**
     * Show a listing of all bulletin items for the current user.
     */
    public function index(Request $request)
    {
        $viewCount = 20;
        $page = intval($request->get('page', 1));
        $bulletins = (new TopBulletins())->run($viewCount + 1, (($page - 1) * $viewCount));

        $hasMoreLink = ($bulletins->count() > $viewCount) ? url('/bulletins?page=' . ($page + 1)) : null;

        $this->setPageTitle(trans('entities.bulletin'));

        return view('common.detailed-listing-with-more', [
            'title'       => trans('entities.bulletin'),
            'entities'    => $bulletins->slice(0, $viewCount),
            'hasMoreLink' => $hasMoreLink,
        ]);
    }

    /**
     * Add a new item as a bulletin.
     */
    public function add(Request $request)
    {
        $bulletinable = $this->getValidatedModelFromRequest($request);
        $bulletinable->bulletins()->firstOrCreate([
            'role_id' => user()->id,
        ]);

        $this->showSuccessNotification(trans('activities.bulletin_add_notification', [
            'name' => $bulletinable->name,
        ]));

        return redirect()->back();
    }

    /**
     * Remove an item as a bulletin.
     */
    public function remove(Request $request)
    {
        $bulletinable = $this->getValidatedModelFromRequest($request);
        $bulletinable->bulletins()->where([
            'role_id' => user()->id,
        ])->delete();

        $this->showSuccessNotification(trans('activities.bulletin_remove_notification', [
            'name' => $bulletinable->name,
        ]));

        return redirect()->back();
    }

    /**
     * Toggle items as a bulletin.
     */
    public function toggle(Request $request)
    {
        $bulletinable = $this->getValidatedModelFromRequest($request);
        $bulletinable->bulletins()
            ->whereIn('role_id', $this->getCurrentUserRoleIds())->delete();
        foreach($request->input('roles', []) as $roleID) {
            $bulletinable->bulletins()->firstOrCreate([
                'role_id' => $roleID,
            ]);
        }

        $this->showSuccessNotification(trans('activities.bulletin_sync_notification', [
            'name' => $bulletinable->name,
        ]));

        return redirect()->back();
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    protected function getValidatedModelFromRequest(Request $request): Entity
    {
        $modelInfo = $this->validate($request, [
            'type' => ['required', 'string'],
            'id'   => ['required', 'integer'],
        ]);

        if (!class_exists($modelInfo['type'])) {
            throw new \Exception('Model not found');
        }

        /** @var Model $model */
        $model = new $modelInfo['type']();
        if (!$model instanceof Bulletinable) {
            throw new \Exception('Model not bulletinable');
        }

        $modelInstance = $model->newQuery()
            ->where('id', '=', $modelInfo['id'])
            ->first(['id', 'name']);

        $inaccessibleEntity = ($modelInstance instanceof Entity && !userCan('view', $modelInstance));
        if (is_null($modelInstance) || $inaccessibleEntity) {
            throw new \Exception('Model instance not found');
        }

        return $modelInstance;
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
