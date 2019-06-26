<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\filters\transform;

use Closure;
use craft\web\User;
use flipbox\craft\restful\Restful;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\rbac\CheckAccessInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class TransformRule extends TransformFilter
{
    /**
     * List of action IDs that this rule applies to. The comparison is case-sensitive.
     * If not set or empty, it means this rule applies to all actions.
     *
     * @var array
     */
    public $actions = [];

    /**
     * List of roles that this rule applies to (requires properly configured User component).
     * Two special roles are recognized, and they are checked via [[User::isGuest]]:
     *
     * - `?`: matches a guest user (not authenticated yet)
     * - `@`: matches an authenticated user
     *
     * If you are using RBAC (Role-Based Access Control), you may also specify role or permission names.
     * In this case, [[User::can()]] will be called to check access.
     *
     * If this property is not set or empty, it means this rule applies to all roles.
     *
     * @var array
     * @see $roleParams
     */
    public $roles;

    /**
     * Parameters to pass to the [[User::can()]] function for evaluating
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
     * @var array|Closure
     * @see $roles
     */
    public $roleParams = [];

    /**
     * @var CheckAccessInterface|null
     */
    public $accessChecker;

    /**
     * Initializes the [[rules]] array by instantiating rule objects from configurations.
     */
    public function init()
    {
        $this->actions = $this->resolveActions($this->actions);

        if (!$this->accessChecker instanceof CheckAccessInterface) {
            $this->accessChecker = Restful::getInstance()->getSettings()->getAccessChecker();
        }

        parent::init();
    }

    /**
     * Checks whether the Web user is allowed to perform the specified action.
     *
     * @param Action $action the action to be performed
     * @param User|false $user the user object or `false` in case of detached User component
     * @return bool|null `true` if the user is allowed, `false` if the user is denied, `null`
     * if the rule does not apply to the user
     * @throws InvalidConfigException if User component is detached
     */
    public function matches(Action $action, $user)
    {
        if ($this->matchAction($action) &&
            $this->matchRoles($user)) {
            return true;
        }

        return false;
    }

    /**
     * @param Action $action the action
     * @return bool whether the rule applies to the action
     */
    protected function matchAction($action): bool
    {
        return empty($this->actions) ||
            isset($this->actions['*']) ||
            array_key_exists($action->id, $this->actions) ||
            in_array($action->id, $this->actions);
    }

    /**
     * @param User|false $user the user object
     * @return bool whether the rule applies to the role
     * @throws InvalidConfigException if User component is detached
     */
    protected function matchRoles($user): bool
    {
        if (empty($this->roles)) {
            return true;
        }
        if ($user === false) {
            throw new InvalidConfigException(
                'The user application component must be available to specify roles in AccessRule.'
            );
        }
        foreach ($this->roles as $role) {
            if ($this->matchRole($user, $role) === true) {
                return true;
            };
        }

        return false;
    }

    /**
     * @param User $user the user object
     * @param string $role
     * @return bool whether the rule applies to the role
     */
    protected function matchRole($user, $role): bool
    {
        if ($role === '?' && $user->getIsGuest()) {
            return true;
        }

        if ($role === '@' && !$user->getIsGuest()) {
            return true;
        }

        if (!$this->accessChecker instanceof CheckAccessInterface) {
            return false;
        }

        $roleParams = $this->roleParams instanceof Closure ?
            call_user_func($this->roleParams, $this) :
            $this->roleParams;

        return $this->accessChecker->checkAccess($user->id, $role, $roleParams);
    }

    /**
     * @param array $actions
     * @return array
     */
    private function resolveActions(array $actions = [])
    {
        $result = [];
        foreach ($actions as $i => $action) {
            if (!is_string($i)) {
                $i = $action;
                $action = null;
            }

            $result[$i] = $action;
        }

        return $result;
    }
}
