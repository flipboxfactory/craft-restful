<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\filters\access;

use flipbox\craft\restful\Restful;
use Yii;
use yii\base\Action;
use yii\base\ActionFilter;
use yii\base\Controller;
use yii\di\Instance;
use yii\rbac\CheckAccessInterface;
use yii\rbac\Permission;
use yii\web\ForbiddenHttpException;
use yii\web\User;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
abstract class AbstractDynamicAccessControl extends ActionFilter
{
    /**
     * @var User|array|string|false the user object representing the authentication status or the ID of the user
     * application component.  This can be a configuration array for creating the object.  You can set it to `false`
     * to explicitly switch this component support off for the filter.
     */
    public $user = 'user';

    /**
     * @var CheckAccessInterface
     */
    public $accessChecker;

    /**
     * @var callable a callback that will be called if the access should be denied
     * to the current user. If not set, [[denyAccess()]] will be called.
     *
     * The signature of the callback should be as follows:
     *
     * ```php
     * function ($rule, $action)
     * ```
     *
     * where `$rule` is the rule that denies the user, and `$action` is the current [[Action|action]] object.
     * `$rule` can be `null` if access is denied because none of the rules matched.
     */
    public $denyCallback;

    /**
     * @var array|\Closure parameters to pass to the [[User::can()]] function for evaluating
     * user permissions in [[$roles]].
     *
     * If this is an array, it will be passed directly to [[User::can()]]. For example for passing an
     * ID from the current request, you may use the following:
     *
     * ```php
     * ['postId' => Yii::$app->request->get('id')]
     * ```
     *
     * You may also specify a closure that returns an array. This can be used to
     * evaluate the array values only if they are needed, for example when a model needs to be
     * loaded like in the following code:
     *
     * ```php
     * 'rules' => [
     *     [
     *         'allow' => true,
     *         'actions' => ['update'],
     *         'roles' => ['updatePost'],
     *         'roleParams' => function($rule) {
     *             return ['post' => Post::findOne(Yii::$app->request->get('id'))];
     *         },
     *     ],
     * ],
     * ```
     *
     * A reference to the [[AccessRule]] instance will be passed to the closure as the first parameter.
     *
     * @see $roles
     * @since 2.0.12
     */
    public $roleParams = [];

    /**
     * @param Controller $controller
     * @return string
     */
    abstract protected function getPermissionName(Controller $controller): string;

    /**
     * Initializes the [[rules]] array by instantiating rule objects from configurations.
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if ($this->user !== false) {
            $this->user = Instance::ensure($this->user, User::class);
        }

        if (!$this->accessChecker instanceof CheckAccessInterface) {
            $this->accessChecker = Restful::getInstance()->getAuthManager();
        }
    }

    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * You may override this method to do last-minute preparation for the action.
     *
     * @param Action $action the action to be executed.
     * @return bool whether the action should continue to be executed.
     * @throws ForbiddenHttpException
     */
    public function beforeAction($action)
    {

        /** @var Permission $permission */
        if (null === ($permission = Restful::getInstance()->getAuthManager()->getPermission(
            $this->getPermissionName($action->controller)
        ))) {
            return $this->deny($action, null);
        };

        if (!$this->checkPermission($permission)) {
            return $this->deny($action, $permission);
        }

        return true;
    }

    /**
     * Check whether a user is permitted to perform access
     *
     * @param Permission $permission
     * @return bool
     */
    protected function checkPermission(Permission $permission): bool
    {
        return $this->accessChecker->checkAccess(
            $this->user->id,
            $permission->name,
            $this->getRoleParams()
        );
    }

    /**
     * Handle denied access
     *
     * @param $action
     * @param null $params
     * @return bool
     * @throws ForbiddenHttpException
     */
    protected function deny($action, $params = null): bool
    {
        if ($this->denyCallback !== null) {
            call_user_func($this->denyCallback, $params, $action);
        } else {
            $this->denyAccess($this->user);
        }
        return false;
    }

    /**
     * Gets additional parameters used to determine a role
     *
     * @return array
     */
    protected function getRoleParams(): array
    {
        $roleParams = $this->roleParams;

        if ($roleParams instanceof \Closure) {
            $roleParams = call_user_func($this->roleParams, $this);
        }

        return (array)$roleParams;
    }

    /**
     * Denies the access of the user.
     * The default implementation will redirect the user to the login page if he is a guest;
     * if the user is already logged, a 403 HTTP exception will be thrown.
     * @param User|false $user the current user or boolean `false` in case of detached User component
     * @throws ForbiddenHttpException if the user is already logged in or in case of detached User component.
     */
    protected function denyAccess($user)
    {
        if ($user !== false && $user->getIsGuest()) {
            $user->loginRequired();
        } else {
            throw new ForbiddenHttpException(Yii::t('restful', 'You are not allowed to perform this action.'));
        }
    }
}
