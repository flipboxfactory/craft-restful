<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/craft-ember/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/craft-ember/
 */

namespace flipbox\craft\ember\filters;

use craft\helpers\ArrayHelper;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.0.0
 */
trait ActionTrait
{
    /**
     * @var array this property defines the a mapping for each action.
     * For each action that should only support limited set of values
     * you add a value with the action id as array key and an array value of
     * allowed status codes (e.g. 'create' => 'value1', 'delete' => 'value2').
     * If an action is not defined the default action property will be used.
     *
     * You can use `'*'` to stand for all actions. When an action is explicitly
     * specified, it takes precedence over the specification given by `'*'`.
     *
     * For example,
     *
     * ```php
     * [
     *   'create' => 'value1',
     *   'update' => 'value2',
     *   '*' => 'value3',
     * ]
     * ```
     */
    public $actions = [];

    /**
     * The default action value
     *
     * @var bool
     */
    public $default = null;

    /**
     * @param string $action
     * @return bool
     */
    protected function actionMatch(string $action): bool
    {
        if ($this->findAction($action) !== null) {
            return true;
        }

        return false;
    }

    /**
     * @param string $action
     * @return string|null
     */
    protected function findAction(string $action)
    {
        // Default
        $value = ArrayHelper::getValue($this->actions, '*', $this->default);

        // Look by specific action
        if (isset($this->actions[$action])) {
            $value = $this->actions[$action];
        }

        return $value;
    }
}
